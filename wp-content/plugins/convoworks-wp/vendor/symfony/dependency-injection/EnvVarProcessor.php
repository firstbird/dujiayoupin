<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Symfony\Component\DependencyInjection;

use Convoworks\Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Convoworks\Symfony\Component\DependencyInjection\Exception\ParameterCircularReferenceException;
use Convoworks\Symfony\Component\DependencyInjection\Exception\RuntimeException;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class EnvVarProcessor implements EnvVarProcessorInterface
{
    private $container;
    private $loaders;
    private $loadedVars = [];
    /**
     * @param EnvVarLoaderInterface[] $loaders
     */
    public function __construct(ContainerInterface $container, ?\Traversable $loaders = null)
    {
        $this->container = $container;
        $this->loaders = $loaders ?? new \ArrayIterator();
    }
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes()
    {
        return ['base64' => 'string', 'bool' => 'bool', 'not' => 'bool', 'const' => 'bool|int|float|string|array', 'csv' => 'array', 'file' => 'string', 'float' => 'float', 'int' => 'int', 'json' => 'array', 'key' => 'bool|int|float|string|array', 'url' => 'array', 'query_string' => 'array', 'resolve' => 'string', 'default' => 'bool|int|float|string|array', 'string' => 'string', 'trim' => 'string', 'require' => 'bool|int|float|string|array'];
    }
    /**
     * {@inheritdoc}
     */
    public function getEnv(string $prefix, string $name, \Closure $getEnv)
    {
        $i = \strpos($name, ':');
        if ('key' === $prefix) {
            if (\false === $i) {
                throw new RuntimeException(\sprintf('Invalid env "key:%s": a key specifier should be provided.', $name));
            }
            $next = \substr($name, $i + 1);
            $key = \substr($name, 0, $i);
            $array = $getEnv($next);
            if (!\is_array($array)) {
                throw new RuntimeException(\sprintf('Resolved value of "%s" did not result in an array value.', $next));
            }
            if (!isset($array[$key]) && !\array_key_exists($key, $array)) {
                throw new EnvNotFoundException(\sprintf('Key "%s" not found in %s (resolved from "%s").', $key, \json_encode($array), $next));
            }
            return $array[$key];
        }
        if ('default' === $prefix) {
            if (\false === $i) {
                throw new RuntimeException(\sprintf('Invalid env "default:%s": a fallback parameter should be provided.', $name));
            }
            $next = \substr($name, $i + 1);
            $default = \substr($name, 0, $i);
            if ('' !== $default && !$this->container->hasParameter($default)) {
                throw new RuntimeException(\sprintf('Invalid env fallback in "default:%s": parameter "%s" not found.', $name, $default));
            }
            try {
                $env = $getEnv($next);
                if ('' !== $env && null !== $env) {
                    return $env;
                }
            } catch (EnvNotFoundException $e) {
                // no-op
            }
            return '' === $default ? null : $this->container->getParameter($default);
        }
        if ('file' === $prefix || 'require' === $prefix) {
            if (!\is_scalar($file = $getEnv($name))) {
                throw new RuntimeException(\sprintf('Invalid file name: env var "%s" is non-scalar.', $name));
            }
            if (!\is_file($file)) {
                throw new EnvNotFoundException(\sprintf('File "%s" not found (resolved from "%s").', $file, $name));
            }
            if ('file' === $prefix) {
                return \file_get_contents($file);
            } else {
                return require $file;
            }
        }
        $returnNull = \false;
        if ('' === $prefix) {
            $returnNull = \true;
            $prefix = 'string';
        }
        if (\false !== $i || 'string' !== $prefix) {
            $env = $getEnv($name);
        } elseif (isset($_ENV[$name])) {
            $env = $_ENV[$name];
        } elseif (isset($_SERVER[$name]) && !\str_starts_with($name, 'HTTP_')) {
            $env = $_SERVER[$name];
        } elseif (\false === ($env = \getenv($name)) || null === $env) {
            // null is a possible value because of thread safety issues
            foreach ($this->loadedVars as $vars) {
                if (\false !== ($env = $vars[$name] ?? \false)) {
                    break;
                }
            }
            if (\false === $env || null === $env) {
                $loaders = $this->loaders;
                $this->loaders = new \ArrayIterator();
                try {
                    $i = 0;
                    $ended = \true;
                    $count = $loaders instanceof \Countable ? $loaders->count() : 0;
                    foreach ($loaders as $loader) {
                        if (\count($this->loadedVars) > $i++) {
                            continue;
                        }
                        $this->loadedVars[] = $vars = $loader->loadEnvVars();
                        if (\false !== ($env = $vars[$name] ?? \false)) {
                            $ended = \false;
                            break;
                        }
                    }
                    if ($ended || $count === $i) {
                        $loaders = $this->loaders;
                    }
                } catch (ParameterCircularReferenceException $e) {
                    // skip loaders that need an env var that is not defined
                } finally {
                    $this->loaders = $loaders;
                }
            }
            if (\false === $env || null === $env) {
                if (!$this->container->hasParameter("env({$name})")) {
                    throw new EnvNotFoundException(\sprintf('Environment variable not found: "%s".', $name));
                }
                $env = $this->container->getParameter("env({$name})");
            }
        }
        if (null === $env) {
            if ($returnNull) {
                return null;
            }
            if (!isset($this->getProvidedTypes()[$prefix])) {
                throw new RuntimeException(\sprintf('Unsupported env var prefix "%s".', $prefix));
            }
            if (!\in_array($prefix, ['string', 'bool', 'not', 'int', 'float'], \true)) {
                return null;
            }
        }
        if (null !== $env && !\is_scalar($env)) {
            throw new RuntimeException(\sprintf('Non-scalar env var "%s" cannot be cast to "%s".', $name, $prefix));
        }
        if ('string' === $prefix) {
            return (string) $env;
        }
        if (\in_array($prefix, ['bool', 'not'], \true)) {
            $env = (bool) ((\filter_var($env, \FILTER_VALIDATE_BOOLEAN) ?: \filter_var($env, \FILTER_VALIDATE_INT)) ?: \filter_var($env, \FILTER_VALIDATE_FLOAT));
            return 'not' === $prefix ? !$env : $env;
        }
        if ('int' === $prefix) {
            if (null !== $env && \false === ($env = \filter_var($env, \FILTER_VALIDATE_INT) ?: \filter_var($env, \FILTER_VALIDATE_FLOAT))) {
                throw new RuntimeException(\sprintf('Non-numeric env var "%s" cannot be cast to int.', $name));
            }
            return (int) $env;
        }
        if ('float' === $prefix) {
            if (null !== $env && \false === ($env = \filter_var($env, \FILTER_VALIDATE_FLOAT))) {
                throw new RuntimeException(\sprintf('Non-numeric env var "%s" cannot be cast to float.', $name));
            }
            return (float) $env;
        }
        if ('const' === $prefix) {
            if (!\defined($env)) {
                throw new RuntimeException(\sprintf('Env var "%s" maps to undefined constant "%s".', $name, $env));
            }
            return \constant($env);
        }
        if ('base64' === $prefix) {
            return \base64_decode(\strtr($env, '-_', '+/'));
        }
        if ('json' === $prefix) {
            $env = \json_decode($env, \true);
            if (\JSON_ERROR_NONE !== \json_last_error()) {
                throw new RuntimeException(\sprintf('Invalid JSON in env var "%s": ', $name) . \json_last_error_msg());
            }
            if (null !== $env && !\is_array($env)) {
                throw new RuntimeException(\sprintf('Invalid JSON env var "%s": array or null expected, "%s" given.', $name, \get_debug_type($env)));
            }
            return $env;
        }
        if ('url' === $prefix) {
            $params = \parse_url($env);
            if (\false === $params) {
                throw new RuntimeException(\sprintf('Invalid URL in env var "%s".', $name));
            }
            if (!isset($params['scheme'], $params['host'])) {
                throw new RuntimeException(\sprintf('Invalid URL in env var "%s": scheme and host expected.', $name));
            }
            $params += ['port' => null, 'user' => null, 'pass' => null, 'path' => null, 'query' => null, 'fragment' => null];
            $params['user'] = null !== $params['user'] ? \rawurldecode($params['user']) : null;
            $params['pass'] = null !== $params['pass'] ? \rawurldecode($params['pass']) : null;
            // remove the '/' separator
            $params['path'] = '/' === ($params['path'] ?? '/') ? '' : \substr($params['path'], 1);
            return $params;
        }
        if ('query_string' === $prefix) {
            $queryString = \parse_url($env, \PHP_URL_QUERY) ?: $env;
            \parse_str($queryString, $result);
            return $result;
        }
        if ('resolve' === $prefix) {
            return \preg_replace_callback('/%%|%([^%\\s]+)%/', function ($match) use($name, $getEnv) {
                if (!isset($match[1])) {
                    return '%';
                }
                if (\str_starts_with($match[1], 'env(') && \str_ends_with($match[1], ')') && 'env()' !== $match[1]) {
                    $value = $getEnv(\substr($match[1], 4, -1));
                } else {
                    $value = $this->container->getParameter($match[1]);
                }
                if (!\is_scalar($value)) {
                    throw new RuntimeException(\sprintf('Parameter "%s" found when resolving env var "%s" must be scalar, "%s" given.', $match[1], $name, \get_debug_type($value)));
                }
                return $value;
            }, $env);
        }
        if ('csv' === $prefix) {
            return \str_getcsv($env, ',', '"', \PHP_VERSION_ID >= 70400 ? '' : '\\');
        }
        if ('trim' === $prefix) {
            return \trim($env);
        }
        throw new RuntimeException(\sprintf('Unsupported env var prefix "%s" for env name "%s".', $prefix, $name));
    }
}
