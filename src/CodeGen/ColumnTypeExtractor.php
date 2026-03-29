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
    protected Parser $parser;

    /**
     * @var array<string, string>
     */
    protected array $columnTypes = [];

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
            if ($ast === null) {
                return [];
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor($this);
            $traverser->traverse($ast);
        } catch (Exception) {
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
        $isSetColumnTypeCall = $methodCall->name instanceof Node\Identifier
            && $methodCall->name->name === 'setColumnType';
        $schemaCall = $methodCall->var;
        $isSchemaMethodCall = $schemaCall instanceof MethodCall;
        $hasEnoughArguments = count($methodCall->args) >= 2;

        if (!$isSetColumnTypeCall || !$isSchemaMethodCall || !$hasEnoughArguments) {
            return;
        }

        $isGetSchemaCall = $schemaCall->name instanceof Node\Identifier
            && $schemaCall->name->name === 'getSchema';
        $isCalledOnThis = $schemaCall->var instanceof Variable
            && $schemaCall->var->name === 'this';

        if (!$isGetSchemaCall || !$isCalledOnThis) {
            return;
        }

        $columnArgNode = $methodCall->args[0];
        $typeArgNode = $methodCall->args[1];
        if (!$columnArgNode instanceof Node\Arg || !$typeArgNode instanceof Node\Arg) {
            return;
        }

        $columnArg = $columnArgNode->value;
        $typeArg = $typeArgNode->value;

        $columnName = $this->getStringValue($columnArg);
        if ($columnName === null) {
            return;
        }

        $typeExpression = $this->getTypeExpression($typeArg);
        if ($typeExpression !== null) {
            $this->columnTypes[$columnName] = $typeExpression;
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
        if ($node instanceof Node\Expr\StaticCall) {
            $staticCall = $node;
            $calledClass = $staticCall->class;
            $calledMethod = $staticCall->name;

            $hasNamedClass = $calledClass instanceof Node\Name;
            $hasIdentifierMethod = $calledMethod instanceof Node\Identifier;
            if (!$hasNamedClass || !$hasIdentifierMethod) {
                return null;
            }

            $className = $calledClass->toString();
            $methodName = $calledMethod->name;
            $isEnumTypeClass = $className === 'EnumType' || str_ends_with($className, '\\EnumType');
            $isFromMethod = $methodName === 'from';
            $hasArguments = $staticCall->args !== [];
            if (!$isEnumTypeClass || !$isFromMethod || !$hasArguments) {
                return null;
            }

            $argNode = $staticCall->args[0];
            if (!$argNode instanceof Node\Arg) {
                return null;
            }

            $arg = $argNode->value;
            if (!$arg instanceof Node\Expr\ClassConstFetch) {
                return null;
            }

            $enumClassNode = $arg->class;
            $constantName = $arg->name;
            $hasNamedEnumClass = $enumClassNode instanceof Node\Name;
            $isClassConstant = $constantName instanceof Node\Identifier
                && $constantName->name === 'class';
            if (!$hasNamedEnumClass || !$isClassConstant) {
                return null;
            }

            $enumClass = $enumClassNode->toString();

            return 'EnumType::from(' . $enumClass . '::class)';
        }

        if ($node instanceof Node\Scalar\String_) {
            return '"' . $node->value . '"';
        }

        return null;
    }
}
