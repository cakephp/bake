# Étendre Bake

Bake dispose d'une architecture extensible qui permet à votre application ou à vos plugins de modifier ou d'ajouter des fonctionnalités de base.
Bake utilise une classe de vue dédiée fondée sur le moteur de template [Twig](https://twig.symfony.com/).

## Events de Bake

Comme une classe de vue, `BakeView` envoie les mêmes events que toute autre classe de vue, ainsi qu'un event `initialize` supplémentaire.
Alors que les classes de vue standard utilisent le préfixe `View.`, `BakeView` utilise le préfixe `Bake.`.

L'event `initialize` peut être utilisé pour apporter des modifications qui s'appliquent à toutes les sorties générées par Bake.
Par exemple, pour ajouter un autre helper à la classe de vue de Bake :

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

Les events de Bake peuvent aussi servir à faire de petits changements dans les templates existants.
Par exemple, pour changer les noms de variables utilisés lors de la génération des fichiers de controller et de template, écoutez l'event `Bake.beforeRender` :

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

Vous pouvez aussi scoper les events `Bake.beforeRender` et `Bake.afterRender` à un fichier généré spécifique.
Par exemple, pour ajouter des actions spécifiques à votre `UsersController` lors de la génération depuis un fichier `Controller/controller.twig` :

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

En scopant les écouteurs d'event vers des templates de Bake spécifiques, vous simplifiez la logique liée à Bake et obtenez des callbacks plus faciles à tester.

## Syntaxe de template de Bake

Les fichiers de template de Bake utilisent la syntaxe [Twig](https://twig.symfony.com/).

Par exemple, si vous générez une commande comme ceci :

```bash
bin/cake bake command Foo
```

Le template utilisé dans `vendor/cakephp/bake/templates/bake/Command/command.twig` ressemble à ceci :

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

L'appel `element()` émet l'en-tête `<?php declare(strict_types=1);`, le namespace et les instructions `use`.
Notez que les commandes utilisent `$this->args` et `$this->io` au lieu de les recevoir en paramètres d'`execute()`.

La classe résultante générée dans `src/Command/FooCommand.php` ressemble à ceci :

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

## Créer un thème de Bake

Si vous souhaitez modifier la sortie produite par la commande `bake`, vous pouvez créer votre propre thème de bake, ce qui vous permet de remplacer tout ou partie des templates utilisés par Bake.

1. Créez un nouveau plugin avec Bake. Le nom du plugin devient le nom du thème de bake. Par exemple `bin/cake bake plugin custom_bake`.
2. Créez un nouveau répertoire `plugins/CustomBake/templates/bake`.
3. Copiez les templates que vous souhaitez surcharger depuis `vendor/cakephp/bake/templates/bake` vers les fichiers correspondants dans votre plugin.
4. Lors de l'exécution de Bake, utilisez l'option `--theme CustomBake` pour utiliser votre thème de bake. Pour éviter d'avoir à la spécifier à chaque fois, vous pouvez aussi définir votre thème personnalisé par défaut :

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## Templates Bake d'application

Si vous n'avez besoin de personnaliser que quelques templates de bake, ou si vous devez utiliser des dépendances de l'application dans vos templates, vous pouvez inclure des surcharges de templates dans les templates de votre application.
Ces surcharges fonctionnent comme la surcharge d'autres templates de plugin.

1. Créez un nouveau répertoire `/templates/plugin/Bake/`.
2. Copiez les templates que vous souhaitez surcharger depuis `vendor/cakephp/bake/templates/bake/` vers les fichiers correspondants dans votre application.

Vous n'avez pas besoin d'utiliser l'option `--theme` quand vous utilisez des templates d'application.

## Créer de nouvelles options de commande pour Bake

Il est possible d'ajouter de nouvelles options de commande Bake, ou de surcharger celles fournies par CakePHP, en créant des commandes dans votre application ou dans vos plugins.
En étendant `Bake\Command\BakeCommand`, Bake trouvera votre nouvelle commande et l'inclura dans bake.

À titre d'exemple, créez le fichier de commande `src/Command/Bake/FooCommand.php`.
Nous étendrons `SimpleBakeCommand` car la commande est simple :

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

Ensuite, créez `templates/bake/foo_template.twig` :

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

Vous devriez maintenant voir votre nouvelle commande dans la sortie de `bin/cake bake`.
Lancez-la avec `bin/cake bake foo Example`.
Cela génère `src/FooPath/ExampleFooOut.php`.

Si vous souhaitez que `bake` crée aussi un fichier de test pour votre classe `ExampleFooOut`, surchargez la méthode `bakeTest()` dans `FooCommand` :

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

- Le **suffixe de classe** est ajouté après le nom fourni dans votre appel à `bake`. Dans l'exemple ci-dessus, cela créerait `ExampleFooOut` et son fichier de test `tests/TestCase/FooPath/ExampleFooOutTest.php`.
- La valeur du **type de classe** est le sous-namespace utilisé pour atteindre votre fichier depuis l'application ou le plugin dans lequel vous générez. Dans l'exemple ci-dessus, cela créerait le namespace de test `App\Test\TestCase\FooPath`.

## Configurer la classe BakeView

Les commandes Bake utilisent la classe `BakeView` pour rendre les templates.
Vous pouvez accéder à l'instance en écoutant l'event `Bake.initialize` :

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
