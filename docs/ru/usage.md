# Генерация кода с помощью Bake

Консоль Bake запускается с использованием PHP CLI.
Если у вас возникли проблемы с запуском скрипта, убедитесь, что:

1. У вас установлен PHP CLI и включены нужные модули, например MySQL и `intl`.
2. Если хост базы данных указан как `localhost`, попробуйте `127.0.0.1`, потому что `localhost` может вызывать проблемы в PHP CLI.
3. В зависимости от того, как настроен ваш компьютер, вам может потребоваться выдать права на выполнение скрипта Cake, чтобы запускать `bin/cake bake`.

Перед запуском Bake вы должны убедиться, что у вас настроено хотя бы одно соединение с базой данных.

Получить список доступных команд bake можно, запустив `bin/cake bake --help`.
В Windows используйте `bin\cake bake --help`:

```bash
$ bin/cake bake --help
bake:
  bake all
  bake behavior         Create a model behavior and test.
  bake cell             Create a view cell, template and test.
  bake command          Create a console command and test.
  bake command_helper   Create a console command helper and test.
  bake component
  bake controller       Create a controller and test
  bake controller all   Create all controllers for an application or plugin.
  bake enum             Create a model Enum
  bake fixture          Create a test fixture class.
  bake fixture all      Create all fixtures for an application or plugin.
  bake form             Create a form class and test.
  bake helper           Create a view helper and test.
  bake mailer           Create a mailer and test.
  bake middleware       Create a middleware class and test.
  bake model            Create a table class and its related entity, enums,
                        test fixture and tests.
  bake model all        Create all models, fixtures and tests in an application
                        or plugin.
  bake plugin           Create a plugin.
  bake template         Create a view template.
  bake template all     Create all view templates for all controllers in an
                        application or plugin.
  bake test             Create a test case skeleton for a class.

To run a command, type `cake command_name [args|options]`
To get help on a specific command, type `cake command_name --help`
To see full descriptions and plugin grouping, use `cake --help -v`
```

## Модели Bake

Модели генерируются на основе существующих таблиц базы данных.
Применяются соглашения CakePHP, поэтому Bake определяет связи по внешним ключам `thing_id`, ссылающимся на таблицы `things` с их первичными ключами `id`.

Для нестандартных связей можно использовать ссылки в ограничениях или определениях внешних ключей, чтобы Bake определил связи:

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Enum'ы Bake

С помощью Bake можно генерировать [backed enum'ы](https://www.php.net/manual/en/language.enumerations.backed.php) для использования в ваших моделях.
Enum'ы размещаются в `src/Model/Enum/`, реализуют `EnumLabelInterface` и используют `EnumLabelTrait`, который предоставляет метод `label()` для отображения в человекочитаемом виде.

Чтобы сгенерировать enum со строковыми значениями:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

В результате создаётся `src/Model/Enum/ArticleStatus.php`:

```php
<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

/**
 * ArticleStatus Enum
 */
enum ArticleStatus: string implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
```

Для enum с целочисленными значениями используйте параметр `-i` и передавайте значения через двоеточие:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

В результате создаётся enum с целочисленными значениями:

```php
<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Database\Type\EnumLabelTrait;

/**
 * Priority Enum
 */
enum Priority: int implements EnumLabelInterface
{
    use EnumLabelTrait;

    case Low = 1;
    case Medium = 2;
    case High = 3;
}
```

Enum'ы также можно генерировать внутри плагинов:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Темы Bake

Параметр `theme` является общим для всех команд bake и позволяет изменять файлы шаблонов, используемые при генерации.
Чтобы создать свои шаблоны, см. [Создание темы Bake](/ru/development#создание-темы-bake).
