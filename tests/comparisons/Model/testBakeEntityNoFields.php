<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TodoItem Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $body
 * @property string $effort
 * @property bool $completed
 * @property int $todo_task_count
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $updated
 *
 * @property \Bake\Test\App\Model\Entity\User $user
 * @property \Bake\Test\App\Model\Entity\TodoReminder $todo_reminder
 * @property \Bake\Test\App\Model\Entity\TodoTask[] $todo_tasks
 * @property \Bake\Test\App\Model\Entity\TodoLabel[] $todo_labels
 */
class TodoItem extends Entity
{
}
