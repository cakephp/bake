<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserPersistent Entity
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
class UserPersistent extends Entity
{
}
