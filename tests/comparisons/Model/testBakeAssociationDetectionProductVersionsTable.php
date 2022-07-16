<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ProductVersions Model
 *
 * @property \Bake\Test\App\Model\Table\ProductsTable&\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \Bake\Test\App\Model\Entity\ProductVersion newEmptyEntity()
 * @method \Bake\Test\App\Model\Entity\ProductVersion newEntity(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \Bake\Test\App\Model\Entity\ProductVersion[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class ProductVersionsTable extends Table
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

        $this->setTable('product_versions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);
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
            ->integer('product_id')
            ->notEmptyString('product_id');

        $validator
            ->dateTime('version')
            ->requirePresence('version', 'create')
            ->notEmptyDateTime('version');

        return $validator;
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
        $rules->add($rules->existsIn('product_id', 'Products'), ['errorField' => 'product_id']);

        return $rules;
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
