# Crear código con Bake

La consola Bake se ejecuta usando PHP CLI.
Si tiene problemas para ejecutar el script, asegúrese de que:

1. Tiene instalado PHP CLI y que tiene habilitados los módulos adecuados, como MySQL e `intl`.
2. Si el host de la base de datos es `localhost`, intente usar `127.0.0.1` en su lugar, ya que `localhost` puede causar problemas con PHP CLI.
3. Dependiendo de cómo esté configurado su equipo, puede que necesite asignar permisos de ejecución al script shell de Cake para llamarlo usando `bin/cake bake`.

Antes de ejecutar Bake, asegúrese de tener al menos una conexión a base de datos configurada.

Puede obtener la lista de comandos bake disponibles ejecutando `bin/cake bake --help`.
En Windows, use `bin\cake bake --help`:

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

## Modelos Bake

Los modelos se generan de forma genérica a partir de las tablas existentes de la base de datos.
Se aplican las convenciones de CakePHP, por lo que Bake detecta las relaciones a partir de las claves externas `thing_id` hacia las tablas `things` con sus claves primarias `id`.

Para relaciones no convencionales, puede usar referencias en las restricciones o en las definiciones de claves externas para que Bake detecte las relaciones:

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Enums Bake

Puede usar Bake para generar [enums respaldados](https://www.php.net/manual/en/language.enumerations.backed.php) para usar en sus modelos.
Los enums se colocan en `src/Model/Enum/`, implementan `EnumLabelInterface` y usan `EnumLabelTrait`, que proporciona un método `label()` para una presentación legible.

Para generar un enum respaldado por cadenas:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

Esto genera `src/Model/Enum/ArticleStatus.php`:

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

Para enums respaldados por enteros, use la opción `-i` y proporcione los valores separados por dos puntos:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

Esto genera un enum respaldado por enteros:

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

También puede generar enums dentro de plugins:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Temas de Bake

La opción `theme` es común a todos los comandos bake y permite cambiar los archivos de template bake que se usan al generar.
Para crear sus propios templates, consulte [Crear un tema de Bake](/es/development#crear-un-tema-de-bake).
