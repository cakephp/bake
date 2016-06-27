<?php
namespace App\Model\Table;

use App\Model\Entity\BakeArticle;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BakeArticles Model
 *
 * @method BakeArticle get($primaryKey, $options = [])
 * @method BakeArticle newEntity($data = null, array $options = [])
 * @method BakeArticle[] newEntities(array $data, array $options = [])
 * @method BakeArticle save(EntityInterface $entity, $options = [])
 * @method BakeArticle patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method BakeArticle[] patchEntities($entities, array $data, array $options = [])
 * @method BakeArticle findOrCreate($search, callable $callback = null)
 */
class BakeArticlesTable extends Table
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

        $this->primaryKey('id');
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
            ->numeric('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->email('email')
            ->allowEmpty('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        return $validator;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'test';
    }
}
