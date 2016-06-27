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
 * @property \Cake\ORM\Association\BelongsTo $SomethingElse
 * @property \Cake\ORM\Association\BelongsTo $BakeUser
 * @property \Cake\ORM\Association\HasMany $BakeComment
 * @property \Cake\ORM\Association\BelongsToMany $BakeTag
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

        $this->belongsTo('SomethingElse', [
            'foreignKey' => 'something_else_id'
        ]);
        $this->belongsTo('BakeUser', [
            'foreignKey' => 'bake_user_id'
        ]);
        $this->hasMany('BakeComment', [
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsToMany('BakeTag', [
            'foreignKey' => 'bake_article_id',
            'joinTable' => 'bake_articles_bake_tags',
            'targetForeignKey' => 'bake_tag_id'
        ]);
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
