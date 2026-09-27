# Extender Bake

Bake cuenta con una arquitectura extensible que permite a su aplicación o a sus plugins modificar o ampliar la funcionalidad base.
Bake hace uso de una clase de vista dedicada que emplea el motor de templates [Twig](https://twig.symfony.com/).

## Eventos de Bake

Como clase de vista, `BakeView` emite los mismos eventos que cualquier otra clase de vista, más un evento initialize adicional.
Sin embargo, mientras que las clases de vista estándar usan el prefijo de evento `View.`, `BakeView` usa el prefijo de evento `Bake.`.

El evento initialize puede usarse para realizar cambios que se aplican a toda la salida generada por Bake.
Por ejemplo, para añadir otro helper a la clase de vista bake:

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

Los eventos de Bake también pueden ser útiles para realizar cambios pequeños en los templates existentes.
Por ejemplo, para cambiar los nombres de variables usados al generar los archivos de controller y template, escuche el evento `Bake.beforeRender`:

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

También puede limitar el alcance de los eventos `Bake.beforeRender` y `Bake.afterRender` a un archivo generado concreto.
Por ejemplo, si quiere añadir acciones concretas a su `UsersController` al generar desde un archivo `Controller/controller.twig`:

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;

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

Al limitar los oyentes de eventos a templates bake concretos, puede simplificar la lógica de eventos relacionada con bake y ofrecer callbacks más fáciles de probar.

## Sintaxis de los templates de Bake

Los archivos de template de Bake usan la sintaxe de templates [Twig](https://twig.symfony.com/).

Por ejemplo, al generar un comando como este:

```bash
bin/cake bake command Foo
```

El template usado en `vendor/cakephp/bake/templates/bake/Command/command.twig` es este:

```php
{{ element('Bake.file_header', {
    namespace: "#{namespace}\\Command",
    classImports: [
        'Cake\\Command\\Command',
        'Cake\\Console\\ConsoleOptionParser',
    ],
}) }}

/**
 * {{ name }} command.
 */
class {{ name }}Command extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'cake {{ command_name }}';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return '{{ command_name }}';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Command description here.';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @link https://book.cakephp.org/6/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription());
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return int|null|void The exit code or null for success
     */
    public function execute()
    {
    }
}
```

La llamada `element()` emite la cabecera `<?php declare(strict_types=1);`, el namespace y las sentencias `use`.
Tenga en cuenta que los comandos usan `$this->args` y `$this->io` en lugar de recibirlos como parámetros de `execute()`.

La clase resultante generada en `src/Command/FooCommand.php` es esta:

```php
<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\ConsoleOptionParser;

/**
 * Foo command.
 */
class FooCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'cake foo';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'foo';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Command description here.';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @link https://book.cakephp.org/6/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription());
    }

    /**
     * Implement this method with your command's logic.
     *
     * @return int|null|void The exit code or null for success
     */
    public function execute()
    {
    }
}
```

## Crear un tema de Bake

Si desea modificar la salida producida por el comando `bake`, puede crear su propio tema bake, lo que le permite reemplazar algunos o todos los templates que usa Bake.

1. Genere un nuevo plugin. El nombre del plugin es el nombre del tema bake. Por ejemplo, `bin/cake bake plugin custom_bake`.
2. Cree un nuevo directorio en `plugins/CustomBake/templates/bake`.
3. Copie los templates que quiera sobrescribir desde `vendor/cakephp/bake/templates/bake` a los archivos coincidentes de su plugin.
4. Al ejecutar Bake, use la opción `--theme CustomBake` para usar su tema bake. Para evitar tener que especificar esta opción cada vez, también puede definir su tema personalizado como predeterminado:

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## Templates de Bake de la aplicación

Si solo necesita personalizar unos pocos templates bake, o si necesita usar dependencias de la aplicación en sus templates, puede incluir sobrescrituras de templates en los templates de su aplicación.
Estas sobrescrituras funcionan de forma similar a sobrescribir otros templates de plugins.

1. Cree un nuevo directorio en `/templates/plugin/Bake/`.
2. Copie los templates que quiera sobrescribir desde `vendor/cakephp/bake/templates/bake/` a los archivos coincidentes de su aplicación.

No necesita usar la opción `--theme` cuando use templates de la aplicación.

## Crear nuevas opciones de comandos bake

Es posible añadir nuevas opciones de comandos bake, o sobrescribir las que proporciona CakePHP, creando comandos en su aplicación o en sus plugins.
Al extender `Bake\Command\BakeCommand`, Bake encontrará su nuevo comando y lo incluirá como parte de bake.

Como ejemplo, cree el archivo de comando `src/Command/Bake/FooCommand.php`.
Extenderemos `SimpleBakeCommand` porque el comando es simple:

```php
<?php
declare(strict_types=1);

namespace App\Command\Bake;

use Bake\Command\SimpleBakeCommand;

class FooCommand extends SimpleBakeCommand
{
    public string $pathFragment = 'FooPath/';

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

A continuación, cree `templates/bake/foo_template.twig`:

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

Ahora debería ver su nuevo comando en la salida de `bin/cake bake`.
Ejecútelo con `bin/cake bake foo Example`.
Esto genera `src/FooPath/ExampleFooOut.php`.

Si también quiere que `bake` cree un archivo de prueba para su clase `ExampleFooOut`, sobrescriba el método `bakeTest()` en `FooCommand`:

```php
use Bake\Command\TestCommand;

public function bakeTest(string $className): void
{
    if ($this->args->getOption('no-test')) {
        return;
    }

    $test = new TestCommand();
    $test->classSuffixes['Foo'] = 'FooOut';
    $test->classTypes['Foo'] = 'FooPath';
    $test->plugin = $this->plugin;
    $test->setArgs($this->args);
    $test->setIo($this->io);
    $test->bake('Foo', $className);
}
```

- El **sufijo de clase** se añade al nombre proporcionado en su llamada a `bake`. En el ejemplo anterior, esto crearía `ExampleFooOut` y su archivo de prueba `tests/TestCase/FooPath/ExampleFooOutTest.php`.
- El valor de **class type** es el sub-namespace usado para acceder a su archivo respecto de la aplicación o plugin en el que genera. En el ejemplo anterior, esto crearía el namespace de pruebas `App\Test\TestCase\FooPath`.

## Configurar la clase BakeView

Los comandos Bake usan la clase `BakeView` para renderizar los templates.
Puede acceder a la instancia escuchando el evento `Bake.initialize`:

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
