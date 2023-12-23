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
 * @since         3.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use InvalidArgumentException;

/**
 * Enum code generator.
 */
class EnumCommand extends SimpleBakeCommand
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public string $pathFragment = 'Model/Enum/';

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'enum';
    }

    /**
     * @inheritDoc
     */
    public function fileName(string $name): string
    {
        return $name . '.php';
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'Bake.Model/enum';
    }

    /**
     * Get template data.
     *
     * @param \Cake\Console\Arguments $arguments The arguments for the command
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public function templateData(Arguments $arguments): array
    {
        $data = parent::templateData($arguments);

        $backed = $arguments->getOption('backed');
        if ($backed && !in_array($backed, ['string', 'int'], true)) {
            throw new InvalidArgumentException('Backed enums must be of type `string` or `int`');
        }

        $data['backed'] = $backed;

        return $data;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);

        $parser->setDescription(
            'Bake (backed) enums for use in models.'
        )->addOption('backed', [
            'help' => 'Valid options are `string` for string columns or `int` for tinyinteger columns.',
            'short' => 'b',
        ]);

        return $parser;
    }
}
