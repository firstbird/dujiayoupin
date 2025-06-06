<?php

namespace Convoworks\PhpParser\Node\Expr;

use Convoworks\PhpParser\Node\Expr;
class PostDec extends Expr
{
    /** @var Expr Variable */
    public $var;
    /**
     * Constructs a post decrement node.
     *
     * @param Expr  $var        Variable
     * @param array $attributes Additional attributes
     */
    public function __construct(Expr $var, array $attributes = array())
    {
        parent::__construct($attributes);
        $this->var = $var;
    }
    public function getSubNodeNames()
    {
        return array('var');
    }
}
