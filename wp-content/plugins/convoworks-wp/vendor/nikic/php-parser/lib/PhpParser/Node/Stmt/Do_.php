<?php

namespace Convoworks\PhpParser\Node\Stmt;

use Convoworks\PhpParser\Node;
class Do_ extends Node\Stmt
{
    /** @var Node\Expr Condition */
    public $cond;
    /** @var Node[] Statements */
    public $stmts;
    /**
     * Constructs a do while node.
     *
     * @param Node\Expr $cond       Condition
     * @param Node[]    $stmts      Statements
     * @param array     $attributes Additional attributes
     */
    public function __construct(Node\Expr $cond, array $stmts = array(), array $attributes = array())
    {
        parent::__construct($attributes);
        $this->cond = $cond;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames()
    {
        return array('cond', 'stmts');
    }
}
