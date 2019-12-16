<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Utility;

use Cake\Database\Connection;
use RuntimeException;

/**
 * Fetch table listings from ConnectionManager
 *
 * Allows common infrastructure tables to be ignored based
 * parameters.
 *
 * @internal
 */
class TableScanner
{
    /**
     * @var \Cake\Database\Connection
     */
    protected $connection;

    /**
     * @var string[]
     */
    protected $ignore;

    /**
     * Constructor
     *
     * @param \Cake\Database\Connection $connection The connection name in ConnectionManager
     * @param string[]|null $ignore List of tables to ignore. If null, the default ignore
     *   list will be used.
     */
    public function __construct(Connection $connection, ?array $ignore = null)
    {
        $this->connection = $connection;
        if ($ignore === null) {
            $ignore = ['i18n', 'cake_sessions', 'phinxlog', 'users_phinxlog'];
        }
        $this->ignore = $ignore;
    }

    /**
     * Get all tables in the connection without applying ignores.
     *
     * @return string[]
     */
    public function listAll(): array
    {
        $schema = $this->connection->getSchemaCollection();
        $tables = $schema->listTables();
        if (empty($tables)) {
            throw new RuntimeException('Your database does not have any tables.');
        }
        sort($tables);

        return $tables;
    }

    /**
     * Get all tables in the connection that aren't ignored.
     *
     * @return string[]
     */
    public function listUnskipped(): array
    {
        $tables = $this->listAll();

        return array_diff($tables, $this->ignore);
    }
}
