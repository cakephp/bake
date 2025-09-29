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
namespace Bake\Test\TestCase\CodeGen;

use Bake\CodeGen\ColumnTypeExtractor;
use Cake\TestSuite\TestCase;

/**
 * ColumnTypeExtractor test
 */
class ColumnTypeExtractorTest extends TestCase
{
    /**
     * @var \Bake\CodeGen\ColumnTypeExtractor
     */
    protected ColumnTypeExtractor $extractor;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->extractor = new ColumnTypeExtractor();
    }

    /**
     * Test extracting enum column types
     *
     * @return void
     */
    public function testExtractEnumTypes(): void
    {
        $code = <<<'PHP'
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('issue_activities');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->getSchema()->setColumnType('activity_type', \Cake\Database\Type\EnumType::from(\App\Model\Enum\IssueActivityTypes::class));
        $this->getSchema()->setColumnType('status', \Cake\Database\Type\EnumType::from(\App\Model\Enum\StatusEnum::class));

        $this->belongsTo('Issues', [
            'foreignKey' => 'issue_id',
            'joinType' => 'INNER',
        ]);
    }
PHP;

        $result = $this->extractor->extract($code);

        $expected = [
            'activity_type' => 'EnumType::from(App\Model\Enum\IssueActivityTypes::class)',
            'status' => 'EnumType::from(App\Model\Enum\StatusEnum::class)',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test extracting with fully qualified class names
     *
     * @return void
     */
    public function testExtractWithFullyQualifiedNames(): void
    {
        $code = <<<'PHP'
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->getSchema()->setColumnType('type', \Cake\Database\Type\EnumType::from(\My\App\Model\Enum\TypeEnum::class));
    }
PHP;

        $result = $this->extractor->extract($code);

        $expected = [
            'type' => 'EnumType::from(My\App\Model\Enum\TypeEnum::class)',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test extracting no column types from empty method
     *
     * @return void
     */
    public function testExtractEmptyMethod(): void
    {
        $code = <<<'PHP'
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
    }
PHP;

        $result = $this->extractor->extract($code);

        $this->assertEquals([], $result);
    }

    /**
     * Test extracting with mixed content
     *
     * @return void
     */
    public function testExtractMixedContent(): void
    {
        $code = <<<'PHP'
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');

        // Custom column type mapping
        $this->getSchema()->setColumnType('status', \Cake\Database\Type\EnumType::from(\App\Model\Enum\ArticleStatus::class));

        $this->addBehavior('Timestamp');

        // Another custom mapping
        $this->getSchema()->setColumnType('priority', \Cake\Database\Type\EnumType::from(\App\Model\Enum\PriorityEnum::class));

        $this->belongsTo('Users');
    }
PHP;

        $result = $this->extractor->extract($code);

        $expected = [
            'status' => 'EnumType::from(App\Model\Enum\ArticleStatus::class)',
            'priority' => 'EnumType::from(App\Model\Enum\PriorityEnum::class)',
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test extracting with invalid code returns empty array
     *
     * @return void
     */
    public function testExtractInvalidCode(): void
    {
        $code = 'this is not valid PHP code {';

        $result = $this->extractor->extract($code);

        $this->assertEquals([], $result);
    }
}
