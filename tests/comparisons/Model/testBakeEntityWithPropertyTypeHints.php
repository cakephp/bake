<?php
namespace Bake\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TodoItem Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $body
 * @property float $effort
 * @property bool $completed
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $updated
 * @property array $array_type
 * @property array $json_type
 * @property $unknown_type
 *
 * @property \Bake\Test\App\Model\Entity\User $user
 * @property \BakeTest\Model\Entity\TodoTask[] $todo_tasks
 * @property \Bake\Test\App\Model\Entity\TodoLabel[] $todo_labels
 */
class TodoItem extends Entity
{

}
