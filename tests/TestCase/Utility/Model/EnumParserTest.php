<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\TestCase\Utility\Model;

use Bake\Utility\Model\EnumParser;
use Cake\TestSuite\TestCase;

/**
 * BakeViewTest class
 */
class EnumParserTest extends TestCase
{
    /**
     * @return void
     */
    public function testParseDefinitionString(): void
    {
        $definitionString = EnumParser::parseDefinitionString('[enum]');
        $this->assertSame('', $definitionString);

        $definitionString = EnumParser::parseDefinitionString('[enum] foo, bar');
        $this->assertSame('foo, bar', $definitionString);

        $definitionString = EnumParser::parseDefinitionString('[enum] foo, bar; Some additional comment');
        $this->assertSame('foo, bar', $definitionString);
    }

    /**
     * @return void
     */
    public function testParseCases(): void
    {
        $cases = EnumParser::parseCases('', false);
        $this->assertSame([], $cases);

        $cases = EnumParser::parseCases('foo, bar', false);
        $this->assertSame(['foo' => 'foo', 'bar' => 'bar'], $cases);

        $cases = EnumParser::parseCases('foo:f, bar:b', false);
        $this->assertSame(['foo' => 'f', 'bar' => 'b'], $cases);

        $cases = EnumParser::parseCases('foo:0, bar:1', true);
        $this->assertSame(['foo' => 0, 'bar' => 1], $cases);

        $cases = EnumParser::parseCases('foo, bar', true);
        $this->assertSame(['foo' => 0, 'bar' => 1], $cases);
    }
}
