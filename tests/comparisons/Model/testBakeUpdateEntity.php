<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Authorization\IdentityInterface;
use Cake\ORM\Entity;
use MyApp\Test;

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
class TodoItem extends Entity implements IdentityInterface
{
    /**
     * @var int
     */
    protected const MY_CONST = 1;

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'user_id',
    ];

    protected array $_accessible = [
        // should not get overwritten
    ];

    /**
     * @var string
     */
    protected string $myProperty = 'string';

    protected function _getName(): string
    {
        return 'name';
    }
}
