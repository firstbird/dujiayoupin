<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Symfony\Component\Cache\Adapter;

use Convoworks\Doctrine\DBAL\ArrayParameterType;
use Convoworks\Doctrine\DBAL\Configuration;
use Convoworks\Doctrine\DBAL\Connection;
use Convoworks\Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Convoworks\Doctrine\DBAL\DriverManager;
use Convoworks\Doctrine\DBAL\Exception as DBALException;
use Convoworks\Doctrine\DBAL\Exception\TableNotFoundException;
use Convoworks\Doctrine\DBAL\ParameterType;
use Convoworks\Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Convoworks\Doctrine\DBAL\Schema\Schema;
use Convoworks\Doctrine\DBAL\ServerVersionProvider;
use Convoworks\Doctrine\DBAL\Tools\DsnParser;
use Convoworks\Symfony\Component\Cache\Exception\InvalidArgumentException;
use Convoworks\Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Convoworks\Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Convoworks\Symfony\Component\Cache\PruneableInterface;
class DoctrineDbalAdapter extends AbstractAdapter implements PruneableInterface
{
    protected $maxIdLength = 255;
    private $marshaller;
    private $conn;
    private $platformName;
    private $serverVersion;
    private $table = 'cache_items';
    private $idCol = 'item_id';
    private $dataCol = 'item_data';
    private $lifetimeCol = 'item_lifetime';
    private $timeCol = 'item_time';
    private $namespace;
    /**
     * You can either pass an existing database Doctrine DBAL Connection or
     * a DSN string that will be used to connect to the database.
     *
     * The cache table is created automatically when possible.
     * Otherwise, use the createTable() method.
     *
     * List of available options:
     *  * db_table: The name of the table [default: cache_items]
     *  * db_id_col: The column where to store the cache id [default: item_id]
     *  * db_data_col: The column where to store the cache data [default: item_data]
     *  * db_lifetime_col: The column where to store the lifetime [default: item_lifetime]
     *  * db_time_col: The column where to store the timestamp [default: item_time]
     *
     * @param Connection|string $connOrDsn
     *
     * @throws InvalidArgumentException When namespace contains invalid characters
     */
    public function __construct($connOrDsn, string $namespace = '', int $defaultLifetime = 0, array $options = [], ?MarshallerInterface $marshaller = null)
    {
        if (isset($namespace[0]) && \preg_match('#[^-+.A-Za-z0-9]#', $namespace, $match)) {
            throw new InvalidArgumentException(\sprintf('Namespace contains "%s" but only characters in [-+.A-Za-z0-9] are allowed.', $match[0]));
        }
        if ($connOrDsn instanceof Connection) {
            $this->conn = $connOrDsn;
        } elseif (\is_string($connOrDsn)) {
            if (!\class_exists(DriverManager::class)) {
                throw new InvalidArgumentException('Failed to parse DSN. Try running "composer require doctrine/dbal".');
            }
            if (\class_exists(DsnParser::class)) {
                $params = (new DsnParser(['db2' => 'ibm_db2', 'mssql' => 'pdo_sqlsrv', 'mysql' => 'pdo_mysql', 'mysql2' => 'pdo_mysql', 'postgres' => 'pdo_pgsql', 'postgresql' => 'pdo_pgsql', 'pgsql' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite', 'sqlite3' => 'pdo_sqlite']))->parse($connOrDsn);
            } else {
                $params = ['url' => $connOrDsn];
            }
            $config = new Configuration();
            if (\class_exists(DefaultSchemaManagerFactory::class)) {
                $config->setSchemaManagerFactory(new DefaultSchemaManagerFactory());
            }
            $this->conn = DriverManager::getConnection($params, $config);
        } else {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be "%s" or string, "%s" given.', __METHOD__, Connection::class, \get_debug_type($connOrDsn)));
        }
        $this->table = $options['db_table'] ?? $this->table;
        $this->idCol = $options['db_id_col'] ?? $this->idCol;
        $this->dataCol = $options['db_data_col'] ?? $this->dataCol;
        $this->lifetimeCol = $options['db_lifetime_col'] ?? $this->lifetimeCol;
        $this->timeCol = $options['db_time_col'] ?? $this->timeCol;
        $this->namespace = $namespace;
        $this->marshaller = $marshaller ?? new DefaultMarshaller();
        parent::__construct($namespace, $defaultLifetime);
    }
    /**
     * Creates the table to store cache items which can be called once for setup.
     *
     * Cache ID are saved in a column of maximum length 255. Cache data is
     * saved in a BLOB.
     *
     * @throws DBALException When the table already exists
     */
    public function createTable()
    {
        $schema = new Schema();
        $this->addTableToSchema($schema);
        foreach ($schema->toSql($this->conn->getDatabasePlatform()) as $sql) {
            $this->conn->executeStatement($sql);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function configureSchema(Schema $schema, Connection $forConnection) : void
    {
        // only update the schema for this connection
        if ($forConnection !== $this->conn) {
            return;
        }
        if ($schema->hasTable($this->table)) {
            return;
        }
        $this->addTableToSchema($schema);
    }
    /**
     * {@inheritdoc}
     */
    public function prune() : bool
    {
        $deleteSql = "DELETE FROM {$this->table} WHERE {$this->lifetimeCol} + {$this->timeCol} <= ?";
        $params = [\time()];
        $paramTypes = [ParameterType::INTEGER];
        if ('' !== $this->namespace) {
            $deleteSql .= " AND {$this->idCol} LIKE ?";
            $params[] = \sprintf('%s%%', $this->namespace);
            $paramTypes[] = ParameterType::STRING;
        }
        try {
            $this->conn->executeStatement($deleteSql, $params, $paramTypes);
        } catch (TableNotFoundException $e) {
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function doFetch(array $ids) : iterable
    {
        $now = \time();
        $expired = [];
        $sql = "SELECT {$this->idCol}, CASE WHEN {$this->lifetimeCol} IS NULL OR {$this->lifetimeCol} + {$this->timeCol} > ? THEN {$this->dataCol} ELSE NULL END FROM {$this->table} WHERE {$this->idCol} IN (?)";
        $result = $this->conn->executeQuery($sql, [$now, $ids], [ParameterType::INTEGER, \class_exists(ArrayParameterType::class) ? ArrayParameterType::STRING : Connection::PARAM_STR_ARRAY])->iterateNumeric();
        foreach ($result as $row) {
            if (null === $row[1]) {
                $expired[] = $row[0];
            } else {
                (yield $row[0] => $this->marshaller->unmarshall(\is_resource($row[1]) ? \stream_get_contents($row[1]) : $row[1]));
            }
        }
        if ($expired) {
            $sql = "DELETE FROM {$this->table} WHERE {$this->lifetimeCol} + {$this->timeCol} <= ? AND {$this->idCol} IN (?)";
            $this->conn->executeStatement($sql, [$now, $expired], [ParameterType::INTEGER, \class_exists(ArrayParameterType::class) ? ArrayParameterType::STRING : Connection::PARAM_STR_ARRAY]);
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function doHave(string $id) : bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->idCol} = ? AND ({$this->lifetimeCol} IS NULL OR {$this->lifetimeCol} + {$this->timeCol} > ?)";
        $result = $this->conn->executeQuery($sql, [$id, \time()], [ParameterType::STRING, ParameterType::INTEGER]);
        return (bool) $result->fetchOne();
    }
    /**
     * {@inheritdoc}
     */
    protected function doClear(string $namespace) : bool
    {
        if ('' === $namespace) {
            if ('sqlite' === $this->getPlatformName()) {
                $sql = "DELETE FROM {$this->table}";
            } else {
                $sql = "TRUNCATE TABLE {$this->table}";
            }
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->idCol} LIKE '{$namespace}%'";
        }
        try {
            $this->conn->executeStatement($sql);
        } catch (TableNotFoundException $e) {
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function doDelete(array $ids) : bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->idCol} IN (?)";
        try {
            $this->conn->executeStatement($sql, [\array_values($ids)], [\class_exists(ArrayParameterType::class) ? ArrayParameterType::STRING : Connection::PARAM_STR_ARRAY]);
        } catch (TableNotFoundException $e) {
        }
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function doSave(array $values, int $lifetime)
    {
        if (!($values = $this->marshaller->marshall($values, $failed))) {
            return $failed;
        }
        $platformName = $this->getPlatformName();
        $insertSql = "INSERT INTO {$this->table} ({$this->idCol}, {$this->dataCol}, {$this->lifetimeCol}, {$this->timeCol}) VALUES (?, ?, ?, ?)";
        switch (\true) {
            case 'mysql' === $platformName:
                $sql = $insertSql . " ON DUPLICATE KEY UPDATE {$this->dataCol} = VALUES({$this->dataCol}), {$this->lifetimeCol} = VALUES({$this->lifetimeCol}), {$this->timeCol} = VALUES({$this->timeCol})";
                break;
            case 'oci' === $platformName:
                // DUAL is Oracle specific dummy table
                $sql = "MERGE INTO {$this->table} USING DUAL ON ({$this->idCol} = ?) " . "WHEN NOT MATCHED THEN INSERT ({$this->idCol}, {$this->dataCol}, {$this->lifetimeCol}, {$this->timeCol}) VALUES (?, ?, ?, ?) " . "WHEN MATCHED THEN UPDATE SET {$this->dataCol} = ?, {$this->lifetimeCol} = ?, {$this->timeCol} = ?";
                break;
            case 'sqlsrv' === $platformName && \version_compare($this->getServerVersion(), '10', '>='):
                // MERGE is only available since SQL Server 2008 and must be terminated by semicolon
                // It also requires HOLDLOCK according to http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
                $sql = "MERGE INTO {$this->table} WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ({$this->idCol} = ?) " . "WHEN NOT MATCHED THEN INSERT ({$this->idCol}, {$this->dataCol}, {$this->lifetimeCol}, {$this->timeCol}) VALUES (?, ?, ?, ?) " . "WHEN MATCHED THEN UPDATE SET {$this->dataCol} = ?, {$this->lifetimeCol} = ?, {$this->timeCol} = ?;";
                break;
            case 'sqlite' === $platformName:
                $sql = 'INSERT OR REPLACE' . \substr($insertSql, 6);
                break;
            case 'pgsql' === $platformName && \version_compare($this->getServerVersion(), '9.5', '>='):
                $sql = $insertSql . " ON CONFLICT ({$this->idCol}) DO UPDATE SET ({$this->dataCol}, {$this->lifetimeCol}, {$this->timeCol}) = (EXCLUDED.{$this->dataCol}, EXCLUDED.{$this->lifetimeCol}, EXCLUDED.{$this->timeCol})";
                break;
            default:
                $platformName = null;
                $sql = "UPDATE {$this->table} SET {$this->dataCol} = ?, {$this->lifetimeCol} = ?, {$this->timeCol} = ? WHERE {$this->idCol} = ?";
                break;
        }
        $now = \time();
        $lifetime = $lifetime ?: null;
        try {
            $stmt = $this->conn->prepare($sql);
        } catch (TableNotFoundException $e) {
            if (!$this->conn->isTransactionActive() || \in_array($platformName, ['pgsql', 'sqlite', 'sqlsrv'], \true)) {
                $this->createTable();
            }
            $stmt = $this->conn->prepare($sql);
        }
        if ('sqlsrv' === $platformName || 'oci' === $platformName) {
            $bind = static function ($id, $data) use($stmt) {
                $stmt->bindValue(1, $id);
                $stmt->bindValue(2, $id);
                $stmt->bindValue(3, $data, ParameterType::LARGE_OBJECT);
                $stmt->bindValue(6, $data, ParameterType::LARGE_OBJECT);
            };
            $stmt->bindValue(4, $lifetime, ParameterType::INTEGER);
            $stmt->bindValue(5, $now, ParameterType::INTEGER);
            $stmt->bindValue(7, $lifetime, ParameterType::INTEGER);
            $stmt->bindValue(8, $now, ParameterType::INTEGER);
        } elseif (null !== $platformName) {
            $bind = static function ($id, $data) use($stmt) {
                $stmt->bindValue(1, $id);
                $stmt->bindValue(2, $data, ParameterType::LARGE_OBJECT);
            };
            $stmt->bindValue(3, $lifetime, ParameterType::INTEGER);
            $stmt->bindValue(4, $now, ParameterType::INTEGER);
        } else {
            $stmt->bindValue(2, $lifetime, ParameterType::INTEGER);
            $stmt->bindValue(3, $now, ParameterType::INTEGER);
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->bindValue(3, $lifetime, ParameterType::INTEGER);
            $insertStmt->bindValue(4, $now, ParameterType::INTEGER);
            $bind = static function ($id, $data) use($stmt, $insertStmt) {
                $stmt->bindValue(1, $data, ParameterType::LARGE_OBJECT);
                $stmt->bindValue(4, $id);
                $insertStmt->bindValue(1, $id);
                $insertStmt->bindValue(2, $data, ParameterType::LARGE_OBJECT);
            };
        }
        foreach ($values as $id => $data) {
            $bind($id, $data);
            try {
                $rowCount = $stmt->executeStatement();
            } catch (TableNotFoundException $e) {
                if (!$this->conn->isTransactionActive() || \in_array($platformName, ['pgsql', 'sqlite', 'sqlsrv'], \true)) {
                    $this->createTable();
                }
                $rowCount = $stmt->executeStatement();
            }
            if (null === $platformName && 0 === $rowCount) {
                try {
                    $insertStmt->executeStatement();
                } catch (DBALException $e) {
                    // A concurrent write won, let it be
                }
            }
        }
        return $failed;
    }
    /**
     * @internal
     */
    protected function getId($key)
    {
        if ('pgsql' !== $this->getPlatformName()) {
            return parent::getId($key);
        }
        if (\str_contains($key, "\x00") || \str_contains($key, '%') || !\preg_match('//u', $key)) {
            $key = \rawurlencode($key);
        }
        return parent::getId($key);
    }
    private function getPlatformName() : string
    {
        if (isset($this->platformName)) {
            return $this->platformName;
        }
        $platform = $this->conn->getDatabasePlatform();
        switch (\true) {
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\MySQLPlatform:
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\MySQL57Platform:
                return $this->platformName = 'mysql';
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\SqlitePlatform:
                return $this->platformName = 'sqlite';
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\PostgreSQLPlatform:
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\PostgreSQL94Platform:
                return $this->platformName = 'pgsql';
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\OraclePlatform:
                return $this->platformName = 'oci';
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\SQLServerPlatform:
            case $platform instanceof \Convoworks\Doctrine\DBAL\Platforms\SQLServer2012Platform:
                return $this->platformName = 'sqlsrv';
            default:
                return $this->platformName = \get_class($platform);
        }
    }
    private function getServerVersion() : string
    {
        if (isset($this->serverVersion)) {
            return $this->serverVersion;
        }
        if ($this->conn instanceof ServerVersionProvider || $this->conn instanceof ServerInfoAwareConnection) {
            return $this->serverVersion = $this->conn->getServerVersion();
        }
        // The condition should be removed once support for DBAL <3.3 is dropped
        $conn = \method_exists($this->conn, 'getNativeConnection') ? $this->conn->getNativeConnection() : $this->conn->getWrappedConnection();
        return $this->serverVersion = $conn->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }
    private function addTableToSchema(Schema $schema) : void
    {
        $types = ['mysql' => 'binary', 'sqlite' => 'text'];
        $table = $schema->createTable($this->table);
        $table->addColumn($this->idCol, $types[$this->getPlatformName()] ?? 'string', ['length' => 255]);
        $table->addColumn($this->dataCol, 'blob', ['length' => 16777215]);
        $table->addColumn($this->lifetimeCol, 'integer', ['unsigned' => \true, 'notnull' => \false]);
        $table->addColumn($this->timeCol, 'integer', ['unsigned' => \true]);
        $table->setPrimaryKey([$this->idCol]);
    }
}
