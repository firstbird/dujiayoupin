<?php

declare (strict_types=1);
namespace Convoworks\Zef\Zel\Symfony;

use Psr\Cache\CacheItemPoolInterface;
use Convoworks\Symfony\Component\Cache\Adapter\ArrayAdapter;
use Convoworks\Symfony\Component\ExpressionLanguage\ParsedExpression;
use Convoworks\Symfony\Component\ExpressionLanguage\Lexer;
use Convoworks\Symfony\Component\ExpressionLanguage\Compiler;
use Convoworks\Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Convoworks\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Convoworks\Symfony\Component\ExpressionLanguage\Expression;
use Convoworks\Zef\Zel\IValueAdapter;
/**
 * This class is copy/paste from Smyfony v4.4 and modified to use Zef\Zel\Parser
 */
/**
 * Allows to compile and evaluate expressions written in your own DSL.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExpressionLanguage
{
    private $cache;
    private $lexer;
    private $parser;
    private $compiler;
    protected $functions = [];
    private $newFunctionsAdded = \false;
    /**
     * @param ExpressionFunctionProviderInterface[] $providers
     */
    public function __construct(CacheItemPoolInterface $cache = null, array $providers = [])
    {
        $this->cache = $cache ?: new ArrayAdapter();
        $this->registerFunctions();
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }
    /**
     * Compiles an expression source code.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $names      An array of valid names
     *
     * @return string The compiled PHP source code
     */
    public function compile($expression, $names = [])
    {
        return $this->getCompiler()->compile($this->parse($expression, $names)->getNodes())->getSource();
    }
    /**
     * Evaluate an expression.
     *
     * @param Expression|string $expression The expression to compile
     * @param array             $values     An array of values
     *
     * @return mixed The result of the evaluation of the expression
     */
    public function evaluate($expression, $values = [])
    {
        $parsed = $this->parse($expression, \array_keys($values));
        $nodes = $parsed->getNodes();
        $value = $nodes->evaluate($this->functions, $values);
        if ($value instanceof IValueAdapter) {
            return $value->get();
        }
        return $value;
    }
    /**
     * Parses an expression.
     *
     * @param Expression|string $expression The expression to parse
     * @param array             $names      An array of valid names
     *
     * @return ParsedExpression A ParsedExpression instance
     */
    public function parse($expression, $names)
    {
        if ($expression instanceof ParsedExpression) {
            return $expression;
        }
        \asort($names);
        $cacheKeyItems = [];
        foreach ($names as $nameKey => $name) {
            $cacheKeyItems[] = \is_int($nameKey) ? $name : $nameKey . ':' . $name;
        }
        $cacheItem = $this->cache->getItem(\rawurlencode($expression . '//' . \implode('|', $cacheKeyItems)));
        if (null === ($parsedExpression = $cacheItem->get())) {
            $tokens = $this->getLexer()->tokenize((string) $expression);
            $nodes = $this->getParser()->parse($tokens, $names);
            $parsedExpression = new ParsedExpression((string) $expression, $nodes);
            $cacheItem->set($parsedExpression);
            $this->cache->save($cacheItem);
        }
        return $parsedExpression;
    }
    /**
     * Registers a function.
     *
     * @param string   $name      The function name
     * @param callable $compiler  A callable able to compile the function
     * @param callable $evaluator A callable able to evaluate the function
     *
     * @throws \LogicException when registering a function after calling evaluate(), compile() or parse()
     *
     * @see ExpressionFunction
     */
    public function register($name, callable $compiler, callable $evaluator)
    {
        // if (null !== $this->parser) {
        //     throw new \LogicException('Registering functions after calling evaluate(), compile() or parse() is not supported.');
        // }
        $this->functions[$name] = ['compiler' => $compiler, 'evaluator' => $evaluator];
        $this->newFunctionsAdded = \true;
    }
    public function addFunction(ExpressionFunction $function)
    {
        $this->register($function->getName(), $function->getCompiler(), $function->getEvaluator());
    }
    public function registerProvider(ExpressionFunctionProviderInterface $provider)
    {
        foreach ($provider->getFunctions() as $function) {
            $this->addFunction($function);
        }
    }
    public function reset()
    {
        $this->parser = null;
        $this->compiler = null;
        $this->lexer = null;
    }
    protected function registerFunctions()
    {
        $this->addFunction(ExpressionFunction::fromPhp('constant'));
    }
    private function getLexer() : Lexer
    {
        if (null === $this->lexer) {
            $this->lexer = new Lexer();
        }
        return $this->lexer;
    }
    private function getParser() : Parser
    {
        if (null === $this->parser || $this->newFunctionsAdded) {
            $this->parser = new Parser($this->functions);
            $this->newFunctionsAdded = \false;
            // Reset the flag
        }
        return $this->parser;
    }
    private function getCompiler() : Compiler
    {
        if (null === $this->compiler) {
            $this->compiler = new Compiler($this->functions);
        }
        return $this->compiler->reset();
    }
}
