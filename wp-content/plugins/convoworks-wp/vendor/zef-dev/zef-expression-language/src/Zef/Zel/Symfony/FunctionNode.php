<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Zef\Zel\Symfony;

use Convoworks\Symfony\Component\ExpressionLanguage\Compiler;
use Convoworks\Zef\Zel\IValueAdapter;
use Convoworks\Symfony\Component\ExpressionLanguage\Node\Node;
use Convoworks\Zef\Zel\AbstractResolver;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
class FunctionNode extends Node
{
    public function __construct(string $name, Node $arguments)
    {
        parent::__construct(['arguments' => $arguments], ['name' => $name]);
    }
    public function compile(Compiler $compiler)
    {
        $arguments = [];
        foreach ($this->nodes['arguments']->nodes as $node) {
            $arguments[] = $compiler->subcompile($node);
        }
        $function = $compiler->getFunction($this->attributes['name']);
        $compiler->raw($function['compiler'](...$arguments));
    }
    public function evaluate(array $functions, array $values)
    {
        $arguments = [$values];
        foreach ($this->nodes['arguments']->nodes as $node) {
            $arguments[] = $node->evaluate($functions, $values);
        }
        $fixed = [];
        foreach ($arguments as $argument) {
            if ($argument instanceof IValueAdapter) {
                \error_log('Trimming array value adapter');
                $fixed[] = $argument->get();
            } else {
                $fixed[] = AbstractResolver::cleanValue($argument);
            }
        }
        return $functions[$this->attributes['name']]['evaluator'](...$fixed);
    }
    public function toArray()
    {
        $array = [];
        $array[] = $this->attributes['name'];
        foreach ($this->nodes['arguments']->nodes as $node) {
            $array[] = ', ';
            $array[] = $node;
        }
        $array[1] = '(';
        $array[] = ')';
        return $array;
    }
}
