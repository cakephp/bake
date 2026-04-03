# Расширение возможностей Bake

Bake имеет расширяемую архитектуру, которая позволяет вашему приложению или плагинам изменять или дополнять базовую функциональность.
Bake использует специальный класс представления и механизм шаблонизатора [Twig](https://twig.symfony.com/).

## События Bake

`BakeView`, как и любой другой класс представления, генерирует стандартные события, а также дополнительное событие инициализации.
Стандартные классы представления используют префикс `View.`, а `BakeView` использует префикс `Bake.`.

Событие `initialize` можно использовать для внесения изменений, которые применяются ко всему выводу Bake.
Например, чтобы добавить helper в класс представления Bake:

```php
<?php
// config/bootstrap_cli.php

use Cake\Event\Event;
use Cake\Event\EventManager;

EventManager::instance()->on('Bake.initialize', function (Event $event) {
    $view = $event->getSubject();

    // В моих шаблонах bake разрешить использование MySpecial helper
    $view->loadHelper('MySpecial', ['some' => 'config']);

    // И добавить переменную $author, чтобы она всегда была доступна
    $view->set('author', 'Andy');
});
```

Если вы хотите изменить Bake из другого плагина, удобнее всего разместить события плагина в `config/bootstrap.php`.

События Bake полезны и для небольших изменений существующих шаблонов.
Например, чтобы изменить имена переменных, используемых при генерации controller и template файлов, можно слушать `Bake.beforeRender`:

```php
<?php
// config/bootstrap_cli.php

use Cake\Event\Event;
use Cake\Event\EventManager;

EventManager::instance()->on('Bake.beforeRender', function (Event $event) {
    $view = $event->getSubject();

    // Использовать $rows для основной переменной данных в index
    if ($view->get('pluralName')) {
        $view->set('pluralName', 'rows');
    }
    if ($view->get('pluralVar')) {
        $view->set('pluralVar', 'rows');
    }

    // Использовать $theOne для основной переменной данных в view/edit
    if ($view->get('singularName')) {
        $view->set('singularName', 'theOne');
    }
    if ($view->get('singularVar')) {
        $view->set('singularVar', 'theOne');
    }
});
```

Вы также можете привязать события `Bake.beforeRender` и `Bake.afterRender` к конкретному генерируемому файлу.
Например, если вы хотите добавить действия в `UsersController` при генерации из `Controller/controller.twig`:

```php
<?php
// config/bootstrap_cli.php

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Hash;

EventManager::instance()->on(
    'Bake.beforeRender.Controller.controller',
    function (Event $event) {
        $view = $event->getSubject();
        if ($view->viewVars['name'] == 'Users') {
            // добавим действия входа и выхода в контроллер Users
            $view->viewVars['actions'] = [
                'login',
                'logout',
                'index',
                'view',
                'add',
                'edit',
                'delete',
            ];
        }
    }
);
```

Фокусируя обработчики на конкретных шаблонах Bake, вы упрощаете связанную с Bake логику событий и получаете более удобные для тестирования callback-функции.

## Синтаксис шаблонов Bake

Файлы шаблонов Bake используют синтаксис [Twig](https://twig.symfony.com/doc/2.x/).

Например, при генерации shell-команды:

```bash
bin/cake bake shell Foo
```

Шаблон `vendor/cakephp/bake/src/Template/Bake/Shell/shell.twig` выглядит так:

```php
<?php
namespace {{ namespace }}\Shell;

use Cake\Console\Shell;

/**
 * {{ name }} shell command.
 */
class {{ name }}Shell extends Shell
{
    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main()
    {
    }
}
```

И итоговый класс `src/Shell/FooShell.php` будет выглядеть так:

```php
<?php
namespace App\Shell;

use Cake\Console\Shell;

/**
 * Foo shell command.
 */
class FooShell extends Shell
{
    /**
     * main() method.
     *
     * @return bool|int Success or error code.
     */
    public function main()
    {
    }
}
```

::: info
До версии 1.5.0 Bake использовал пользовательские теги ERB-стиля внутри `.ctp` файлов шаблонов.

- `<%` открывающий PHP-тег шаблона Bake.
- `%>` закрывающий PHP-тег шаблона Bake.
- `<%=` короткий echo-тег шаблона Bake.
- `<%-` открывающий тег с удалением пробелов перед тегом.
- `-%>` закрывающий тег с удалением пробелов после тега.
:::

## Создание темы Bake

Если вы хотите изменить вывод, создаваемый командой `bake`, вы можете создать собственную тему Bake, которая позволит заменить часть или все шаблоны.

1. Сгенерируйте новый плагин. Имя плагина станет именем темы Bake.
2. Создайте директорию `plugins/[name]/src/Template/Bake/Template/`.
3. Скопируйте нужные шаблоны из `vendor/cakephp/bake/src/Template/Bake/Template` в соответствующие файлы вашего плагина.
4. При запуске Bake используйте параметр `--theme`, чтобы указать тему. Чтобы не передавать его каждый раз, можно настроить тему по умолчанию:

```php
<?php
// В config/bootstrap.php или config/bootstrap_cli.php
Configure::write('Bake.theme', 'MyTheme');
```

## Настройка шаблонов Bake

Если вы хотите изменить стандартный вывод команды `bake`, вы можете создать собственные шаблоны прямо в приложении.
В этом случае использовать `--theme` в командной строке не нужно.

1. Создайте директорию `/src/Template/Bake/`.
2. Скопируйте шаблоны, которые хотите изменить, из `vendor/cakephp/bake/src/Template/Bake/`.

## Создание новых параметров команды Bake

Можно добавить новые параметры команды Bake или переопределить существующие, создавая задачи в приложении или плагине.
Если расширить `Bake\Shell\Task\BakeTask`, Bake найдёт новую задачу и включит её в список доступных.

В качестве примера создадим задачу, которая генерирует произвольный класс `foo`.
Сначала создайте файл `src/Shell/Task/FooTask.php`.
Мы расширим `SimpleBakeTask`, так как новая shell task будет простой.

```php
<?php
namespace App\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;

class FooTask extends SimpleBakeTask
{
    public $pathFragment = 'Foo/';

    public function name()
    {
        return 'foo';
    }

    public function fileName($name)
    {
        return $name . 'Foo.php';
    }

    public function template()
    {
        return 'foo';
    }
}
```

После этого создайте `src/Template/Bake/foo.twig`:

```php
<?php
namespace {{ namespace }}\Foo;

/**
 * {{ $name }} foo
 */
class {{ name }}Foo
{
    // Добавить код.
}
```

Теперь вы должны увидеть новую задачу в выводе `bin/cake bake`.
Запустите её командой `bin/cake bake foo Example`.
Это создаст класс `ExampleFoo` в `src/Foo/ExampleFoo.php`.

Если вы хотите, чтобы вызов `bake` также создавал тестовый файл для `ExampleFoo`, переопределите метод `bakeTest()` в `FooTask`:

```php
public function bakeTest($className)
{
    if (!isset($this->Test->classSuffixes[$this->name()])) {
      $this->Test->classSuffixes[$this->name()] = 'Foo';
    }

    $name = ucfirst($this->name());
    if (!isset($this->Test->classTypes[$name])) {
      $this->Test->classTypes[$name] = 'Foo';
    }

    return parent::bakeTest($className);
}
```

- **Суффикс класса** добавляется к имени, переданному в вызове `bake`. В примере выше это создаст `ExampleFooTest.php`.
- **Тип класса** определяет подпространство имён, ведущее к файлу относительно приложения или плагина. В примере выше это создаст тест с namespace `App\Test\TestCase\Foo`.
