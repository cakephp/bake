<?php
declare(strict_types=1);

namespace Bake\Test\App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DatatypesFixture
 */
class DatatypesFixture extends TestFixture
{
    /**
     * Import
     *
     * @var array<string, mixed>
     */
    public array $import = ['table' => 'datatypes', 'connection' => 'test'];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'decimal_field' => '30.123',
                'float_field' => 42.23,
                'huge_int' => 1234567891234567891,
                'small_int' => 1234,
                'tiny_int' => 12,
                'bool' => false,
                'uuid' => null,
                'timestamp_field' => '2007-03-17 01:16:23',
            ],
        ];
        parent::init();
    }
}
