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

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'title' => true,
        'body' => true,
        'created' => true
    ];
}
