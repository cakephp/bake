<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BakeArticle Entity.
 *
 * @property int $id
 * @property int $bake_user_id
 * @property \App\Model\Entity\BakeUser $bake_user
 * @property string $title
 * @property string $body
 * @property bool $published
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $updated
 * @property $unknown_type
 * @property \BakeTest\Model\Entity\Author[] $authors
 */
class BakeArticle extends Entity
{

}
