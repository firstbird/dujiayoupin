<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Symfony\Component\DependencyInjection\Loader;

use Convoworks\Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Convoworks\Symfony\Component\Config\Builder\ConfigBuilderGeneratorInterface;
use Convoworks\Symfony\Component\Config\Builder\ConfigBuilderInterface;
use Convoworks\Symfony\Component\Config\FileLocatorInterface;
use Convoworks\Symfony\Component\DependencyInjection\Attribute\When;
use Convoworks\Symfony\Component\DependencyInjection\Container;
use Convoworks\Symfony\Component\DependencyInjection\ContainerBuilder;
use Convoworks\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Convoworks\Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Convoworks\Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Convoworks\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
/**
 * PhpFileLoader loads service definitions from a PHP file.
 *
 * The PHP file is required and the $container variable can be
 * used within the file to change the container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PhpFileLoader extends FileLoader
{
    protected $autoRegisterAliasesForSinglyImplementedInterfaces = \false;
    private $generator;
    public function __construct(ContainerBuilder $container, FileLocatorInterface $locator, ?string $env = null, ?ConfigBuilderGeneratorInterface $generator = null)
    {
        parent::__construct($container, $locator, $env);
        $this->generator = $generator;
    }
    /**
     * {@inheritdoc}
     */
    public function load($resource, ?string $type = null)
    {
        // the container and loader variables are exposed to the included file below
        $container = $this->container;
        $loader = $this;
        $path = $this->locator->locate($resource);
        $this->setCurrentDir(\dirname($path));
        $this->container->fileExists($path);
        // the closure forbids access to the private scope in the included file
        $load = \Closure::bind(function ($path, $env) use($container, $loader, $resource, $type) {
            return include $path;
        }, $this, ProtectedPhpFileLoader::class);
        try {
            $callback = $load($path, $this->env);
            if (\is_object($callback) && \is_callable($callback)) {
                $this->executeCallback($callback, new ContainerConfigurator($this->container, $this, $this->instanceof, $path, $resource, $this->env), $path);
            }
        } finally {
            $this->instanceof = [];
            $this->registerAliasesForSinglyImplementedInterfaces();
        }
        return null;
    }
    /**
     * {@inheritdoc}
     */
    public function supports($resource, ?string $type = null)
    {
        if (!\is_string($resource)) {
            return \false;
        }
        if (null === $type && 'php' === \pathinfo($resource, \PATHINFO_EXTENSION)) {
            return \true;
        }
        return 'php' === $type;
    }
    /**
     * Resolve the parameters to the $callback and execute it.
     */
    private function executeCallback(callable $callback, ContainerConfigurator $containerConfigurator, string $path)
    {
        if (!$callback instanceof \Closure) {
            $callback = \Closure::fromCallable($callback);
        }
        $arguments = [];
        $configBuilders = [];
        $r = new \ReflectionFunction($callback);
        if (\PHP_VERSION_ID >= 80000) {
            $attribute = null;
            foreach ($r->getAttributes(When::class) as $attribute) {
                if ($this->env === $attribute->newInstance()->env) {
                    $attribute = null;
                    break;
                }
            }
            if (null !== $attribute) {
                return;
            }
        }
        foreach ($r->getParameters() as $parameter) {
            $reflectionType = $parameter->getType();
            if (!$reflectionType instanceof \ReflectionNamedType) {
                throw new \InvalidArgumentException(\sprintf('Could not resolve argument "$%s" for "%s". You must typehint it (for example with "%s" or "%s").', $parameter->getName(), $path, ContainerConfigurator::class, ContainerBuilder::class));
            }
            $type = $reflectionType->getName();
            switch ($type) {
                case ContainerConfigurator::class:
                    $arguments[] = $containerConfigurator;
                    break;
                case ContainerBuilder::class:
                    $arguments[] = $this->container;
                    break;
                case FileLoader::class:
                case self::class:
                    $arguments[] = $this;
                    break;
                default:
                    try {
                        $configBuilder = $this->configBuilder($type);
                    } catch (InvalidArgumentException|\LogicException $e) {
                        throw new \InvalidArgumentException(\sprintf('Could not resolve argument "%s" for "%s".', $type . ' $' . $parameter->getName(), $path), 0, $e);
                    }
                    $configBuilders[] = $configBuilder;
                    $arguments[] = $configBuilder;
            }
        }
        // Force load ContainerConfigurator to make env(), param() etc available.
        \class_exists(ContainerConfigurator::class);
        $callback(...$arguments);
        /** @var ConfigBuilderInterface $configBuilder */
        foreach ($configBuilders as $configBuilder) {
            $containerConfigurator->extension($configBuilder->getExtensionAlias(), $configBuilder->toArray());
        }
    }
    /**
     * @param string $namespace FQCN string for a class implementing ConfigBuilderInterface
     */
    private function configBuilder(string $namespace) : ConfigBuilderInterface
    {
        if (!\class_exists(ConfigBuilderGenerator::class)) {
            throw new \LogicException('You cannot use the config builder as the Config component is not installed. Try running "composer require symfony/config".');
        }
        if (null === $this->generator) {
            throw new \LogicException('You cannot use the ConfigBuilders without providing a class implementing ConfigBuilderGeneratorInterface.');
        }
        // If class exists and implements ConfigBuilderInterface
        if (\class_exists($namespace) && \is_subclass_of($namespace, ConfigBuilderInterface::class)) {
            return new $namespace();
        }
        // If it does not start with Symfony\Config\ we dont know how to handle this
        if ('Symfony\\Config\\' !== \substr($namespace, 0, 15)) {
            throw new InvalidArgumentException(\sprintf('Could not find or generate class "%s".', $namespace));
        }
        // Try to get the extension alias
        $alias = Container::underscore(\substr($namespace, 15, -6));
        if (\false !== \strpos($alias, '\\')) {
            throw new InvalidArgumentException('You can only use "root" ConfigBuilders from "Symfony\\Config\\" namespace. Nested classes like "Symfony\\Config\\Framework\\CacheConfig" cannot be used.');
        }
        if (!$this->container->hasExtension($alias)) {
            $extensions = \array_filter(\array_map(function (ExtensionInterface $ext) {
                return $ext->getAlias();
            }, $this->container->getExtensions()));
            throw new InvalidArgumentException(\sprintf('There is no extension able to load the configuration for "%s". Looked for namespace "%s", found "%s".', $namespace, $alias, $extensions ? \implode('", "', $extensions) : 'none'));
        }
        $extension = $this->container->getExtension($alias);
        if (!$extension instanceof ConfigurationExtensionInterface) {
            throw new \LogicException(\sprintf('You cannot use the config builder for "%s" because the extension does not implement "%s".', $namespace, ConfigurationExtensionInterface::class));
        }
        $configuration = $extension->getConfiguration([], $this->container);
        $loader = $this->generator->build($configuration);
        return $loader();
    }
}
/**
 * @internal
 */
final class ProtectedPhpFileLoader extends PhpFileLoader
{
}
