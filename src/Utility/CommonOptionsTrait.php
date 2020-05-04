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
 * @since         1.4.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Bake\Utility;

use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use InvalidArgumentException;

/**
 * Long term this trait should be folded into Bake\Command\BakeCommand
 *
 * For now it is a helpful bridge between tasks and commands.
 */
trait CommonOptionsTrait
{
    /**
     * @var string|null
     */
    public $plugin;

    /**
     * @var string|null
     */
    public $theme;

    /**
     * @var string
     */
    public $connection;

    /**
     * Pull common/frequently used arguments & options into properties
     * so that method signatures can be simpler.
     *
     * @param \Cake\Console\Arguments $args Arguments to extract
     * @return void
     */
    protected function extractCommonProperties(Arguments $args): void
    {
        // These properties should ideally not exist, but until ConsoleOptionParser
        // gets validation and transform logic they will have to stay.
        if ($args->hasOption('plugin')) {
            $plugin = $args->getOption('plugin');
            $parts = explode('/', $plugin);
            $this->plugin = implode('/', array_map([$this, '_camelize'], $parts));

            if (strpos($this->plugin, '\\')) {
                throw new InvalidArgumentException(
                    'Invalid plugin namespace separator, please use / instead of \ for plugins.'
                );
            }
        }

        $this->theme = $args->getOption('theme');
        $this->connection = $args->getOption('connection');
    }

    /**
     * Set common options used by all bake tasks.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Options parser.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function _setCommonOptions(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $bakeThemes = [];
        $templates = 'templates' . DS . 'bake';
        foreach (Plugin::loaded() as $plugin) {
            $path = Plugin::path($plugin);
            if (is_dir($path . $templates)) {
                $bakeThemes[] = $plugin;
            }
        }

        $parser->addOption('plugin', [
            'short' => 'p',
            'help' => 'Plugin to bake into.',
        ])->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force overwriting existing files without prompting.',
        ])->addOption('connection', [
            'short' => 'c',
            'default' => 'default',
            'help' => 'The datasource connection to get data from.',
        ])->addOption('theme', [
            'short' => 't',
            'help' => 'The theme to use when baking code.',
            'default' => Configure::read('Bake.theme') ?? '',
            'choices' => $bakeThemes,
        ]);

        return $parser;
    }
}
