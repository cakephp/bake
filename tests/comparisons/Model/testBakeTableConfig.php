<?php
namespace App\Model\Table;

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
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
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

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'website';
    }
}
