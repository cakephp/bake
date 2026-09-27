# Estendendo o Bake

O Bake possui uma arquitetura extensível que permite à sua aplicação ou plugins
modificar ou complementar as funcionalidades básicas.
O Bake faz uso de uma classe de view dedicada que usa o mecanismo de templates [Twig](https://twig.symfony.com/).

## Eventos do Bake

Como uma classe view, `BakeView` emite os mesmos eventos que qualquer outra classe view, mais um evento extra de inicialização.
No entanto, enquanto as classes view padrão usam o prefixo de evento `View.`, `BakeView` usa o prefixo de evento `Bake.`.

O evento de inicialização pode ser usado para fazer mudanças que se aplicam a toda saída gerada pelo Bake.
Por exemplo, para adicionar outro helper à classe view do Bake:

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

Os eventos do Bake também podem ser úteis para fazer pequenas alterações nos templates existentes.
Por exemplo, para alterar os nomes das variáveis usadas ao gerar os arquivos de controller e template, escute o evento `Bake.beforeRender`:

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

Você também pode restringir os eventos `Bake.beforeRender` e `Bake.afterRender` a um arquivo gerado específico.
Por exemplo, se você quiser adicionar ações específicas ao seu `UsersController` ao gerar a partir de um arquivo `Controller/controller.twig`:

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

Ao restringir os listeners de eventos a templates específicos do bake, você simplifica a lógica de eventos relacionada ao bake e fornece callbacks mais fáceis de testar.

## Sintaxe de Templates do Bake

Os arquivos de template do Bake usam a sintaxe de templates [Twig](https://twig.symfony.com/).

Por exemplo, ao gerar um comando como este:

```bash
bin/cake bake command Foo
```

O template usado em `vendor/cakephp/bake/templates/bake/Command/command.twig` tem este aspecto:

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

A chamada `element()` emite o cabeçalho `<?php declare(strict_types=1);`, o namespace e as instruções `use`.
Note que os comandos usam `$this->args` e `$this->io` em vez de recebê-los como parâmetros de `execute()`.

A classe gerada em `src/Command/FooCommand.php` fica assim:

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

## Criando um Tema Bake

Se você deseja modificar a saída produzida pelo comando `bake`, pode criar seu próprio tema do Bake, o que permite substituir alguns ou todos os templates que o Bake usa.

1. Gere um novo plugin. O nome do plugin é o nome do tema do Bake. Por exemplo, `bin/cake bake plugin custom_bake`.
2. Crie um novo diretório em `plugins/CustomBake/templates/bake`.
3. Copie quaisquer templates que queira sobrescrever de `vendor/cakephp/bake/templates/bake` para arquivos correspondentes no seu plugin.
4. Ao executar o Bake, use a opção `--theme CustomBake` para usar seu tema do bake. Para evitar especificar essa opção toda vez, você também pode definir seu tema personalizado como padrão:

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## Templates de Bake da Aplicação

Se você só precisa personalizar alguns templates do bake, ou precisa usar dependências da aplicação em seus templates, pode incluir sobrescritas de templates nos templates da aplicação.
Essas sobrescritas funcionam de forma semelhante à sobrescrita de outros templates de plugins.

1. Crie um novo diretório em `/templates/plugin/Bake/`.
2. Copie quaisquer templates que queira sobrescrever de `vendor/cakephp/bake/templates/bake/` para arquivos correspondentes na sua aplicação.

Você não precisa usar a opção `--theme` ao usar templates da aplicação.

## Criando Novas Opções de Comando do Bake

É possível adicionar novas opções de comando do bake, ou sobrescrever as fornecidas pelo CakePHP, criando comandos na sua aplicação ou plugins.
Ao estender `Bake\Command\BakeCommand`, o Bake encontrará seu novo comando e o incluirá como parte do bake.

Como exemplo, crie o arquivo do comando `src/Command/Bake/FooCommand.php`.
Estenderemos `SimpleBakeCommand` porque o comando é simples:

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

Em seguida, crie `templates/bake/foo_template.twig`:

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

Agora você deve ver seu novo comando na saída de `bin/cake bake`.
Execute-o com `bin/cake bake foo Example`.
Isso gera `src/FooPath/ExampleFooOut.php`.

Se você também quiser que o `bake` crie um arquivo de teste para a sua classe `ExampleFooOut`, sobrescreva o método `bakeTest()` em `FooCommand`:

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

- O **sufixo da classe** é anexado ao nome fornecido na sua chamada ao `bake`. No exemplo acima, isso criaria `ExampleFooOut` e seu arquivo de teste `tests/TestCase/FooPath/ExampleFooOutTest.php`.
- O valor do **tipo de classe** é o sub-namespace usado para acessar seu arquivo em relação ao app ou plugin no qual você está gerando. No exemplo acima, isso criaria o namespace de teste `App\Test\TestCase\FooPath`.

## Configurando a Classe BakeView

Os comandos do Bake usam a classe `BakeView` para renderizar templates.
Você pode acessar a instância escutando o evento `Bake.initialize`:

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
