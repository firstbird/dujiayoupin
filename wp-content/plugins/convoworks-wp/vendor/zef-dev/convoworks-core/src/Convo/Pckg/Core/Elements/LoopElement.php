<?php

declare (strict_types=1);
namespace Convo\Pckg\Core\Elements;

class LoopElement extends \Convo\Core\Workflow\AbstractWorkflowContainerComponent implements \Convo\Core\Workflow\IConversationElement
{
    /**
     * @var \Convo\Core\Workflow\IConversationElement[]
     */
    private $_elements = array();
    /** @var array */
    private $_dataCollection;
    private $_item;
    private $_loop_until;
    private $_offset;
    private $_limit;
    public function __construct($properties)
    {
        parent::__construct($properties);
        $this->_dataCollection = $properties['data_collection'];
        $this->_item = $properties['item'];
        $this->_loop_until = $properties['loop_until'];
        $this->_offset = $properties['offset'];
        $this->_limit = $properties['limit'];
        if (isset($properties['elements'])) {
            foreach ($properties['elements'] as $element) {
                $this->addElement($element);
            }
        }
    }
    public function read(\Convo\Core\Workflow\IConvoRequest $request, \Convo\Core\Workflow\IConvoResponse $response)
    {
        $items = $this->evaluateString($this->_dataCollection);
        if (!\is_array($items) && !$items instanceof \Iterator && !$items instanceof \IteratorAggregate) {
            throw new \Exception('Excepted to find iterable for [' . $this->_dataCollection . '] got [' . \gettype($items) . ']');
        }
        if (\is_array($items)) {
            $items = new \ArrayIterator($items);
        } else {
            if ($items instanceof \IteratorAggregate) {
                $items = $items->getIterator();
            }
        }
        $slot_name = $this->evaluateString($this->_item);
        $scope_type = \Convo\Core\Params\IServiceParamsScope::SCOPE_TYPE_REQUEST;
        $params = $this->getService()->getComponentParams($scope_type, $this);
        $start = 0;
        if (\is_countable($items)) {
            $end = \count($items);
        } else {
            if ($items instanceof \Iterator) {
                $end = \iterator_count($items);
                $items->rewind();
            } else {
                throw new \Exception('Could not count [' . $this->_dataCollection . '] got [' . \gettype($items) . ']');
            }
        }
        $offset = $this->evaluateString($this->_offset);
        $limit = $this->evaluateString($this->_limit);
        if ($offset !== null && $offset !== '') {
            if ($offset > $end || $offset < 0) {
                $this->_logger->warning('Offset [' . $offset . '] falls outside the range [' . $start . ', ' . $end . ']. Starting from 0.');
            } else {
                $start = $offset;
            }
        }
        if ($limit !== null && $limit !== '') {
            /** @var int $limit */
            $limit = \intval($limit);
            $limit = \abs($limit);
            $end = \min($start + $limit, $end);
        }
        $this->_logger->debug('Offset [' . $offset . ']  start & end [' . $start . ', ' . $end . ']');
        $i = 0;
        foreach ($items as $item) {
            if ($i < $start) {
                $i++;
                continue;
            }
            if ($i >= $end) {
                break;
            }
            $params->setServiceParam($slot_name, ['value' => $item, 'index' => $i, 'natural' => $i + 1, 'first' => $i === $start, 'last' => $i === $end - 1]);
            $loop_until = $this->evaluateString($this->_loop_until);
            if ($loop_until) {
                $this->_logger->info('Exiting loop.');
                break;
            }
            foreach ($this->_elements as $element) {
                $element->read($request, $response);
            }
            $i++;
        }
    }
    public function addElement(\Convo\Core\Workflow\IConversationElement $element)
    {
        $this->_elements[] = $element;
        $this->addChild($element);
    }
    /**
     * @return \Convo\Core\Workflow\IConversationElement[]
     */
    public function getElements()
    {
        return $this->_elements;
    }
    // UTIL
    public function __toString()
    {
        return parent::__toString() . '[' . \count($this->_elements) . ']';
    }
}
