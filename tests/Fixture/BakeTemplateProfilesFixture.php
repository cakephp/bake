<?php
/**
 * Created by javier
 * Date: 31/01/16
 * Time: 09:19
 */

namespace Bake\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture used for test bake template task
 *
 * @package Bake\Test\Fixture
 * @see TemplateTaskTest::testBakeTemplate
 */
class BakeTemplateProfilesFixture extends TestFixture
{
    
    /**
     * @var string
     */
    public $table = 'profiles';
    
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer', 'null' => false],
        'nick' => ['type' => 'string', 'null' => false],
        'avatar' => ['type' => 'string', 'default' => null],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['author_id' => 1, 'nick' => 'The Comedian', 'avatar' => 'smiley.png'],
        ['author_id' => 2, 'nick' => 'Rorschach', 'avatar' => 'stains.png'],
        ['author_id' => 3, 'nick' => 'Ozymandias', 'avatar' => null],
        ['author_id' => 4, 'nick' => 'Dr. Manhattan', 'avatar' => 'blue_lightning.png'],
    ];
}
