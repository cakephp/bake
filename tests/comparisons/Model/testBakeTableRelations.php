<?php
namespace App\Model\Table;

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
 * @method \App\Model\Entity\BakeArticle get($primaryKey, $options = [])
 * @method \App\Model\Entity\BakeArticle newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\BakeArticle[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BakeArticle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\BakeArticle findOrCreate($search, callable $callback = null)
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
