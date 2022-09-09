<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ParseTestTable Model
 *
 * @method \Bake\Test\App\Model\Entity\ParseTestTable newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\ParseTestTable newEntity(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ParseTestTable[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ParseTestTable extends Table
{
    /**
     * @var int
     */
    protected const SOME_CONST = 1;

    /**
     * @var string
     */
    protected $withDocProperty = <<<'TEXT'
    BLOCK OF TEXT
TEXT;

    protected $withoutDocProperty = 1;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('test');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);

        return $rules;
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
            ->numeric('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('username')
            ->maxLength('username', 100, 'Name must be shorter than 100 characters.')
            ->requirePresence('username', 'create')
            ->allowEmptyString('username', null, false);

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

    /**
     * @param \Cake\ORM\Query $query Finder query
     * @param array $options Finder options
     * @return \Cake\ORM\Query
     */
    public function findTest(Query $query, array $options): Query
    {
        return $query;
    }
}
