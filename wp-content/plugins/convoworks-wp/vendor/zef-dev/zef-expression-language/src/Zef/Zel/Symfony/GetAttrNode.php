<?php

declare (strict_types=1);
namespace Convoworks\Zef\Zel\Symfony;

class GetAttrNode extends \Convoworks\Symfony\Component\ExpressionLanguage\Node\GetAttrNode
{
    public function evaluate($functions, $values)
    {
        switch ($this->attributes['type']) {
            case self::PROPERTY_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                if (\is_null($obj)) {
                    return null;
                }
                if (empty($obj)) {
                    return null;
                }
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object. [' . \gettype($obj) . ']');
                }
                $property = $this->nodes['attribute']->attributes['value'];
                if (!$this->_shouldCallMethod($this->attributes['type'], $obj, $property)) {
                    return null;
                }
                return $obj->{$property};
            case self::METHOD_CALL:
                $obj = $this->nodes['node']->evaluate($functions, $values);
                $method = $this->nodes['attribute']->attributes['value'];
                if (\is_null($obj)) {
                    return null;
                }
                if (!\is_object($obj)) {
                    throw new \RuntimeException('Unable to get a property on a non-object.');
                }
                if (!\is_callable($toCall = [$obj, $this->nodes['attribute']->attributes['value']])) {
                    throw new \RuntimeException(\sprintf('Unable to call method "%s" of object "%s".', $this->nodes['attribute']->attributes['value'], \get_class($obj)));
                }
                if (!$this->_shouldCallMethod($this->attributes['type'], $obj, $method)) {
                    return null;
                }
                return $toCall(...\array_values($this->nodes['arguments']->evaluate($functions, $values)));
            case self::ARRAY_CALL:
                $array = $this->nodes['node']->evaluate($functions, $values);
                if (\is_null($array)) {
                    return null;
                }
                if (!\is_array($array) && !$array instanceof \ArrayAccess) {
                    throw new \RuntimeException('Unable to get an item on a non-array.');
                }
                return $array[$this->nodes['attribute']->evaluate($functions, $values)] ?? null;
        }
    }
    private function _shouldCallMethod($callType, $object, $value)
    {
        $isAccessible = 0;
        if (\is_array($object)) {
            return \true;
        }
        if (\is_a($object, 'Convoworks\\Zef\\Zel\\IValueAdapter') && \is_array($object->get())) {
            return \true;
        }
        switch ($callType) {
            case self::PROPERTY_CALL:
                if (\in_array($value, \array_keys(\get_object_vars($object)))) {
                    $isAccessible++;
                }
                if (\is_a($object, 'Convoworks\\Zef\\Zel\\IValueAdapter') && \in_array($value, \array_keys(\get_object_vars($object->get())))) {
                    $isAccessible++;
                }
                $wrappedClassMethods = \get_class_methods($object);
                foreach ($wrappedClassMethods as $wrappedClassMethod) {
                    if (\str_contains(\strtolower($wrappedClassMethod), \strtolower($value))) {
                        $isAccessible++;
                    }
                }
                if (\is_a($object, 'Convoworks\\Zef\\Zel\\IValueAdapter')) {
                    $originalClassMethods = \get_class_methods($object->get());
                    foreach ($originalClassMethods as $originalClassMethod) {
                        if (\str_contains(\strtolower($originalClassMethod), \strtolower($value))) {
                            $isAccessible++;
                        }
                    }
                }
                break;
            case self::METHOD_CALL:
                if (\in_array($value, \get_class_methods($object))) {
                    $isAccessible++;
                }
                if (\is_a($object, 'Convoworks\\Zef\\Zel\\IValueAdapter') && \in_array($value, \get_class_methods($object->get()))) {
                    $isAccessible++;
                }
                break;
            default:
                break;
        }
        if (empty($isAccessible)) {
            return \false;
        }
        return \true;
    }
}
