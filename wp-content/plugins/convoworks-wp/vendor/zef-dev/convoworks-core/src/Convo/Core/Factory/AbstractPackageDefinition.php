<?php

declare (strict_types=1);
namespace Convo\Core\Factory;

use Convo\Core\ComponentNotFoundException;
use Convo\Core\Intent\IntentModel;
use Convo\Core\Intent\ISystemEntityRepository;
use Convo\Core\Intent\ISystemIntentRepository;
use Convo\Core\Intent\SystemEntity;
use Convo\Core\Intent\SystemIntent;
use Convo\Core\Expression\ExpressionFunctionProviderInterface;
use Convo\Core\Util\StrUtil;
abstract class AbstractPackageDefinition implements \Convo\Core\Factory\IPackageDefinition, \Convo\Core\Factory\ITemplateSource, \Convo\Core\Factory\IComponentProvider, ISystemIntentRepository, ISystemEntityRepository, ExpressionFunctionProviderInterface
{
    private $_namespace;
    private $_packageDir;
    /**
     * @var ComponentDefinition[]
     */
    private $_definitions;
    private $_templates;
    private $_templateFiles;
    /**
     * @var SystemEntity[]
     */
    private $_entities;
    /**
     * @var SystemIntent[]
     */
    private $_intents;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $namespace
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, $namespace, $packageDir)
    {
        $this->_logger = $logger;
        $this->_namespace = $namespace;
        $this->_packageDir = $packageDir;
    }
    // PACKAGE
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Intent\IPrefixed::accepts()
     */
    public function accepts($namespace)
    {
        return $this->getNamespace() === $namespace;
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Factory\IPackageDefinition::getNamespace()
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }
    /**
     * @return ComponentDefinition[]
     */
    protected function _initDefintions()
    {
        return [];
    }
    /**
     * @return array
     */
    protected function _initIntents()
    {
        return [];
    }
    public function getFunctions()
    {
        return [];
    }
    // INTENTS
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Intent\ISystemIntentRepository::findPlatformIntent()
     */
    public function findPlatformIntent($name, $platformId)
    {
        $this->_logger->debug('Searching for platform [' . $platformId . '] intent [' . $name . ']');
        $intents = $this->getIntents();
        foreach ($intents as $definition) {
            try {
                $intent = $definition->getPlatformModel($platformId);
                if ($intent->getName() === $name) {
                    $this->_logger->debug('Returning  intent [' . $intent . '] for [' . $name . ']');
                    return $definition;
                }
            } catch (\Convo\Core\ComponentNotFoundException $e) {
            }
        }
        throw new \Convo\Core\ComponentNotFoundException('Platform [' . $platformId . '] intent [' . $name . '] not found');
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Intent\ISystemIntentRepository::getIntent()
     */
    public function getIntent($name)
    {
        $intents = $this->getIntents();
        if (isset($intents[$name])) {
            return $intents[$name];
        }
        throw new \Convo\Core\ComponentNotFoundException('System intent [' . $name . '] not found');
    }
    public function getIntents()
    {
        if (!isset($this->_intents)) {
            $this->_intents = $this->_initIntents();
        }
        return $this->_intents;
    }
    protected function _loadIntents($path)
    {
        $data = $this->_loadFile($path);
        $intents = [];
        foreach ($data as $intent_name => $definitions) {
            foreach ($definitions as $definition) {
                if (!isset($intents[$intent_name])) {
                    $intents[$intent_name] = new SystemIntent($intent_name);
                }
                $intent = new IntentModel();
                $intent->load($definition['definition']);
                foreach ($definition['platforms'] as $platform_id) {
                    $intents[$intent_name]->setPlatformModel($platform_id, $intent);
                }
            }
        }
        $this->_logger->debug('Loaded intents [' . \implode(', ', \array_keys($intents)) . ']');
        return $intents;
    }
    // ENTITIES
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Intent\ISystemEntityRepository::findPlatformEntity()
     */
    public function findPlatformEntity($name, $platformId)
    {
        $entities = $this->getEntities();
        foreach ($entities as $definition) {
            try {
                $entity = $definition->getPlatformModel($platformId);
                if ($entity->getName() === $name) {
                    return $entity;
                }
            } catch (\Convo\Core\ComponentNotFoundException $e) {
            }
        }
        throw new \Convo\Core\ComponentNotFoundException('Platform [' . $platformId . '] entity [' . $name . '] not found');
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Intent\ISystemEntityRepository::getEntity()
     */
    public function getEntity($name)
    {
        $entities = $this->getEntities();
        if (isset($entities[$name])) {
            return $entities[$name];
        }
        throw new \Convo\Core\ComponentNotFoundException('System entity [' . $name . '] not found');
    }
    public function getEntities()
    {
        if (!isset($this->_entities)) {
            $this->_entities = $this->_initEntities();
        }
        return $this->_entities;
    }
    protected function _initEntities()
    {
        return [];
    }
    // TEMPLATES
    public function registerTemplate($path)
    {
        $this->_templateFiles[] = $path;
    }
    public function getTemplates()
    {
        if (!isset($this->_templates)) {
            $this->_templates = [];
            foreach ($this->_templateFiles as $path) {
                $this->_addTemplate($this->_loadFile($path));
            }
        }
        return $this->_templates;
    }
    private function _addTemplate($template)
    {
        $template['template_id'] = $this->getNamespace() . '.' . $template['template_id'];
        $this->_templates[] = $template;
    }
    /**
     * @param array $template
     * @deprecated
     */
    public function addTemplate($template)
    {
        if (!isset($this->_templates)) {
            $this->_templates = [];
        }
        $this->_addTemplate($template);
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Factory\IPackageDefinition::getTemplate()
     */
    public function getTemplate($templateId)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template['template_id'] === $templateId) {
                return $template;
            }
        }
        throw new ComponentNotFoundException('Service template [' . $templateId . '] not found');
    }
    // COMPONENTS
    public function getComponentDefinitions()
    {
        if (!isset($this->_definitions)) {
            $this->_definitions = [];
            foreach ($this->_initDefintions() as $definition) {
                $this->_definitions[$definition->getType()] = $definition;
            }
        }
        return $this->_definitions;
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Factory\IComponentProvider::getComponentDefinition()
     */
    public function getComponentDefinition($class)
    {
        // 		$this->_logger->debug( 'Searching for class ['.$class.'] in ['.$this.']');
        $definitions = $this->getComponentDefinitions();
        if (isset($definitions[$class])) {
            return $definitions[$class];
        }
        foreach ($definitions as $definition) {
            /* @var $definition ComponentDefinition */
            if ($definition->isAlias($class)) {
                // $this->_logger->debug( 'Found definition ['.$definition.'] in ['.$this.'] as alias');
                if (!\class_exists($class)) {
                    \class_alias($definition->getType(), $class);
                }
                return $definition;
            }
        }
        throw new ComponentNotFoundException('Component definition [' . $class . '] not found');
    }
    public function getComponentHelp($component)
    {
        $path = $this->_packageDir . '/Help/' . $component;
        if (!StrUtil::endsWith($path, '.html')) {
            $path .= '.html';
        }
        $path = \realpath($path);
        if ($path === \false) {
            throw new ComponentNotFoundException("Requested help file [{$component}] does not exist.");
        }
        $this->_logger->debug('Going to try opening help file [' . $path . ']');
        if (($help = \file_get_contents($path)) === \false) {
            throw new ComponentNotFoundException('Could not find help for component [' . $component . '] in [' . $this->_packageDir . ']');
        }
        return $help;
    }
    /**
     * {@inheritDoc}
     * @see \Convo\Core\Factory\IComponentProvider::createPackageComponent()
     */
    public function createPackageComponent(\Convo\Core\ConvoServiceInstance $service, \Convo\Core\Factory\PackageProvider $packageProvider, $componentData)
    {
        $definition = $this->getComponentDefinition($componentData['class']);
        $componentData['properties'] = \array_merge($definition->getDefaultProperties(), $componentData['properties']);
        foreach ($definition->getComponentProperties() as $property_name => $property_definition) {
            if (\strpos($property_name, '_') === 0) {
                // 				$this->_logger->debug( 'Skipping system property ['.$property_name.']');
                continue;
            }
            if (!\is_array($property_definition)) {
                // 				$this->_logger->debug( 'Skipping simple property ['.$property_name.']');
                continue;
            }
            if (isset($property_definition['valueType']) && $property_definition['valueType'] === 'class' && !empty($componentData['properties'][$property_name])) {
                $this->_logger->debug('Creating property [' . $property_name . ']');
                if (isset($property_definition['editor_properties']['multiple']) && $property_definition['editor_properties']['multiple']) {
                    $components = [];
                    foreach ($componentData['properties'][$property_name] as $component_data) {
                        $components[] = $packageProvider->createComponent($service, $component_data);
                    }
                    $componentData['properties'][$property_name] = $components;
                } else {
                    $componentData['properties'][$property_name] = $packageProvider->createComponent($service, $componentData['properties'][$property_name]);
                }
            }
        }
        /* @var \Convo\Core\Factory\IComponentFactory $factory */
        try {
            $factory = $definition->getProperty('_factory');
        } catch (\Convo\Core\ComponentNotFoundException $e) {
            $factory = new \Convo\Core\Factory\DefaultComponentFactory($componentData);
        }
        return $factory->createComponent($componentData['properties'], $service);
    }
    // DUMP DEFINITION
    public function getRow()
    {
        $data = array('namespace' => $this->_namespace, 'templates' => [], 'components' => [], 'intents' => [], 'entities' => []);
        foreach ($this->getComponentDefinitions() as $definition) {
            /* @var $definition ComponentDefinition */
            $data['components'][] = $definition->getRow();
        }
        foreach ($this->getTemplates() as $template) {
            $data['templates'][] = $template;
        }
        foreach ($this->getIntents() as $intent) {
            $data['intents'][] = $this->_intentToRow($intent);
        }
        foreach ($this->getEntities() as $entity) {
            $data['entities'][] = $this->_entityToRow($entity);
        }
        return $data;
    }
    private function _intentToRow(SystemIntent $intent)
    {
        $utterances = [];
        foreach ($intent->getPlatforms() as $platform) {
            $model = $intent->getPlatformModel($platform);
            foreach ($model->getUtterances() as $utterance) {
                $utterances[] = ['raw' => $utterance->getText(), 'model' => $utterance->getParts()];
            }
        }
        $row = ['name' => $this->getNamespace() . '.' . $intent->getName(), 'platforms' => $intent->getPlatforms(), 'utterances' => $utterances];
        return $row;
    }
    private function _entityToRow(SystemEntity $entity)
    {
        $row = ['name' => $this->getNamespace() . '.' . $entity->getName(), 'platforms' => $entity->getPlatforms()];
        return $row;
    }
    // UTIL
    protected function _loadFile($path)
    {
        $this->_logger->debug('Loading definition file from [' . $path . ']');
        $raw = \file_get_contents($path);
        if (\false === $raw) {
            throw new \Exception('Could not load file [' . $path . ']');
        }
        $data = \json_decode($raw, \true);
        if (\false === $data) {
            throw new \Exception('Invalid json data in file [' . $path . ']');
        }
        return $data;
    }
    // UTIL
    public function __toString()
    {
        return \get_class($this) . '[' . $this->_namespace . '][' . $this->_packageDir . ']';
    }
}
