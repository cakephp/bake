<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $updated
 *
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\CounterCachePost[] $counter_cache_posts
 */
class User extends Entity
{
    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'foo',
        'bar',
    ];
}
