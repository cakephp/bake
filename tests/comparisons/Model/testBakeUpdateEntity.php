<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Authorization\IdentityInterface;
use Cake\I18n\DateTime;
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
 * @property \Bake\Test\App\Model\Entity\User $user
 * @property \Bake\Test\App\Model\Entity\TodoReminder $todo_reminder
 * @property array<\Bake\Test\App\Model\Entity\TodoTask> $todo_tasks
 * @property array<\Bake\Test\App\Model\Entity\TodoLabel> $todo_labels
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
     * @var array<string>
     */
    protected array $hidden = [
        'user_id',
    ];

    public protected(set) int $id;
    public protected(set) int $user_id;
    public protected(set) string $title;
    public protected(set) ?string $body;
    public protected(set) string $effort;
    public protected(set) bool $completed;
    public protected(set) int $todo_task_count;
    public protected(set) ?DateTime $created;
    public protected(set) ?DateTime $updated;
    public protected(set) ?User $user;
    public protected(set) ?TodoReminder $todo_reminder;
    public protected(set) ?array $todo_tasks;
    public protected(set) ?array $todo_labels;

    protected array $patchable = [
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
