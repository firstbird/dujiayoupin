<?php

declare (strict_types=1);
namespace Convo\Pckg\Appointments\Freeslot;

class DefaultFreeSlotValidator implements \Convo\Pckg\Appointments\Freeslot\IFreeSlotValidator
{
    /**
     * @var \DateTime
     **/
    protected $_time;
    protected $_value;
    public function __construct($time)
    {
        $this->_time = $time;
    }
    /**
     * @param array $item
     * @return bool
     */
    public function add($item)
    {
        $this->_value = $item;
        return \true;
    }
    /**
     * @param array $item
     * @return bool
     */
    public function active()
    {
        if (isset($this->_value)) {
            return \false;
        }
        return \true;
    }
    /**
     * @return array
     */
    public function values()
    {
        if (isset($this->_value)) {
            return [$this->_value];
        }
        return [];
    }
}
