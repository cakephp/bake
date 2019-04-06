<?php
namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CategoriesProducts Model
 *
 * @property \Bake\Test\App\Model\Table\CategoriesTable|\Cake\ORM\Association\BelongsTo $Categories
 * @property \Bake\Test\App\Model\Table\ProductsTable|\Cake\ORM\Association\BelongsTo $Products
 *
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct newEntity($data = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\CategoriesProduct findOrCreate($search, callable $callback = null, $options = [])
 */
class CategoriesProductsTable extends Table
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

        $this->setTable('categories_products');
        $this->setDisplayField('category_id');
        $this->setPrimaryKey(['category_id', 'product_id']);

        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER',
        ]);
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
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

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
