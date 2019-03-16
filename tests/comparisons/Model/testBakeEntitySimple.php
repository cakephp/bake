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
}
