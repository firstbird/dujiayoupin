<?php

declare (strict_types=1);
namespace Convo\Pckg\Core\Elements;

use Convo\Core\Preview\PreviewBlock;
use Convo\Core\Preview\PreviewSection;
class ElementsFragment extends \Convo\Pckg\Core\Elements\ElementCollection implements \Convo\Core\Workflow\IIdentifiableComponent, \Convo\Core\Workflow\IFragmentComponent
{
    private $_fragmentId;
    private $_fragmentName;
    public function __construct($properties)
    {
        parent::__construct($properties);
        $this->_fragmentId = $properties['fragment_id'];
        $this->_fragmentName = $properties['name'] ?? 'Nameless Elements Fragment';
    }
    public function getComponentId()
    {
        return $this->_fragmentId;
    }
    public function getName()
    {
        return $this->_fragmentId;
    }
    public function getWorkflowName()
    {
        return $this->_fragmentName;
    }
    // PREVIEW
    public function getPreview()
    {
        $pblock = new PreviewBlock($this->getName(), $this->getComponentId());
        $pblock->setLogger($this->_logger);
        // What the bot says first
        $section = new PreviewSection('Read', $this->_logger);
        $section->collect($this->getElements(), '\\Convo\\Core\\Preview\\IBotSpeechResource');
        $pblock->addSection($section);
        return $pblock;
    }
    // UTIL
    public function __toString()
    {
        return parent::__toString() . '[' . $this->_fragmentId . ']';
    }
}
