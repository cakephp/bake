<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Cake\I18n\DateTime;
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
 * @property \Bake\Test\App\Model\Entity\User $user
 * @property \Bake\Test\App\Model\Entity\TodoReminder $todo_reminder
 * @property array<\Bake\Test\App\Model\Entity\TodoTask> $todo_tasks
 * @property array<\Bake\Test\App\Model\Entity\TodoLabel> $todo_labels
 */
class TodoItem extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $patchable = [
        'user_id' => true,
        'title' => true,
        'body' => true,
        'effort' => true,
        'completed' => true,
        'todo_task_count' => true,
        'created' => true,
        'updated' => true,
        'user' => true,
        'todo_reminder' => true,
        'todo_tasks' => true,
        'todo_labels' => true,
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
}
