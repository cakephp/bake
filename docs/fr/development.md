# Étendre Bake

Bake dispose d'une architecture extensible qui permet à votre application ou à vos plugins de modifier ou d'ajouter des fonctionnalités de base.
Bake utilise une classe de vue dédiée fondée sur le moteur de template [Twig](https://twig.symfony.com/).

## Events de Bake

Comme une classe de vue, `BakeView` envoie les mêmes events que toute autre classe de vue, ainsi qu'un event `initialize` supplémentaire.
Alors que les classes de vue standard utilisent le préfixe `View.`, `BakeView` utilise le préfixe `Bake.`.

L'event `initialize` peut être utilisé pour faire des changements qui s'appliquent à toutes les sorties générées par Bake.
Par exemple, pour ajouter un helper :

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;

// dans src/Application::bootstrapCli()

EventManager::instance()->on('Bake.initialize', function (EventInterface $event) {
    $view = $event->getSubject();

    // Dans mes templates de bake, permet l'utilisation du helper MySpecial
    $view->loadHelper('MySpecial', ['some' => 'config']);

    // Et ajoute une variable $author pour qu'elle soit toujours disponible
    $view->set('author', 'Andy');
});
```

Les events de Bake peuvent aussi servir à faire de petits changements dans les templates existants.
Par exemple, pour changer les noms de variables utilisés lors de la création de fichiers de controller et de template :

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;

// dans src/Application::bootstrapCli()

EventManager::instance()->on('Bake.beforeRender', function (EventInterface $event) {
    $view = $event->getSubject();

    // Utilise $rows pour la principale variable de données dans les index
    if ($view->get('pluralName')) {
        $view->set('pluralName', 'rows');
    }
    if ($view->get('pluralVar')) {
        $view->set('pluralVar', 'rows');
    }

    // Utilise $theOne pour la principale variable de données dans les view/edit
    if ($view->get('singularName')) {
        $view->set('singularName', 'theOne');
    }
    if ($view->get('singularVar')) {
        $view->set('singularVar', 'theOne');
    }
});
```

Vous pouvez aussi scoper les events `Bake.beforeRender` et `Bake.afterRender` à un fichier généré spécifique.
Par exemple, pour ajouter des actions à `UsersController` lors de la génération depuis `Controller/controller.twig` :

```php
<?php
use Cake\Event\EventInterface;
use Cake\Event\EventManager;
use Cake\Utility\Hash;

// dans src/Application::bootstrapCli()

EventManager::instance()->on(
    'Bake.beforeRender.Controller.controller',
    function (EventInterface $event) {
        $view = $event->getSubject();
        if ($view->get('name') === 'Users') {
            // ajouter les actions login et logout au controller Users
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

En scopant les écouteurs d'event vers des templates de Bake spécifiques, vous simplifiez la logique liée à Bake et obtenez des callbacks plus faciles à tester.

## Syntaxe de template de Bake

Les fichiers de template de Bake utilisent la syntaxe [Twig](https://twig.symfony.com/).

Par exemple, si vous générez une commande comme ceci :

```bash
bin/cake bake command Foo
```

Le template utilisé dans `vendor/cakephp/bake/templates/bake/Command/command.twig` ressemble à ceci :

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
    * Méthode hook pour définir le parseur d'option de cette commande.
    *
    * @link https://book.cakephp.org/5/fr/console-commands/commands.html#defining-arguments-and-options
    * @param \Cake\Console\ConsoleOptionParser $parser Le parseur à définir
    * @return \Cake\Console\ConsoleOptionParser Le parseur construit.
    */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
    * Implémentez cette méthode avec la logique de votre commande.
    *
    * @param \Cake\Console\Arguments $args Les arguments de la commande.
    * @param \Cake\Console\ConsoleIo $io La console io
    * @return int|null|void Le code de sortie ou null pour un succès
    */
    public function execute(Arguments $args, ConsoleIo $io)
    {
    }
}
```

La classe résultante générée dans `src/Command/FooCommand.php` ressemble à ceci :

```php
<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
* Commande Foo.
*/
class FooCommand extends Command
{
    /**
    * Méthode hook pour définir le parseur d'option de cette commande.
    *
    * @link https://book.cakephp.org/5/fr/console-commands/commands.html#defining-arguments-and-options
    * @param \Cake\Console\ConsoleOptionParser $parser Le parseur à définir
    * @return \Cake\Console\ConsoleOptionParser Le parseur construit.
    */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
    * Implémentez cette méthode avec la logique de votre commande.
    *
    * @param \Cake\Console\Arguments $args Les arguments de la commande.
    * @param \Cake\Console\ConsoleIo $io La console io
    * @return int|null|void Le code de sortie ou null pour un succès
    */
    public function execute(Arguments $args, ConsoleIo $io)
    {
    }
}
```

## Créer un thème de Bake

Si vous souhaitez modifier la sortie produite par la commande `bake`, vous pouvez créer votre propre thème afin de remplacer tout ou partie des templates utilisés par Bake.

1. Créez un nouveau plugin avec Bake. Le nom du plugin devient le nom du thème. Par exemple `bin/cake bake plugin bake_perso`.
2. Créez un nouveau répertoire `plugins/BakePerso/templates/bake`.
3. Copiez les templates que vous souhaitez surcharger depuis `vendor/cakephp/bake/templates/bake` vers les fichiers correspondants dans votre plugin.
4. Lors de l'exécution de Bake, utilisez l'option `--theme BakePerso`. Pour éviter d'avoir à la répéter à chaque fois, vous pouvez définir le thème par défaut :

```php
<?php
// dans src/Application::bootstrapCli() avant de charger le plugin 'Bake'.
Configure::write('Bake.theme', 'MonTheme');
```

## Templates Bake d'application

Si vous n'avez besoin de personnaliser que quelques templates, ou si vous devez utiliser des dépendances de l'application dans vos templates, vous pouvez inclure des surcharges directement dans l'application.

1. Créez un nouveau répertoire `/templates/plugin/Bake/`.
2. Copiez les templates que vous souhaitez surcharger depuis `vendor/cakephp/bake/templates/bake/` vers les fichiers correspondants dans votre application.

Vous n'avez pas besoin d'utiliser l'option `--theme` quand vous utilisez des templates d'application.

## Créer de nouvelles options de commande pour Bake

Il est possible d'ajouter de nouvelles options de commande Bake, ou de surcharger celles fournies par CakePHP, en créant des commandes dans votre application ou dans vos plugins.
En étendant `Bake\Command\BakeCommand`, Bake trouvera votre nouvelle commande et l'inclura.

À titre d'exemple, créez le fichier `src/Command/Bake/FooCommand.php` :

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

Ensuite, créez `templates/bake/foo_template.twig` :

```php
<?php
namespace {{ namespace }}\FooPath;

/**
 * {{ name }} fooOut
 */
class {{ name }}FooOut
{
    // Ajouter le code.
}
```

Vous devriez maintenant voir votre nouvelle commande dans la sortie de `bin/cake bake`.
Vous pouvez l'exécuter avec `bin/cake bake foo Exemple`.
Cela générera `src/FooPath/ExempleFooOut.php`.

Si vous souhaitez que l'appel à `bake` crée aussi un fichier de test pour `ExempleFooOut`, surchargez `bakeTest()` dans `FooCommand` :

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

    return parent::bakeTest($className);
}
```

- Le **suffixe de classe** sera ajouté après le nom passé à `bake`. Dans l'exemple ci-dessus, cela créerait `ExempleFooTest.php`.
- Le **type de classe** sera le sous-namespace utilisé pour atteindre le fichier relatif à l'application ou au plugin. Dans l'exemple ci-dessus, cela créerait le namespace `App\Test\TestCase\Foo`.
