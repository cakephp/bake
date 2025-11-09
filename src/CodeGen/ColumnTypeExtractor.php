<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;

/**
 * Extracts column type mappings from existing Table class initialize methods.
 *
 * @internal
 */
class ColumnTypeExtractor extends NodeVisitorAbstract
{
    /**
     * @var \PhpParser\Parser
     */
    protected Parser $parser;

    /**
     * @var array<string, string>
     */
    protected array $columnTypes = [];

    /**
     * @var bool
     */
    protected bool $inInitialize = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $version = PhpVersion::fromComponents(8, 1);
        $this->parser = (new ParserFactory())->createForVersion($version);
    }

    /**
     * Extracts column type mappings from initialize method code
     *
     * @param string $code The initialize method code
     * @return array<string, string> Map of column names to type expressions
     */
    public function extract(string $code): array
    {
        $this->columnTypes = [];
        $this->inInitialize = false;

        try {
            // Wrap code in a dummy class if needed for parsing
            $wrappedCode = "<?php\nclass Dummy {\n" . $code . "\n}";
            $ast = $this->parser->parse($wrappedCode);

            $traverser = new NodeTraverser();
            $traverser->addVisitor($this);
            $traverser->traverse($ast);
        } catch (Exception $e) {
            // If parsing fails, return empty array
            return [];
        }

        return $this->columnTypes;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        // Check if we're entering the initialize method
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === 'initialize') {
            $this->inInitialize = true;

            return null;
        }

        // Only process nodes within initialize method
        if (!$this->inInitialize) {
            return null;
        }

        // Look for $this->getSchema()->setColumnType() calls
        if ($node instanceof Expression && $node->expr instanceof MethodCall) {
            $this->processMethodCall($node->expr);
        } elseif ($node instanceof MethodCall) {
            $this->processMethodCall($node);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === 'initialize') {
            $this->inInitialize = false;
        }

        return null;
    }

    /**
     * Process a method call to check if it's setColumnType
     *
     * @param \PhpParser\Node\Expr\MethodCall $methodCall The method call to process
     * @return void
     */
    protected function processMethodCall(MethodCall $methodCall): void
    {
        // Check if this is a setColumnType call
        if ($methodCall->name instanceof Node\Identifier && $methodCall->name->name === 'setColumnType') {
            // Check if it's called on getSchema()
            if (
                $methodCall->var instanceof MethodCall &&
                $methodCall->var->name instanceof Node\Identifier &&
                $methodCall->var->name->name === 'getSchema' &&
                $methodCall->var->var instanceof Variable &&
                $methodCall->var->var->name === 'this'
            ) {
                // Extract the column name and type expression
                if (count($methodCall->args) >= 2) {
                    $columnArg = $methodCall->args[0]->value;
                    $typeArg = $methodCall->args[1]->value;

                    // Get column name
                    $columnName = $this->getStringValue($columnArg);
                    if ($columnName === null) {
                        return;
                    }

                    // Get the type expression as a string
                    $typeExpression = $this->getTypeExpression($typeArg);
                    if ($typeExpression !== null) {
                        $this->columnTypes[$columnName] = $typeExpression;
                    }
                }
            }
        }
    }

    /**
     * Get string value from a node
     *
     * @param \PhpParser\Node $node The node to extract string from
     * @return string|null The string value or null
     */
    protected function getStringValue(Node $node): ?string
    {
        if ($node instanceof Node\Scalar\String_) {
            return $node->value;
        }

        return null;
    }

    /**
     * Convert a type expression node to string representation
     *
     * @param \PhpParser\Node $node The type expression node
     * @return string|null String representation of the type expression
     */
    protected function getTypeExpression(Node $node): ?string
    {
        // Handle EnumType::from() calls
        if (
            $node instanceof Node\Expr\StaticCall &&
            $node->class instanceof Node\Name &&
            $node->name instanceof Node\Identifier
        ) {
            $className = $node->class->toString();
            $methodName = $node->name->name;

            // Handle EnumType::from() calls
            if ($className === 'EnumType' || str_ends_with($className, '\\EnumType')) {
                if ($methodName === 'from' && count($node->args) > 0) {
                    // Extract the enum class name
                    $arg = $node->args[0]->value;
                    if ($arg instanceof Node\Expr\ClassConstFetch) {
                        if (
                            $arg->class instanceof Node\Name &&
                            $arg->name instanceof Node\Identifier &&
                            $arg->name->name === 'class'
                        ) {
                            $enumClass = $arg->class->toString();
                            // Return the full EnumType::from() expression
                            return 'EnumType::from(' . $enumClass . '::class)';
                        }
                    }
                }
            }
        }

        // Handle simple string types
        if ($node instanceof Node\Scalar\String_) {
            return '"' . $node->value . '"';
        }

        return null;
    }
}
