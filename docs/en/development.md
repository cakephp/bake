# Extending Bake

Bake features an extensible architecture that allows your application or plugins
to modify or add to the base functionality.
Bake makes use of a dedicated view class that uses the [Twig](https://twig.symfony.com/) template engine.

## Bake Events

As a view class, `BakeView` emits the same events as any other view class, plus one extra initialize event.
However, whereas standard view classes use the event prefix `View.`, `BakeView` uses the event prefix `Bake.`.

The initialize event can be used to make changes that apply to all baked output.
For example, to add another helper to the bake view class:

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;

// in src/Application::bootstrapCli()

EventManager::instance()->on('Bake.initialize', function (EventInterface $event) {
    $view = $event->getSubject();

    // In my bake templates, allow the use of the MySpecial helper
    $view->loadHelper('MySpecial', ['some' => 'config']);

    // And add an $author variable so it's always available
    $view->set('author', 'Andy');
});
```

Bake events can also be handy for making small changes to existing templates.
For example, to change the variable names used when baking controller and template files, listen for `Bake.beforeRender`:

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;

// in src/Application::bootstrapCli()

EventManager::instance()->on('Bake.beforeRender', function (EventInterface $event) {
    $view = $event->getSubject();

    // Use $rows for the main data variable in indexes
    if ($view->get('pluralName')) {
        $view->set('pluralName', 'rows');
    }
    if ($view->get('pluralVar')) {
        $view->set('pluralVar', 'rows');
    }

    // Use $theOne for the main data variable in view/edit
    if ($view->get('singularName')) {
        $view->set('singularName', 'theOne');
    }
    if ($view->get('singularVar')) {
        $view->set('singularVar', 'theOne');
    }
});
```

You may also scope `Bake.beforeRender` and `Bake.afterRender` events to a specific generated file.
For instance, if you want to add specific actions to your `UsersController` when generating from a `Controller/controller.twig` file:

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Utility\Hash;

// in src/Application::bootstrapCli()

EventManager::instance()->on(
    'Bake.beforeRender.Controller.controller',
    function (EventInterface $event) {
        $view = $event->getSubject();
        if ($view->get('name') === 'Users') {
            // add the login and logout actions to the Users controller
            $view->set('actions', [
                'login',
                'logout',
                'index',
                'view',
                'add',
                'edit',
                'delete',
            ]);
        }
    }
);
```

By scoping event listeners to specific bake templates, you can simplify your bake-related event logic and provide callbacks that are easier to test.

## Bake Template Syntax

Bake template files use the [Twig](https://twig.symfony.com/) template syntax.

For example, when baking a command like this:

```bash
bin/cake bake command Foo
```

The template used at `vendor/cakephp/bake/templates/bake/Command/command.twig` looks like this:

```php
<?php
declare(strict_types=1);

namespace {{ namespace }}\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
* {{ name }} command.
*/
class {{ name }}Command extends Command
{
    /**
    * Hook method for defining this command's option parser.
    *
    * @link https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
    * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
    * @return \Cake\Console\ConsoleOptionParser The built parser.
    */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
    * Implement this method with your command's logic.
    *
    * @param \Cake\Console\Arguments $args The command arguments.
    * @param \Cake\Console\ConsoleIo $io The console io
    * @return int|null|void The exit code or null for success
    */
    public function execute(Arguments $args, ConsoleIo $io)
    {
    }
}
```

The resultant baked class at `src/Command/FooCommand.php` looks like this:

```php
<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
* Foo command.
*/
class FooCommand extends Command
{
    /**
    * Hook method for defining this command's option parser.
    *
    * @link https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
    * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
    * @return \Cake\Console\ConsoleOptionParser The built parser.
    */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
    * Implement this method with your command's logic.
    *
    * @param \Cake\Console\Arguments $args The command arguments.
    * @param \Cake\Console\ConsoleIo $io The console io
    * @return int|null|void The exit code or null for success
    */
    public function execute(Arguments $args, ConsoleIo $io)
    {
    }
}
```

## Creating a Bake Theme

If you wish to modify the output produced by the `bake` command, you can create your own bake theme, which allows you to replace some or all of the templates that Bake uses.

1. Bake a new plugin. The plugin name is the bake theme name. For example, `bin/cake bake plugin custom_bake`.
2. Create a new directory at `plugins/CustomBake/templates/bake`.
3. Copy any templates you want to override from `vendor/cakephp/bake/templates/bake` into matching files in your plugin.
4. When running Bake, use the `--theme CustomBake` option to use your bake theme. To avoid specifying this option every time, you can also set your custom theme as the default:

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## Application Bake Templates

If you only need to customize a few bake templates, or need to use application dependencies in your templates, you can include template overrides in your application templates.
These overrides work similarly to overriding other plugin templates.

1. Create a new directory at `/templates/plugin/Bake/`.
2. Copy any templates you want to override from `vendor/cakephp/bake/templates/bake/` into matching files in your application.

You do not need to use the `--theme` option when using application templates.

## Creating New Bake Command Options

It is possible to add new bake command options, or override the ones provided by CakePHP, by creating commands in your application or plugins.
By extending `Bake\Command\BakeCommand`, Bake will find your new command and include it as part of bake.

As an example, create the command file `src/Command/Bake/FooCommand.php`.
We will extend `SimpleBakeCommand` because the command is simple:

```php
<?php
declare(strict_types=1);

namespace App\Command\Bake;

use Bake\Command\SimpleBakeCommand;

class FooCommand extends SimpleBakeCommand
{
    public $pathFragment = 'FooPath/';

    public function name(): string
    {
        return 'foo';
    }

    public function template(): string
    {
        return 'fooTemplate';
    }

    public function fileName(string $name): string
    {
        return $name . 'FooOut.php';
    }
}
```

Next create `templates/bake/foo_template.twig`:

```php
<?php
namespace {{ namespace }}\FooPath;

/**
 * {{ name }} fooOut
 */
class {{ name }}FooOut
{
    // Add code.
}
```

You should now see your new command in the output of `bin/cake bake`.
Run it with `bin/cake bake foo Example`.
This generates `src/FooPath/ExampleFooOut.php`.

If you also want `bake` to create a test file for your `ExampleFooOut` class, override the `bakeTest()` method in `FooCommand`:

```php
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

public function bakeTest(string $className, Arguments $args, ConsoleIo $io): void
{
    if (!isset($this->Test->classSuffixes[$this->name()])) {
        $this->Test->classSuffixes[$this->name()] = 'Foo';
    }

    $name = ucfirst($this->name());
    if (!isset($this->Test->classTypes[$name])) {
        $this->Test->classTypes[$name] = 'Foo';
    }

    parent::bakeTest($className, $args, $io);
}
```

- The **class suffix** is appended to the name provided in your `bake` call. In the example above, that would create `ExampleFooTest.php`.
- The **class type** is the sub-namespace used to reach your file relative to the app or plugin you are baking into. In the example above, that would create the test namespace `App\Test\TestCase\Foo`.

## Configuring the BakeView Class

Bake commands use the `BakeView` class to render templates.
You can access the instance by listening to the `Bake.initialize` event:

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
