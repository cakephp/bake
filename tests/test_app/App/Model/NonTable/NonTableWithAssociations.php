<?php
declare(strict_types=1);

namespace Bake\Test\App\Model\NonTable;

use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\AssociationCollection;

/**
 * A non-table based model with associations
 */
class NonTableWithAssociations extends AbstractNonTable
{
    protected $associations;

    /**
     * Get the associations collection for this table.
     *
     * @return \Cake\ORM\AssociationCollection The collection of association objects.
     */
    public function associations(): AssociationCollection
    {
        $this->associations = new AssociationCollection();

        $this->associations->load(BelongsTo::class, 'Users');

        return $this->associations;
    }

    /**
     * Returns an association object configured for the specified alias.
     *
     * @param string $name The alias used for the association.
     * @return \Cake\ORM\Association The association.
     */
    public function getAssociation(string $name): Association
    {
        return $this->associations->get($name);
    }
}
