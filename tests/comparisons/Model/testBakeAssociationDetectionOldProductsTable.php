<?php
namespace App\Model\Table;

use App\Model\Entity\OldProduct;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OldProducts Model
 *
 * @method OldProduct get($primaryKey, $options = [])
 * @method OldProduct newEntity($data = null, array $options = [])
 * @method OldProduct[] newEntities(array $data, array $options = [])
 * @method OldProduct save(EntityInterface $entity, $options = [])
 * @method OldProduct patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method OldProduct[] patchEntities($entities, array $data, array $options = [])
 * @method OldProduct findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OldProductsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('old_products');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }
}
