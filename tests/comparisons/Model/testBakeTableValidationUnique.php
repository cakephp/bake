<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UniqueFields Model
 *
 * @method \Bake\Test\App\Model\Entity\UniqueField newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\UniqueField newEntity(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\UniqueField[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class UniqueFieldsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('unique_fields');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->allowEmptyString('username');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('field_1')
            ->maxLength('field_1', 255)
            ->allowEmptyString('field_1')
            ->add('field_1', 'unique', [
                'rule' => [
                    'validateUnique',
                    [
                        'pass' => [
                            'scope' => ['field_2'],
                        ],
                    ],
                ],
                'provider' => 'table',
            ]);

        $validator
            ->scalar('field_2')
            ->maxLength('field_2', 255)
            ->allowEmptyString('field_2')
            ->add('field_2', 'unique', [
                'rule' => [
                    'validateUnique',
                    [
                        'pass' => [
                            'scope' => ['field_1'],
                        ],
                    ],
                ],
                'provider' => 'table',
            ]);

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'test';
    }
}
