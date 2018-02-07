<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $updated
 *
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\CounterCachePost[] $counter_cache_posts
 */
class User extends Entity
{

}
