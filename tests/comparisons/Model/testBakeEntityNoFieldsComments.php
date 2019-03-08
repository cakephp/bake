<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BakeArticle Entity
 *
 * @property int $id ID
 * @property int $bake_user_id
 * @property string $title Title
 * @property string|null $body Contents
 * @property float $rating Rating
 * @property float $score Score
 * @property bool $published Is Published
 * @property \Cake\I18n\Time|null $created Creation date
 * @property \Cake\I18n\Time|null $updated Modification date
 *
 * @property \App\Model\Entity\BakeUser $bake_user
 * @property \App\Model\Entity\BakeComment[] $bake_comments
 * @property \App\Model\Entity\BakeTag[] $bake_tags
 */
class BakeArticle extends Entity
{

}
