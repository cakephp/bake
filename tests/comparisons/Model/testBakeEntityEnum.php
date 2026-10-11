<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Bake\Test\App\Model\Enum\ArticleStatus;
use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * @property int $id
 * @property int|null $author_id
 * @property string|null $title
 * @property string|null $body
 * @property \Bake\Test\App\Model\Enum\ArticleStatus|null $published
 * @property \Bake\Test\App\Model\Entity\Author $author
 * @property array<\Bake\Test\App\Model\Entity\Tag> $tags
 * @property array<\Bake\Test\App\Model\Entity\ArticlesTag> $articles_tags
 */
class Article extends Entity
{
    public protected(set) int $id;
    public protected(set) ?int $author_id;
    public protected(set) ?string $title;
    public protected(set) ?string $body;
    public protected(set) ?ArticleStatus $published;
    public protected(set) ?Author $author;
    public protected(set) ?array $tags;
    public protected(set) ?array $articles_tags;
}
