<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $updated
 * @property array<\Bake\Test\App\Model\Entity\Comment> $comments
 * @property array<\Bake\Test\App\Model\Entity\Relation> $relations
 * @property array<\Bake\Test\App\Model\Entity\TodoItem> $todo_items
 */
class User extends Entity
{
    public protected(set) int $id;
    public protected(set) ?string $username;
    public protected(set) ?string $password;
    public protected(set) ?DateTime $created;
    public protected(set) ?DateTime $updated;
    public protected(set) ?array $comments;
    public protected(set) ?array $relations;
    public protected(set) ?array $todo_items;
}
