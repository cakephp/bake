<?php
namespace Bake\Test\App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Posts Model
 *
 * @property \Bake\Test\App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property \Bake\Test\App\Model\Table\CategoriesTable|\Cake\ORM\Association\BelongsTo $Categories
 *
 * @method \Bake\Test\App\Model\Entity\Post get($primaryKey, $options = [])
 * @method \Bake\Test\App\Model\Entity\Post newEntity($data = null, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Post[] newEntities(array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Post|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\Post|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Bake\Test\App\Model\Entity\Post patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Post[] patchEntities($entities, array $data, array $options = [])
 * @method \Bake\Test\App\Model\Entity\Post findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class PostsTable extends Table
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

        $this->setTable('posts');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('CounterCache', [
            'Users' => ['post_count']
        ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id'
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->allowEmpty('title');

        $validator
            ->boolean('published')
            ->requirePresence('published', 'create')
            ->notEmpty('published');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));

        return $rules;
    }
}
