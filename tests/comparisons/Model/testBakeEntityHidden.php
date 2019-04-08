<?php
namespace Bake\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $updated
 *
 * @property \Bake\Test\App\Model\Entity\Comment[] $comments
 * @property \Bake\Test\App\Model\Entity\TodoItem[] $todo_items
 */
class User extends Entity
{

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];
}
