<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         2.0.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
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
    protected Connection $connection;

    /**
     * @var array<string>
     */
    protected array $ignore;

    /**
     * Constructor
     *
     * @param \Cake\Database\Connection $connection The connection name in ConnectionManager
     * @param array<string>|null $ignore List of tables or regex pattern to ignore. If null, the default ignore
     *   list will be used.
     */
    public function __construct(Connection $connection, ?array $ignore = null)
    {
        $this->connection = $connection;
        if ($ignore === null) {
            $ignore = ['i18n', 'cake_sessions', 'sessions', '/phinxlog/'];
        }
        $this->ignore = $ignore;
    }

    /**
     * Get all tables in the connection without applying ignores.
     *
     * @return array<string, string>
     */
    public function listAll(): array
    {
        $schema = $this->connection->getSchemaCollection();
        $tables = $schema->listTables();
        if (!$tables) {
            throw new RuntimeException('Your database does not have any tables.');
        }
        sort($tables);

        return array_combine($tables, $tables);
    }

    /**
     * Get all tables in the connection that aren't ignored.
     *
     * @return array<string, string>
     */
    public function listUnskipped(): array
    {
        $tables = $this->listAll();

        foreach ($tables as $key => $table) {
            if ($this->shouldSkip($table)) {
                unset($tables[$key]);
            }
        }

        return $tables;
    }

    /**
     * Call from any All command that needs the shadow translation tables to be skipped.
     *
     * @param array<string, string> $tables
     * @return array<string, string>
     */
    public function removeShadowTranslationTables(array $tables): array
    {
        foreach ($tables as $key => $table) {
            if (!preg_match('/^(.+)_translations$/', $table, $matches)) {
                continue;
            }

            if (empty($tables[$matches[1]])) {
                continue;
            }

            unset($tables[$key]);
        }

        return $tables;
    }

    /**
     * @param string $table Table name.
     * @return bool
     */
    protected function shouldSkip(string $table): bool
    {
        foreach ($this->ignore as $ignore) {
            if (str_starts_with($ignore, '/')) {
                if ((bool)preg_match($ignore, $table)) {
                    return true;
                }
            }

            if ($ignore === $table) {
                return true;
            }
        }

        return false;
    }
}
