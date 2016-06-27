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

        $this->table('articles');
        $this->displayField('title');
        $this->primaryKey('id');

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
