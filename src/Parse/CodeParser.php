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
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Parse;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * @internal
 */
final class CodeParser extends NodeVisitorAbstract
{
    protected Parser $parser;

    protected NodeTraverser $traverser;

    protected string $code = '';

    protected array $parsed = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(
            ParserFactory::PREFER_PHP7,
            new Emulative([
                'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
            ]),
        );
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);
    }

    /**
     * @param string $code Code to parse
     * @param string $className Class name expected in file
     * @return \Bake\Parse\ParsedClass
     * @throws \Bake\Parse\ParseException
     */
    public function parseClass(string $code, ?string $className = null): ParsedClass
    {
        $this->code = $code;
        $this->traverser->traverse($this->parser->parse($code));

        return new ParsedClass(...$this->parsed);
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        $this->parsed = [
            'namespace' => null,
            'name' => null,
            'uses' => [
                'classes' => [],
                'funtions' => [],
                'constants' => [],
            ],
            'methods' => [],
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
                throw new ParseException('Multiple namespaces in a file is not supported.');
            }
            $this->parsed['namespace'] = (string)$node->name;

            return null;
        }

        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                switch ($node->type) {
                    case Use_::TYPE_NORMAL:
                        $this->parsed['uses']['classes'] += $this->normalizeUse($use);
                        break;
                    case Use_::TYPE_FUNCTION:
                        $this->parsed['uses']['functions'] += $this->normalizeUse($use);
                        break;
                    case Use_::TYPE_CONSTANT:
                        $this->parsed['uses']['constants'] += $this->normalizeUse($use);
                        break;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof GroupUse) {
            $prefix = (string)$node->prefix;
            foreach ($node->uses as $use) {
                switch ($use->type) {
                    case Use_::TYPE_NORMAL:
                        $this->parsed['uses']['classes'] += $this->normalizeUse($use, $prefix);
                        break;
                    case Use_::TYPE_FUNCTION:
                        $this->parsed['uses']['functions'] += $this->normalizeUse($use, $prefix);
                        break;
                    case Use_::TYPE_CONSTANT:
                        $this->parsed['uses']['constants'] += $this->normalizeUse($use, $prefix);
                        break;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Class_) {
            $this->parsed['name'] = (string)$node->name;

            foreach ($node->getMethods() as $method) {
                $startPos = $method->getStartFilePos();
                $endPos = $method->getEndFilePos();
                $code = '    ' . substr($this->code, $startPos, $endPos - $startPos + 1);

                $doc = $method->getDocComment()?->getText();
                $doc = $doc ? '    ' . $doc : null;

                $name = (string)$method->name;
                $this->parsed['methods'][$name] = new ParsedMethod(
                    $name,
                    $code,
                    $doc,
                    []
                );
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        if (!isset($this->parsed['namespace'], $this->parsed['name'])) {
            throw new ParseException('Could not find namespaced class.');
        }

        return null;
    }

    /**
     * @param \PhpParser\Node\Stmt\UseUse $use Use node
     * @param string|null $prefix Group use prefix
     * @return array<string, string>
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

        return [$name => (string)$alias];
    }
}
