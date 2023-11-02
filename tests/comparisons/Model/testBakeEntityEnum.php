<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * @property int $id
 * @property int|null $author_id
 * @property string|null $title
 * @property string|null $body
 * @property \Bake\Test\App\Model\Enum\ArticleStatus|null $published
 *
 * @property \Bake\Test\App\Model\Entity\Author $author
 * @property \Bake\Test\App\Model\Entity\Tag[] $tags
 * @property \Bake\Test\App\Model\Entity\ArticlesTag[] $articles_tags
 */
class Article extends Entity
{
}
