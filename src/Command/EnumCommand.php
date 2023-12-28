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
use Cake\Utility\Inflector;
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
        $cases = $this->parseCases($arguments->getArgument('cases'), (bool)$arguments->getOption('int'));
        $isOfTypeInt = $this->isOfTypeInt($cases);
        $backingType = $isOfTypeInt ? 'int' : 'string';
        if ($arguments->getOption('int')) {
            if ($cases && !$isOfTypeInt) {
                throw new InvalidArgumentException('The cases provided do not seem to match the int type you want to bake');
            }

            $backingType = 'int';
        }

        $data = parent::templateData($arguments);
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
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);

        $parser->setDescription(
            'Bake backed enums for use in models.'
        )->addArgument('name', [
            'help' => 'Name of the enum to bake. You can use Plugin.name to bake plugin enums.',
            'required' => true,
        ])->addArgument('cases', [
            'help' => 'List of either `one,two` for string or `0:foo,1:bar` for int type.',
        ])->addOption('int', [
            'help' => 'Using backed enums with int instead of string as return type.',
            'boolean' => true,
            'short' => 'i',
        ]);

        return $parser;
    }

    /**
     * @param string|null $casesString
     * @return array<int|string, string>
     */
    protected function parseCases(?string $casesString, bool $int): array
    {
        if ($casesString === null) {
            return [];
        }

        $enumCases = explode(',', $casesString);

        $definition = [];
        foreach ($enumCases as $k => $enumCase) {
            $key = $value = trim($enumCase);
            if (str_contains($key, ':')) {
                $value = trim(mb_substr($key, strpos($key, ':') + 1));
                $key = mb_substr($key, 0, strpos($key, ':'));
            } elseif ($int) {
                $key = $k;
            }

            $definition[$key] = $value;
        }

        return $definition;
    }

    /**
     * @param array<int|string, string> $definition
     * @return bool
     */
    protected function isOfTypeInt(array $definition): bool
    {
        if (!$definition) {
            return false;
        }

        foreach ($definition as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int|string, string> $cases
     * @return array<string>
     */
    protected function formatCases(array $cases): array
    {
        $formatted = [];
        foreach ($cases as $case => $alias) {
            $alias = mb_strtoupper(Inflector::underscore($alias));
            if (is_string($case)) {
                $case = '\'' . $case . '\'';
            }
            $formatted[] = 'case ' . $alias . ' = ' . $case . ';';
        }

        return $formatted;
    }
}
