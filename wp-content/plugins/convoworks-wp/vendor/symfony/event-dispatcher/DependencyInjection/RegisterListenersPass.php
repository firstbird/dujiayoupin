<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Symfony\Component\EventDispatcher\DependencyInjection;

use Convoworks\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Convoworks\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Convoworks\Symfony\Component\DependencyInjection\ContainerBuilder;
use Convoworks\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Convoworks\Symfony\Component\DependencyInjection\Reference;
use Convoworks\Symfony\Component\EventDispatcher\EventDispatcher;
use Convoworks\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Convoworks\Symfony\Contracts\EventDispatcher\Event;
/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class RegisterListenersPass implements CompilerPassInterface
{
    protected $dispatcherService;
    protected $listenerTag;
    protected $subscriberTag;
    protected $eventAliasesParameter;
    private $hotPathEvents = [];
    private $hotPathTagName = 'container.hot_path';
    private $noPreloadEvents = [];
    private $noPreloadTagName = 'container.no_preload';
    public function __construct(string $dispatcherService = 'event_dispatcher', string $listenerTag = 'kernel.event_listener', string $subscriberTag = 'kernel.event_subscriber', string $eventAliasesParameter = 'event_dispatcher.event_aliases')
    {
        if (0 < \func_num_args()) {
            trigger_deprecation('symfony/event-dispatcher', '5.3', 'Configuring "%s" is deprecated.', __CLASS__);
        }
        $this->dispatcherService = $dispatcherService;
        $this->listenerTag = $listenerTag;
        $this->subscriberTag = $subscriberTag;
        $this->eventAliasesParameter = $eventAliasesParameter;
    }
    /**
     * @return $this
     */
    public function setHotPathEvents(array $hotPathEvents)
    {
        $this->hotPathEvents = \array_flip($hotPathEvents);
        if (1 < \func_num_args()) {
            trigger_deprecation('symfony/event-dispatcher', '5.4', 'Configuring "$tagName" in "%s" is deprecated.', __METHOD__);
            $this->hotPathTagName = \func_get_arg(1);
        }
        return $this;
    }
    /**
     * @return $this
     */
    public function setNoPreloadEvents(array $noPreloadEvents) : self
    {
        $this->noPreloadEvents = \array_flip($noPreloadEvents);
        if (1 < \func_num_args()) {
            trigger_deprecation('symfony/event-dispatcher', '5.4', 'Configuring "$tagName" in "%s" is deprecated.', __METHOD__);
            $this->noPreloadTagName = \func_get_arg(1);
        }
        return $this;
    }
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->dispatcherService) && !$container->hasAlias($this->dispatcherService)) {
            return;
        }
        $aliases = [];
        if ($container->hasParameter($this->eventAliasesParameter)) {
            $aliases = $container->getParameter($this->eventAliasesParameter);
        }
        $globalDispatcherDefinition = $container->findDefinition($this->dispatcherService);
        foreach ($container->findTaggedServiceIds($this->listenerTag, \true) as $id => $events) {
            $noPreload = 0;
            foreach ($events as $event) {
                $priority = $event['priority'] ?? 0;
                if (!isset($event['event'])) {
                    if ($container->getDefinition($id)->hasTag($this->subscriberTag)) {
                        continue;
                    }
                    $event['method'] = $event['method'] ?? '__invoke';
                    $event['event'] = $this->getEventFromTypeDeclaration($container, $id, $event['method']);
                }
                $event['event'] = $aliases[$event['event']] ?? $event['event'];
                if (!isset($event['method'])) {
                    $event['method'] = 'on' . \preg_replace_callback(['/(?<=\\b|_)[a-z]/i', '/[^a-z0-9]/i'], function ($matches) {
                        return \strtoupper($matches[0]);
                    }, $event['event']);
                    $event['method'] = \preg_replace('/[^a-z0-9]/i', '', $event['method']);
                    if (null !== ($class = $container->getDefinition($id)->getClass()) && ($r = $container->getReflectionClass($class, \false)) && !$r->hasMethod($event['method'])) {
                        if (!$r->hasMethod('__invoke')) {
                            throw new InvalidArgumentException(\sprintf('None of the "%s" or "__invoke" methods exist for the service "%s". Please define the "method" attribute on "%s" tags.', $event['method'], $id, $this->listenerTag));
                        }
                        $event['method'] = '__invoke';
                    }
                }
                $dispatcherDefinition = $globalDispatcherDefinition;
                if (isset($event['dispatcher'])) {
                    $dispatcherDefinition = $container->findDefinition($event['dispatcher']);
                }
                $dispatcherDefinition->addMethodCall('addListener', [$event['event'], [new ServiceClosureArgument(new Reference($id)), $event['method']], $priority]);
                if (isset($this->hotPathEvents[$event['event']])) {
                    $container->getDefinition($id)->addTag($this->hotPathTagName);
                } elseif (isset($this->noPreloadEvents[$event['event']])) {
                    ++$noPreload;
                }
            }
            if ($noPreload && \count($events) === $noPreload) {
                $container->getDefinition($id)->addTag($this->noPreloadTagName);
            }
        }
        $extractingDispatcher = new ExtractingEventDispatcher();
        foreach ($container->findTaggedServiceIds($this->subscriberTag, \true) as $id => $tags) {
            $def = $container->getDefinition($id);
            // We must assume that the class value has been correctly filled, even if the service is created by a factory
            $class = $def->getClass();
            if (!($r = $container->getReflectionClass($class))) {
                throw new InvalidArgumentException(\sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$r->isSubclassOf(EventSubscriberInterface::class)) {
                throw new InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, EventSubscriberInterface::class));
            }
            $class = $r->name;
            $dispatcherDefinitions = [];
            foreach ($tags as $attributes) {
                if (!isset($attributes['dispatcher']) || isset($dispatcherDefinitions[$attributes['dispatcher']])) {
                    continue;
                }
                $dispatcherDefinitions[$attributes['dispatcher']] = $container->findDefinition($attributes['dispatcher']);
            }
            if (!$dispatcherDefinitions) {
                $dispatcherDefinitions = [$globalDispatcherDefinition];
            }
            $noPreload = 0;
            ExtractingEventDispatcher::$aliases = $aliases;
            ExtractingEventDispatcher::$subscriber = $class;
            $extractingDispatcher->addSubscriber($extractingDispatcher);
            foreach ($extractingDispatcher->listeners as $args) {
                $args[1] = [new ServiceClosureArgument(new Reference($id)), $args[1]];
                foreach ($dispatcherDefinitions as $dispatcherDefinition) {
                    $dispatcherDefinition->addMethodCall('addListener', $args);
                }
                if (isset($this->hotPathEvents[$args[0]])) {
                    $container->getDefinition($id)->addTag($this->hotPathTagName);
                } elseif (isset($this->noPreloadEvents[$args[0]])) {
                    ++$noPreload;
                }
            }
            if ($noPreload && \count($extractingDispatcher->listeners) === $noPreload) {
                $container->getDefinition($id)->addTag($this->noPreloadTagName);
            }
            $extractingDispatcher->listeners = [];
            ExtractingEventDispatcher::$aliases = [];
        }
    }
    private function getEventFromTypeDeclaration(ContainerBuilder $container, string $id, string $method) : string
    {
        if (null === ($class = $container->getDefinition($id)->getClass()) || !($r = $container->getReflectionClass($class, \false)) || !$r->hasMethod($method) || 1 > ($m = $r->getMethod($method))->getNumberOfParameters() || !($type = $m->getParameters()[0]->getType()) instanceof \ReflectionNamedType || $type->isBuiltin() || Event::class === ($name = $type->getName())) {
            throw new InvalidArgumentException(\sprintf('Service "%s" must define the "event" attribute on "%s" tags.', $id, $this->listenerTag));
        }
        return $name;
    }
}
/**
 * @internal
 */
class ExtractingEventDispatcher extends EventDispatcher implements EventSubscriberInterface
{
    public $listeners = [];
    public static $aliases = [];
    public static $subscriber;
    public function addListener(string $eventName, $listener, int $priority = 0)
    {
        $this->listeners[] = [$eventName, $listener[1], $priority];
    }
    public static function getSubscribedEvents() : array
    {
        $events = [];
        foreach ([self::$subscriber, 'getSubscribedEvents']() as $eventName => $params) {
            $events[self::$aliases[$eventName] ?? $eventName] = $params;
        }
        return $events;
    }
}
