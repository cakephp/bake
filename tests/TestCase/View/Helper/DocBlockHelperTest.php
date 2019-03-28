<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.9.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Bake\Test\TestCase\View\Helper;

use Bake\View\BakeView;
use Bake\View\Helper\DocBlockHelper;
use Cake\Http\ServerRequest as Request;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Cake\TestSuite\Stub\Response;
use Cake\TestSuite\TestCase;

/**
 * DocBlockHelper Test
 *
 * @coversDefaultClass \Bake\View\Helper\DocBlockHelper
 */
class DocBlockHelperTest extends TestCase
{
    /**
     * @var BakeView
     */
    protected $View;

    /**
     * @var DocBlockHelper
     */
    protected $DocBlockHelper;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $request = new Request();
        $response = new Response();
        $this->View = new BakeView($request, $response);
        $this->DocBlockHelper = new DocBlockHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->DocBlockHelper);
    }

    /**
     * Tests the classDescription method including annotation spacing
     *
     * @return void
     * @covers ::classDescription
     */
    public function testClassDescription()
    {
        $className = 'Comments';
        $classType = 'Model';
        $lines = [
            'Line 1',
            '@foo $bar baz',
            '@see there',
        ];
        $classDescription = $this->DocBlockHelper->classDescription($className, $classType, $lines);
        $expected = "/**\n * Comments Model\n *\n * Line 1\n * @foo \$bar baz\n *\n * @see there\n */";
        $this::assertSame($expected, $classDescription);
    }

    /**
     * Tests the associatedEntityTypeToHintType method
     *
     * @return void
     * @covers ::associatedEntityTypeToHintType
     */
    public function testAssociatedEntityTypeToHintType()
    {
        // Test with MANY_TO_MANY
        $type = 'Foo';
        $association = new BelongsToMany('Foo');
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Foo[]';
        $this->assertSame($expected, $assocEntityType);

        // Test with ONE_TO_MANY
        $type = 'Bar';
        $association = new HasMany('Bar');
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Bar[]';
        $this->assertSame($expected, $assocEntityType);

        // Test with ONE_TO_ONE
        $type = 'Ping';
        $association = new HasOne('Ping');
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Ping';
        $this->assertSame($expected, $assocEntityType);

        // Test with MANY_TO_ONE
        $type = 'Pong';
        $association = new BelongsTo('Pong');
        $assocEntityType = $this->DocBlockHelper->associatedEntityTypeToHintType($type, $association);
        $expected = 'Pong';
        $this->assertSame($expected, $assocEntityType);
    }

    /**
     * Tests the buildEntityPropertyHintTypeMap method
     *
     * @return void
     * @covers ::buildEntityPropertyHintTypeMap
     */
    public function testBuildEntityPropertyHintTypeMap()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the buildEntityAssociationHintTypeMap method
     *
     * @return void
     * @covers ::buildEntityAssociationHintTypeMap
     * @covers ::_insertAfter
     */
    public function testBuildEntityAssociationHintTypeMap()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the columnTypeToHintType method
     *
     * @return void
     * @covers ::columnTypeToHintType
     */
    public function testColumnTypeToHintType()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the propertyHints method
     *
     * @return void
     * @covers ::propertyHints
     */
    public function testPropertyHints()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    /**
     * Tests the buildTableAnnotations method
     *
     * @return void
     * @covers ::buildTableAnnotations
     */
    public function testBuildTableAnnotations()
    {
        $this->markTestIncomplete('Not implemented yet');
    }
}
