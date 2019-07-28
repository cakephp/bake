<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BakeArticles Model
 *
 * @method \App\Model\Entity\BakeArticle get($primaryKey, $options = [])
 * @method \App\Model\Entity\BakeArticle newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\BakeArticle[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BakeArticle saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BakeArticle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle findOrCreate($search, callable $callback = null, $options = [])
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

        $this->setPrimaryKey('id');
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
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 100, 'Name must be shorter than 100 characters.')
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', false);

        $validator
            ->nonNegativeInteger('count')
            ->requirePresence('count', 'create')
            ->allowEmptyString('count', false);

        $validator
            ->greaterThanOrEqual('price', 0)
            ->requirePresence('price', 'create')
            ->allowEmptyString('price', false);

        $validator
            ->email('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table'])
            ->allowEmptyString('email');

        $validator
            ->uploadError('image', true)
            ->uploadedFile('image', ['optional' => true, 'types' => ['image/jpeg']])
            ->allowEmptyFile('image');

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
