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

use Cake\Command\Command;
use Cake\Console\Command\HelpCommand;
use Cake\Console\CommandCollection;
use Cake\Console\CommandCollectionAwareInterface;
use Cake\Console\ConsoleIoInterface;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Exception\ConsoleException;

/**
 * Command that provides help and an entry point to bake tools.
 */
class EntryCommand extends Command implements CommandCollectionAwareInterface
{
    /**
     * The command collection to get help on.
     */
    protected CommandCollection $commands;

    /**
     * The HelpCommand to get help.
     */
    protected HelpCommand $help;

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'bake';
    }

    /**
     * @inheritDoc
     */
    public function setCommandCollection(CommandCollection $commands): void
    {
        $this->commands = $commands;
    }

    /**
     * Run the command.
     *
     * Override the run() method for special handling of the `--help` option.
     *
     * @param array<int, string> $argv Arguments from the CLI environment.
     * @param \Cake\Console\ConsoleIoInterface|null $io The console io
     *   instance if the default one shouldn't be used
     * @return int|null Exit code or null for success.
     */
    public function run(array $argv, ?ConsoleIoInterface $io = null): ?int
    {
        if ($io !== null) {
            $this->io = $io;
        }

        $parser = $this->getOptionParser();
        try {
            $this->parseArguments($parser, $argv);
        } catch (ConsoleException $e) {
            $this->io->error('Error: ' . $e->getMessage());

            return static::CODE_ERROR;
        }
        $this->setOutputLevel();

        // This is the variance from Command::run()
        if (!$this->args->getArgumentAt(0) && $this->args->getOption('help')) {
            $this->executeCommand($this->help, []);

            return static::CODE_SUCCESS;
        }

        if ($this->args->getOption('quiet')) {
            $this->io->setInteractive(false);
        }

        $this->initialize();

        $this->dispatchEvent('Command.beforeExecute', ['args' => $this->args, 'io' => $this->io]);
        $result = $this->execute();
        $this->dispatchEvent('Command.afterExecute', ['args' => $this->args, 'io' => $this->io, 'result' => $result]);

        return $result;
    }

    /**
     * Execute the command.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        if ($this->args->hasArgumentAt(0)) {
            $name = $this->args->getArgumentAt(0);
            $this->io->error(
                "Could not find bake command named `{$name}`."
                . ' Run `bake --help` to get a list of commands.',
            );

            return static::CODE_ERROR;
        }
        $this->io->warning('No command provided. Run `bake --help` to get a list of commands.');

        return static::CODE_ERROR;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $this->help = new HelpCommand();
        $parser = $this->help->buildOptionParser($parser);
        $parser
            ->setDescription(
                'Bake generates code for your application. Different types of classes can be generated' .
                ' with the subcommands listed below. For example run <info>bake controller --help</info>' .
                ' to learn more about generating a controller.',
            );
        $commands = [];
        foreach ($this->commands as $command => $class) {
            if (str_starts_with($command, 'bake')) {
                $parts = explode(' ', $command);

                // Remove `bake`
                array_shift($parts);
                if ($parts === []) {
                    continue;
                }
                $commands[$command] = $class;
            }
        }

        $CommandCollection = new CommandCollection($commands);
        $this->help->setCommandCollection($CommandCollection);

        return $parser;
    }
}
