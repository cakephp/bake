<?php
namespace App\Model\Table;

use App\Model\Entity\CategoriesProduct;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CategoriesProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method CategoriesProduct get($primaryKey, $options = [])
 * @method CategoriesProduct newEntity($data = null, array $options = [])
 * @method CategoriesProduct[] newEntities(array $data, array $options = [])
 * @method CategoriesProduct save(EntityInterface $entity, $options = [])
 * @method CategoriesProduct patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method CategoriesProduct[] patchEntities($entities, array $data, array $options = [])
 * @method CategoriesProduct findOrCreate($search, callable $callback = null)
 */
class CategoriesProductsTable extends Table
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

        $this->table('categories_products');
        $this->displayField('category_id');
        $this->primaryKey(['category_id', 'product_id']);

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));
        return $rules;
    }
}
