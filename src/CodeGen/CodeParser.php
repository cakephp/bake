<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.8.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\CodeGen;

use PhpParser\Error;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeAbstract;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

/**
 * @internal
 */
class CodeParser extends NodeVisitorAbstract
{
    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var \PhpParser\NodeTraverser
     */
    protected $traverser;

    /**
     * @var string
     */
    protected $fileText = '';

    /**
     * @var array
     */
    protected $parsed = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(
            ParserFactory::PREFER_PHP7,
            new Emulative([
                'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
            ])
        );
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);
    }

    /**
     * @param string $code Code to parse
     * @return \Bake\CodeGen\ParsedFile
     * @throws \Bake\CodeGen\ParseException
     */
    public function parseFile(string $code): ParsedFile
    {
        $this->fileText = $code;
        try {
            $this->traverser->traverse($this->parser->parse($code));
        } catch (Error $e) {
            throw new ParseException($e->getMessage(), null, $e);
        }

        return new ParsedFile(
            $this->parsed['namespace'],
            $this->parsed['classImports'],
            $this->parsed['functionImports'],
            $this->parsed['constImports'],
            $this->parsed['class']
        );
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        $this->parsed = [
            'classImports' => [],
            'functionImports' => [],
            'constImports' => [],
        ];

        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            if (isset($this->parsed['namespace'])) {
                throw new ParseException('Multiple namespaces are not not supported');
            }
            $this->parsed['namespace'] = (string)$node->name;

            return null;
        }

        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                [$alias, $target] = $this->normalizeUse($use);
                switch ($node->type) {
                    case Use_::TYPE_NORMAL:
                        $this->parsed['classImports'][$alias] = $target;
                        break;
                    case Use_::TYPE_FUNCTION:
                        $this->parsed['functionImports'][$alias] = $target;
                        break;
                    case Use_::TYPE_CONSTANT:
                        $this->parsed['constImports'][$alias] = $target;
                        break;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof GroupUse) {
            $prefix = (string)$node->prefix;
            foreach ($node->uses as $use) {
                [$alias, $target] = $this->normalizeUse($use, $prefix);
                switch ($node->type != Use_::TYPE_UNKNOWN ? $node->type : $use->type) {
                    case Use_::TYPE_NORMAL:
                        $this->parsed['classImports'][$alias] = $target;
                        break;
                    case Use_::TYPE_FUNCTION:
                        $this->parsed['functionImports'][$alias] = $target;
                        break;
                    case Use_::TYPE_CONSTANT:
                        $this->parsed['constImports'][$alias] = $target;
                        break;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Class_) {
            if (!isset($this->parsed['namespace'])) {
                throw new ParseException('Classes must be defined in a namespace');
            }
            if (isset($this->parsed['class'])) {
                throw new ParseException('Only one class can be defined');
            }

            $constants = [];
            foreach ($node->getConstants() as $constant) {
                if (count($constant->consts) > 1) {
                    throw new ParseException('Multiple constants per line are not supported, update your file');
                }

                $name = (string)current($constant->consts)->name;
                $constants[$name] = $this->getNodeCode($constant);
            }

            $properties = [];
            foreach ($node->getProperties() as $property) {
                if (count($property->props) > 1) {
                    throw new ParseException('Multiple properties per line are not supported, update your file');
                }

                $name = (string)current($property->props)->name;
                $properties[$name] = $this->getNodeCode($property);
            }

            $methods = [];
            foreach ($node->getMethods() as $method) {
                $name = (string)$method->name;
                $methods[$name] = $this->getNodeCode($method);
            }

            $this->parsed['class'] = new ParsedClass((string)$node->name, $constants, $properties, $methods);

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @param \PhpParser\NodeAbstract $node Parser node
     * @return string
     */
    protected function getNodeCode(NodeAbstract $node): string
    {
        $startPos = $node->getStartFilePos();
        $endPos = $node->getEndFilePos();
        $code = '    ' . substr($this->fileText, $startPos, $endPos - $startPos + 1);

        $doc = $node->getDocComment() ? $node->getDocComment()->getText() : null;
        if ($doc) {
            $code = sprintf("    %s\n%s", $doc, $code);
        }

        return $code;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        if (!isset($this->parsed['namespace'], $this->parsed['class'])) {
            throw new ParseException('Unable to parse file');
        }

        return null;
    }

    /**
     * @param \PhpParser\Node\Stmt\UseUse $use Use node
     * @param string|null $prefix Group use prefix
     * @return array{string, string}
     */
    protected function normalizeUse(UseUse $use, ?string $prefix = null): array
    {
        $name = (string)$use->name;
        if ($prefix) {
            $name = $prefix . '\\' . $name;
        }

        $alias = $use->alias;
        if (!$alias) {
            $last = strrpos($name, '\\', -1);
            if ($last !== false) {
                $alias = substr($name, strrpos($name, '\\', -1) + 1);
            } else {
                $alias = $name;
            }
        }

        return [(string)$alias, $name];
    }
}
