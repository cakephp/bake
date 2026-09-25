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
 * @since         0.1.0
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Command;

use Bake\Utility\Process;
use Bake\Utility\TemplateRenderer;
use Bake\View\BakeView;
use Cake\Command\PluginLoadCommand;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Utility\Filesystem;
use Cake\Utility\Inflector;
use RuntimeException;
use function Cake\Core\env;

/**
 * The Plugin Command handles creating an empty plugin, ready to be used
 */
class PluginCommand extends BakeCommand
{
    /**
     * Plugin path.
     */
    public string $path;

    protected bool $isVendor = false;

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return 'Create a plugin.';
    }

    /**
     * Execute the command.
     *
     * @return int|null The exit code or null for success
     */
    public function execute(): ?int
    {
        $name = $this->args->getArgument('name');
        if (empty($name)) {
            $this->io->error('You must provide a plugin name in CamelCase format.');
            $this->io->out('To make an "MyExample" plugin, run <info>`cake bake plugin MyExample`</info>.');

            return static::CODE_ERROR;
        }
        $parts = explode('/', $name);
        $plugin = implode('/', array_map(Inflector::camelize(...), $parts));
        if ($this->args->getOption('standalone-path')) {
            $this->path = (string)$this->args->getOption('standalone-path');
            $this->path = rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $this->isVendor = true;

            if (!is_dir($this->path)) {
                $this->io->error(sprintf('Path `%s` does not exist.', $this->path));

                return static::CODE_ERROR;
            }
        }
        $pluginPath = $this->pluginPath($plugin);
        if (is_dir($pluginPath) && !$this->args->getOption('class-only')) {
            $this->io->out(sprintf('Plugin: %s already exists, no action taken', $plugin));
            $this->io->out(sprintf('Path: %s', $pluginPath));

            return static::CODE_ERROR;
        }
        if (!$this->bake($plugin)) {
            $this->io->error(sprintf('An error occurred trying to bake: %s in %s', $plugin, $this->path . $plugin));
            $this->abort();
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Bake the plugin's contents
     *
     * Also update the autoloader and the root composer.json file if it can be found
     *
     * @param string $plugin Name of the plugin in CamelCased format
     * @return bool|null
     */
    public function bake(string $plugin): ?bool
    {
        if (!$this->isVendor) {
            $pathOptions = App::path('plugins');
            $currentPath = current($pathOptions);
            $this->path = $currentPath !== false ? $currentPath : '';

            if (count($pathOptions) > 1) {
                $this->findPath($pathOptions);
            }
        }

        $this->io->out(sprintf('<info>Plugin Name:</info> %s', $plugin));
        $this->io->out(sprintf('<info>Plugin Directory:</info> %s', $this->path . $plugin));
        $this->io->hr();

        $looksGood = $this->io->askChoice('Look okay?', ['y', 'n', 'q'], 'y');

        if (strtolower($looksGood) !== 'y') {
            return null;
        }

        $this->generateFiles($plugin, $this->path);

        if (!$this->isVendor) {
            if (!$this->args->getOption('class-only')) {
                $this->modifyApplication($plugin);
            }

            $composer = $this->findComposer();
            if ($composer === false) {
                $this->io->error('Could not find composer executable.');
                $this->abort();
            }

            /** @var non-empty-string $cwd */
            $cwd = getcwd();
            try {
                // Windows makes running multiple commands at once hard.
                chdir(dirname($this->rootComposerFilePath()));
                $command = 'php ' . escapeshellarg($composer) . ' dump-autoload';
                $process = new Process($this->io);
                $this->io->out($process->call($command));
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
                $this->io->error(sprintf('Could not run `composer dump-autoload`: %s', $error));
                $this->abort();
            } finally {
                chdir($cwd);
            }
        }

        $this->io->hr();
        $this->io->out(sprintf('<success>Created:</success> %s in %s', $plugin, $this->path . $plugin), 2);

        $emptyFile = $this->path . '.gitkeep';
        $this->deleteEmptyFile($emptyFile);

        return true;
    }

    /**
     * Modify the application class
     *
     * @param string $plugin Name of plugin the plugin.
     * @return void
     */
    protected function modifyApplication(string $plugin): void
    {
        $this->executeCommand(PluginLoadCommand::class, [$plugin]);
    }

    /**
     * Generate all files for a plugin
     *
     * Find the first path which contains `src/Template/Bake/Plugin` that contains
     * something, and use that as the template to recursively render a plugin's
     * contents. Allows the creation of a bake them containing a `Plugin` folder
     * to provide customized bake output for plugins.
     *
     * @param string $pluginName the CamelCase name of the plugin
     * @param string $path the path to the plugins dir (the containing folder)
     * @return void
     */
    protected function generateFiles(
        string $pluginName,
        string $path,
    ): void {
        $namespace = str_replace('/', '\\', $pluginName);
        $baseNamespace = Configure::read('App.namespace');

        $name = $pluginName;
        $vendor = 'your-name-here';
        if (str_contains($pluginName, '/')) {
            [$vendor, $name] = explode('/', $pluginName);
        }
        $package = Inflector::dasherize($vendor) . '/' . Inflector::dasherize($name);

        $composerConfig = json_decode(
            (string)file_get_contents(ROOT . DS . 'composer.json'),
            true,
        );

        $renderer = $this->createTemplateRenderer()
            ->set([
                'name' => $name,
                'package' => $package,
                'namespace' => $namespace,
                'baseNamespace' => $baseNamespace,
                'plugin' => $pluginName,
                'routePath' => Inflector::dasherize($pluginName),
                'path' => $path,
                'root' => ROOT,
                'cakeVersion' => $composerConfig['require']['cakephp/cakephp'],
            ]);

        $root = $path . $pluginName . DS;

        $paths = [];
        if ($this->args->hasOption('theme')) {
            $paths[] = Plugin::templatePath((string)$this->args->getOption('theme'));
        }

        $paths = array_merge($paths, Configure::read('App.paths.templates'));
        $paths[] = Plugin::templatePath('Bake');

        $fs = new Filesystem();
        $templates = [];
        do {
            $templatesPath = array_shift($paths) . BakeView::BAKE_TEMPLATE_FOLDER . '/Plugin';
            if (is_dir($templatesPath)) {
                $files = iterator_to_array(
                    $fs->findRecursive($templatesPath, '/\.twig$/'),
                );

                if (!$this->isVendor) {
                    $vendorFiles = [
                        '.gitignore.twig', 'README.md.twig', 'composer.json.twig', 'phpunit.xml.dist.twig',
                        'bootstrap.php.twig', 'schema.sql.twig',
                    ];

                    foreach ($files as $key => $file) {
                        if (in_array($file->getFilename(), $vendorFiles, true)) {
                            unset($files[$key]);
                        }
                    }
                }

                if ($this->args->getOption('class-only')) {
                    $files = array_filter($files, function ($file): bool {
                        return $file->getFilename() === 'Plugin.php.twig';
                    });
                }

                $templates = array_keys($files);
            }
        } while (!$templates);

        sort($templates);
        foreach ($templates as $template) {
            $template = substr((string)$template, strrpos((string)$template, 'Plugin' . DIRECTORY_SEPARATOR) + 7, -4);
            $template = rtrim($template, '.');
            $filename = $template;
            if ($filename === 'src' . DIRECTORY_SEPARATOR . 'Plugin.php') {
                $filename = 'src' . DIRECTORY_SEPARATOR . $name . 'Plugin.php';
            }
            $this->generateFile($renderer, $template, $root, $filename);
        }
    }

    /**
     * Generate a file
     *
     * @param \Bake\Utility\TemplateRenderer $renderer The renderer to use.
     * @param string $template The template to render
     * @param string $root The path to the plugin's root
     * @param string $filename Filename to generate.
     * @return void
     */
    protected function generateFile(
        TemplateRenderer $renderer,
        string $template,
        string $root,
        string $filename,
    ): void {
        $this->io->out(sprintf('Generating %s file...', $template));
        $out = $renderer->generate('Bake.Plugin/' . $template);
        $this->io->createFile($root . $filename, $out);
    }

    /**
     * The path to the main application's composer file
     *
     * This is a test isolation wrapper
     *
     * @return string the abs file path
     */
    protected function rootComposerFilePath(): string
    {
        return ROOT . DS . 'composer.json';
    }

    /**
     * find and change $this->path to the user selection
     *
     * @param array<string> $pathOptions The list of paths to look in.
     * @return void
     */
    public function findPath(array $pathOptions): void
    {
        $valid = false;
        foreach ($pathOptions as $i => $path) {
            if (!is_dir($path)) {
                unset($pathOptions[$i]);
            }
        }
        $pathOptions = array_values($pathOptions);
        $max = count($pathOptions);

        if ($max === 0) {
            $this->io->error('No valid plugin paths found! Please configure a plugin path that exists.');
            $this->abort();
        }

        if ($max === 1) {
            $this->path = $pathOptions[0];

            return;
        }

        $choice = 0;
        while (!$valid) {
            foreach ($pathOptions as $i => $option) {
                $this->io->out($i + 1 . '. ' . $option);
            }
            $prompt = 'Choose a plugin path from the paths above.';
            $choice = (int)$this->io->ask($prompt);
            if ($choice > 0 && $choice <= $max) {
                $valid = true;
            }
        }
        $this->path = $pathOptions[$choice - 1];
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(
            'Create the directory structure, AppController class and testing setup for a new plugin. ' .
            'Can create plugins in any of your bootstrapped plugin paths.',
        )->addArgument('name', [
            'help' => 'CamelCased name of the plugin to create.'
            . ' For standalone plugins you can use vendor prefixed names like MyVendor/MyPlugin.',
        ])->addOption('composer', [
            'default' => ROOT . DS . 'composer.phar',
            'help' => 'The path to the composer executable.',
        ])->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force overwriting existing files without prompting.',
        ])->addOption('theme', [
            'short' => 't',
            'help' => 'The theme to use when baking code.',
            'default' => Configure::read('Bake.theme') ?: null,
            'choices' => $this->getBakeThemes(),
        ])
        ->addOption('standalone-path', [
            'short' => 'p',
            'help' => 'Generate a standalone plugin in the provided path.',
        ])->addOption('class-only', [
            'short' => 'c',
            'boolean' => true,
            'help' => 'Generate only the plugin class.',
        ]);

        return $parser;
    }

    /**
     * Uses either the CLI option or looks in $PATH and cwd for composer.
     *
     * @return string|false Either the path to composer or false if it cannot be found.
     */
    public function findComposer(): string|bool
    {
        if ($this->args->hasOption('composer')) {
            /** @var string $path */
            $path = $this->args->getOption('composer');
            if (file_exists($path)) {
                return $path;
            }
        }
        $composer = false;
        $path = (string)env('PATH');
        if (!empty($path)) {
            $paths = explode(PATH_SEPARATOR, $path);
            $composer = $this->searchPath($paths);
        }

        return $composer;
    }

    /**
     * Search the $PATH for composer.
     *
     * @param array<string> $path The paths to search.
     * @return string|false
     */
    protected function searchPath(array $path): string|bool
    {
        $composer = ['composer.phar', 'composer'];
        foreach ($path as $dir) {
            foreach ($composer as $cmd) {
                if (is_file($dir . DS . $cmd)) {
                    $this->io->verbose('Found composer executable in ' . $dir);

                    return $dir . DS . $cmd;
                }
            }
        }

        return false;
    }
}
