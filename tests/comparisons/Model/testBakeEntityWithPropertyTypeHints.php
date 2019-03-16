<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BakeArticle Entity
 *
 * @property int $id
 * @property int $bake_user_id
 * @property string $title
 * @property string|null $body
 * @property float $rating
 * @property float $score
 * @property bool $published
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $updated
 * @property array $array_type
 * @property array $json_type
 * @property $unknown_type
 *
 * @property \App\Model\Entity\BakeUser $bake_user
 * @property \BakeTest\Model\Entity\Author[] $authors
 * @property \App\Model\Entity\BakeComment[] $bake_comments
 * @property \App\Model\Entity\BakeTag[] $bake_tags
 */
class BakeArticle extends Entity
{
}
