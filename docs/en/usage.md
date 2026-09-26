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

### Concrete Entity Properties

By default entity fields are stored as dynamic fields. Pass `--concrete-properties` to have Bake
declare real PHP properties on the baked entity class, following
[Declaring Concrete Properties](https://book.cakephp.org/6.x/orm/entities.html#declaring-concrete-properties)
in the CakePHP book:

```bash
bin/cake bake model Articles --concrete-properties
```

The generated properties use `public protected(set)` visibility, so they can be read directly
while writes still go through the entity's `set()` API. Class types are referenced by their
short name instead of a fully qualified name:

```php
use App\Model\Enum\Status;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;

class Article extends Entity
{
    public protected(set) int $id;
    public protected(set) ?string $title;
    public protected(set) ?DateTime $created;
    public protected(set) ?Status $status;
    public protected(set) bool $published;
    public protected(set) ?User $author;
    public protected(set) ?array $comments;
}
```

Classes outside the entity's namespace, like `Cake\I18n\DateTime` or the `Status` enum, are
imported, with imports kept alphabetically ordered. Classes in the same namespace, like the
`User` association (`App\Model\Entity\User`), don't need an import and are referenced by their
short name directly.

Note that:

- Fields used by `Cake\ORM\Entity` itself, such as `hidden`, `patchable`, `dirty` and `errors`,
  are skipped so those remain dynamic fields.
- No property is initialized, not even the nullable ones. Fields which have not been hydrated,
  like an association which has not been loaded, are uninitialized, so reading them directly
  raises an `Error` about accessing an uninitialized property. The entity API handles this
  safely: `$article->get('author')` and `hasValue('author')` return `null` and `false`
  respectively for such fields.
- The `@property` annotations in the class docblock are still generated as they can express
  types like an array of entities that PHP property types cannot.
- Re-baking an existing entity with the option and `--update` does not duplicate the
  declarations.

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
