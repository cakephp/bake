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
namespace Bake\Command;

use Bake\Utility\TableScanner;
use Cake\Console\Arguments;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use Throwable;

/**
 * Command for `bake all`
 */
class AllCommand extends BakeCommand
{
    /**
     * All commands to call.
     *
     * @var array<string>
     */
    protected array $commands = [
        ModelCommand::class,
        ControllerCommand::class,
        TemplateCommand::class,
    ];

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);

        $parser = $parser->setDescription(
            'Generate the model, controller, template, tests and fixture for a table.',
        )->addArgument('name', [
            'help' => 'Name of the table to generate code for.',
        ])->addOption('everything', [
            'help' => 'Generate code for all tables.',
            'default' => false,
            'boolean' => true,
        ])->addOption('prefix', [
            'help' => 'The namespace prefix to use.',
            'default' => false,
        ]);

        return $parser;
    }

    /**
     * Execute the command.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $this->extractCommonProperties($this->args);
        $name = $this->args->getArgument('name') ?? '';
        $name = $this->getNameWithoutPrefix($name);
        $this->io->out('Bake All');
        $this->io->hr();
        /** @var \Cake\Database\Connection $connection */
        $connection = ConnectionManager::get($this->connection);
        $scanner = new TableScanner($connection);
        $tables = $scanner->removeShadowTranslationTables($scanner->listUnskipped());
        if (!$name && !$this->args->getOption('everything')) {
            $this->io->out('Choose a table to generate from the following:');
            foreach ($tables as $table) {
                $this->io->out('- ' . $this->camelize($table));
            }

            return static::CODE_SUCCESS;
        }
        if (!$this->args->getOption('everything')) {
            $tables = [$name];
        }
        $errors = 0;
        foreach ($this->commands as $commandName) {
            /** @var \Bake\Command\BakeCommand $command */
            $command = new $commandName($this->factory);

            $options = $this->args->getOptions();
            if (
                $this->args->hasOption('prefix') &&
                !($command instanceof ControllerCommand) &&
                !($command instanceof TemplateCommand)
            ) {
                unset($options['prefix']);
            }

            foreach ($tables as $table) {
                $parser = $command->getOptionParser();
                $subArgs = new Arguments([$table], $options, $parser->argumentNames());

                $command->setIo($this->io);
                $command->setArgs($subArgs);

                try {
                    $command->execute();
                } catch (Throwable $e) {
                    if (!$this->args->getOption('everything') || !$this->args->getOption('force')) {
                        throw $e;
                    }

                    $message = sprintf('Error generating %s for %s: %s', $commandName, $table, $e->getMessage());
                    $this->io->error($message);
                    $errors++;
                }
            }
        }
        if ($errors) {
            $this->io->warning(sprintf('Bake All completed, but with %s errors.', $errors));
        } else {
            $this->io->success('Bake All complete.');
        }

        return $errors ? static::CODE_ERROR : static::CODE_SUCCESS;
    }
}
