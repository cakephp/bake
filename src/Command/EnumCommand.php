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

use Bake\Utility\Model\EnumParser;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Inflector;
use InvalidArgumentException;

/**
 * Enum code generator.
 */
class EnumCommand extends SimpleBakeCommand
{
    /**
     * Task name used in path generation.
     */
    public string $pathFragment = 'Model/Enum/';

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Create a model Enum';
    }

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
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        $arguments = $this->args;
        $cases = EnumParser::parseCases($arguments->getArgument('cases'), (bool)$arguments->getOption('int'));
        $isOfTypeInt = $this->isOfTypeInt($cases);
        $backingType = $isOfTypeInt ? 'int' : 'string';
        if ($arguments->getOption('int')) {
            if ($cases && !$isOfTypeInt) {
                throw new InvalidArgumentException('Cases do not match requested `int` backing type.');
            }

            $backingType = 'int';
        }

        $data = parent::templateData();
        $data['backingType'] = $backingType;
        $data['cases'] = $this->formatCases($cases);

        return $data;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->setCommonOptions($parser);

        $parser->setDescription(
            'Bake backed enums for use in models.',
        )->addArgument('name', [
            'help' => 'Name of the enum to bake. You can use Plugin.name to bake plugin enums.',
            'required' => true,
        ])->addArgument('cases', [
            'help' => 'List of either `one,two` for string or `foo:0,bar:1` for int type.',
        ])->addOption('int', [
            'help' => 'Using backed enums with int instead of string as return type.',
            'boolean' => true,
            'short' => 'i',
        ]);

        return $parser;
    }

    /**
     * @param array<string, int|string> $definition
     * @return bool
     */
    protected function isOfTypeInt(array $definition): bool
    {
        if ($definition === []) {
            return false;
        }

        return array_all($definition, fn($value) => is_int($value));
    }

    /**
     * @param array<string, int|string> $cases
     * @return array<string>
     */
    protected function formatCases(array $cases): array
    {
        $formatted = [];
        foreach ($cases as $case => $value) {
            $case = Inflector::camelize(Inflector::underscore($case));
            if (is_string($value)) {
                $value = "'" . $value . "'";
            }
            $formatted[] = 'case ' . $case . ' = ' . $value . ';';
        }

        return $formatted;
    }

    /**
     * Generate a class stub
     *
     * @param string $name The class name
     * @return void
     */
    protected function bake(string $name): void
    {
        parent::bake($name);

        $path = $this->getPath();
        $filename = $path . $name . '.php';

        // Work around composer caching that classes/files do not exist.
        // Check for the file as it might not exist in tests.
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
}
