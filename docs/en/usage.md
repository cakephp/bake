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
Current Paths:

* app:  src/
* root: /path/to/your/app/
* core: /path/to/your/app/vendor/cakephp/cakephp/

Available Commands:

Bake:
- bake all
- bake behavior
- bake cell
- bake command
- bake command_helper
- bake component
- bake controller
- bake controller all
- bake enum
- bake fixture
- bake fixture all
- bake form
- bake helper
- bake mailer
- bake middleware
- bake model
- bake model all
- bake plugin
- bake template
- bake template all
- bake test

To run a command, type `cake command_name [args|options]`
To get help on a specific command, type `cake command_name --help`
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
Enums are placed in `src/Model/Enum/` and implement `EnumLabelInterface`, which provides a `label()` method for human-readable display.

To bake a string-backed enum:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

This generates `src/Model/Enum/ArticleStatus.php`:

```php
namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;
use Cake\Utility\Inflector;

enum ArticleStatus: string implements EnumLabelInterface
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return Inflector::humanize(Inflector::underscore($this->name));
    }
}
```

For int-backed enums, use the `-i` option and provide values with colons:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

This generates an int-backed enum:

```php
enum Priority: int implements EnumLabelInterface
{
    case Low = 1;
    case Medium = 2;
    case High = 3;

    // ...
}
```

You can also bake enums into plugins:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Bake Themes

The `theme` option is common to all bake commands and allows changing the bake template files used when baking.
To create your own templates, see [Creating a Bake Theme](/development#creating-a-bake-theme).
