# Code Generation with Bake

The Bake console is run using the PHP CLI.
If you have problems running the script, ensure that:

1. You have the PHP CLI installed and that it has the proper modules enabled, such as MySQL and `intl`.
2. If the database host is `localhost`, try `127.0.0.1` instead, as `localhost` can cause issues with PHP CLI.
3. Depending on how your computer is configured, you may need to set execute rights on the Cake shell script to call it using `bin/cake bake`.

Before running Bake you should make sure you have at least one database connection configured.

You can get the list of available bake commands by running `bin/cake bake --help`.
For Windows usage use `bin\cake bake --help`:

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

## Bake Models

Models are generically baked from existing database tables.
CakePHP conventions apply, so Bake detects relations based on `thing_id` foreign keys to `things` tables with their `id` primary keys.

For non-conventional relations, you can use references in constraints or foreign key definitions for Bake to detect relations:

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Bake Enums

You can use Bake to generate [backed enums](https://www.php.net/manual/en/language.enumerations.backed.php) for use in your models.
Enums are placed in `src/Model/Enum/`, implement `EnumLabelInterface` and use `EnumLabelTrait`, which provides a `label()` method for human-readable display.

To bake a string-backed enum:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

This generates `src/Model/Enum/ArticleStatus.php`:

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

For int-backed enums, use the `-i` option and provide values with colons:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

This generates an int-backed enum:

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

You can also bake enums into plugins:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Bake Themes

The `theme` option is common to all bake commands and allows changing the bake template files used when baking.
To create your own templates, see [Creating a Bake Theme](/development#creating-a-bake-theme).
