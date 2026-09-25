# Génération de code avec Bake

La console Bake est exécutée avec le CLI PHP.
Si vous avez des problèmes en exécutant ce script, vérifiez que :

1. Le CLI PHP est installé et qu'il a les bons modules activés, par exemple MySQL et `intl`.
2. Si l'hôte de la base de données est `localhost`, essayez `127.0.0.1`, car `localhost` peut causer des problèmes avec PHP CLI.
3. Selon la configuration de votre ordinateur, vous devrez peut-être donner les permissions d'exécution au script `cake` pour autoriser le lancement via `bin/cake bake`.

Avant de lancer Bake, vous devez vous assurer qu'au moins une connexion de base de données est configurée.

Vous pouvez voir la liste des commandes disponibles en lançant `bin/cake bake --help`.
Pour Windows, utilisez `bin\cake bake --help` :

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

## Modèles de Bake

Les modèles sont générés à partir des tables existantes de la base de données.
Les conventions de CakePHP s'appliquent : Bake détecte donc les relations à partir des clés étrangères `thing_id` vers les tables `things` et leurs clés primaires `id`.

Pour les relations non conventionnelles, vous pouvez utiliser des références dans les contraintes ou les définitions de clés étrangères afin que Bake détecte les relations :

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Enums de Bake

Vous pouvez utiliser Bake pour générer des [enums adossés](https://www.php.net/manual/fr/language.enumerations.backed.php) à utiliser dans vos modèles.
Les enums sont placées dans `src/Model/Enum/`, implémentent `EnumLabelInterface` et utilisent `EnumLabelTrait`, qui fournit une méthode `label()` pour un affichage lisible par l'humain.

Pour générer une enum adossée à des chaînes :

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

Cela génère `src/Model/Enum/ArticleStatus.php` :

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

Pour les enums adossées à des entiers, utilisez l'option `-i` et fournissez les valeurs séparées par des deux-points :

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

Cela génère une enum adossée à des entiers :

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

Vous pouvez aussi générer des enums dans les plugins :

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Thèmes de Bake

L'option `theme` est commune à toutes les commandes Bake et permet de changer les fichiers de template utilisés lors de la génération.
Pour créer vos propres templates, référez-vous à [Créer un thème de Bake](/fr/development#creer-un-theme-de-bake).
