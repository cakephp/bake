# Расширение возможностей Bake

Bake имеет расширяемую архитектуру, которая позволяет вашему приложению или плагинам изменять или дополнять базовую функциональность.
Bake использует выделенный класс представления, работающий с движком шаблонов [Twig](https://twig.symfony.com/).

## События Bake

Как и любой другой класс представления, `BakeView` генерирует те же события, плюс одно дополнительное событие инициализации.
Однако стандартные классы представления используют префикс события `View.`, а `BakeView` — префикс `Bake.`.

Событие инициализации можно использовать для внесения изменений, которые применяются ко всему выводу Bake.
Например, чтобы добавить ещё один helper в класс представления Bake:

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

События Bake также пригодятся для небольших изменений существующих шаблонов.
Например, чтобы изменить имена переменных, используемых при генерации файлов контроллера и шаблона, слушайте событие `Bake.beforeRender`:

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

Вы также можете ограничить события `Bake.beforeRender` и `Bake.afterRender` конкретным генерируемым файлом.
Например, если вы хотите добавить определённые действия в ваш `UsersController` при генерации из файла `Controller/controller.twig`:

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

Привязывая обработчики событий к конкретным шаблонам bake, вы упрощаете связанную с Bake логику событий и получаете более удобные для тестирования callback-функции.

## Синтаксис шаблонов Bake

Файлы шаблонов Bake используют синтаксис шаблонов [Twig](https://twig.symfony.com/).

Например, при генерации команды следующим образом:

```bash
bin/cake bake command Foo
```

Шаблон, используемый в `vendor/cakephp/bake/templates/bake/Command/command.twig`, выглядит так:

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

Вызов `element()` выводит заголовок `<?php declare(strict_types=1);`, пространство имён и инструкции `use`.
Обратите внимание, что команды используют `$this->args` и `$this->io` вместо того, чтобы получать их в качестве параметров `execute()`.

Итоговый сгенерированный класс в `src/Command/FooCommand.php` выглядит так:

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

## Создание темы Bake

Если вы хотите изменить вывод, создаваемый командой `bake`, вы можете создать собственную тему Bake, которая позволит заменить часть или все шаблоны, используемые Bake.

1. Сгенерируйте новый плагин. Имя плагина станет именем темы Bake. Например, `bin/cake bake plugin custom_bake`.
2. Создайте новую директорию `plugins/CustomBake/templates/bake`.
3. Скопируйте нужные шаблоны из `vendor/cakephp/bake/templates/bake` в соответствующие файлы вашего плагина.
4. При запуске Bake используйте параметр `--theme CustomBake`, чтобы применить вашу тему. Чтобы не указывать его каждый раз, можно сделать вашу собственную тему темой по умолчанию:

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## Шаблоны Bake приложения

Если вам нужно настроить лишь несколько шаблонов bake или использовать зависимости приложения в ваших шаблонах, вы можете разместить переопределения шаблонов в шаблонах приложения.
Такие переопределения работают так же, как переопределение других шаблонов плагинов.

1. Создайте новую директорию `/templates/plugin/Bake/`.
2. Скопируйте нужные шаблоны из `vendor/cakephp/bake/templates/bake/` в соответствующие файлы вашего приложения.

При использовании шаблонов приложения параметр `--theme` использовать не нужно.

## Создание новых параметров команды Bake

Можно добавить новые команды bake или переопределить команды, предоставляемые CakePHP, создавая команды в вашем приложении или плагинах.
Если расширить `Bake\Command\BakeCommand`, Bake найдёт вашу новую команду и включит её в состав bake.

В качестве примера создайте файл команды `src/Command/Bake/FooCommand.php`.
Мы расширим `SimpleBakeCommand`, так как команда простая:

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

Затем создайте `templates/bake/foo_template.twig`:

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

Теперь вы должны увидеть новую команду в выводе `bin/cake bake`.
Запустите её командой `bin/cake bake foo Example`.
Это создаст `src/FooPath/ExampleFooOut.php`.

Если вы хотите, чтобы `bake` также создавал тестовый файл для вашего класса `ExampleFooOut`, переопределите метод `bakeTest()` в `FooCommand`:

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

- **Суффикс класса** добавляется к имени, переданному в вашем вызове `bake`. В примере выше это создаст `ExampleFooOut` и его тестовый файл `tests/TestCase/FooPath/ExampleFooOutTest.php`.
- Значение **типа класса** — это подпространство имён, ведущее к вашему файлу относительно приложения или плагина, в который выполняется генерация. В примере выше это создаст namespace теста `App\Test\TestCase\FooPath`.

## Настройка класса BakeView

Команды Bake используют класс `BakeView` для отрисовки шаблонов.
Получить доступ к экземпляру можно, слушая событие `Bake.initialize`:

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
