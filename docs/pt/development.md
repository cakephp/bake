# Estendendo o Bake

O Bake fornece uma arquitetura expansível que permite à sua aplicação ou plugin modificar ou adicionar funcionalidades às funções básicas.
Bake faz uso de uma classe view dedicada que usa o mecanismo de templates [Twig](https://twig.symfony.com/).

## Eventos do Bake

Como uma classe view, `BakeView` emite os mesmos eventos que qualquer outra classe view, mais um evento extra de inicialização.
Enquanto as classes view padrão usam o prefixo `View.`, `BakeView` usa o prefixo `Bake.`.

O evento de inicialização pode ser usado para fazer mudanças que se aplicam a todas as saídas do Bake.
Por exemplo, ao adicionar outro helper à classe view do Bake:

```php
<?php
// config/bootstrap_cli.php

use Cake\Event\Event;
use Cake\Event\EventManager;

EventManager::instance()->on('Bake.initialize', function (Event $event) {
    $view = $event->getSubject();

    // In my bake templates, allow the use of the MySpecial helper
    $view->loadHelper('MySpecial', ['some' => 'config']);

    // And add an $author variable so it's always available
    $view->set('author', 'Andy');
});
```

Se você deseja modificar o Bake a partir de outro plugin, é recomendável colocar os eventos do plugin no arquivo `config/bootstrap.php`.

Os eventos do Bake podem ser úteis para pequenas alterações nos templates existentes.
Por exemplo, para alterar os nomes das variáveis usados no controller e template quando executar o Bake:

```php
<?php
// config/bootstrap_cli.php

use Cake\Event\Event;
use Cake\Event\EventManager;

EventManager::instance()->on('Bake.beforeRender', function (Event $event) {
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

Você também pode aplicar os eventos `Bake.beforeRender` e `Bake.afterRender` a um arquivo específico.
Por exemplo, se quiser adicionar ações ao `UsersController` ao gerar a partir de `Controller/controller.twig`:

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

Ao adicionar listeners específicos para determinados templates do Bake, você simplifica a lógica relacionada ao Bake e fornece callbacks fáceis de testar.

## Sintaxe de Templates do Bake

Os arquivos de template do Bake usam a sintaxe [Twig](https://twig.symfony.com/doc/2.x/).

Então, por exemplo, quando você executar algo como:

```bash
bin/cake bake shell Foo
```

O template usado em `vendor/cakephp/bake/src/Template/Bake/Shell/shell.twig` parece com isto:

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

E o resultado gerado em `src/Shell/FooShell.php` é semelhante a:

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
Nas versões anteriores à 1.5.0, o Bake usava tags estilo ERB dentro dos arquivos de template `.ctp`.

- `<%` abre uma tag PHP de template Bake.
- `%>` fecha uma tag PHP de template Bake.
- `<%=` é a forma short-echo do template Bake.
- `<%-` abre a tag removendo espaços em branco antes dela.
- `-%>` fecha a tag removendo espaços em branco após ela.
:::

## Criando um Tema Bake

Se você deseja modificar a saída produzida com o comando `bake`, pode criar seu próprio tema para substituir alguns ou todos os templates que o Bake usa.

1. Gere um novo plugin. O nome do plugin é o nome do tema.
2. Crie uma nova pasta em `plugins/[name]/Template/Bake/Template/`.
3. Copie qualquer template que queira modificar de `vendor/cakephp/bake/src/Template/Bake/Template` para a pasta acima e altere conforme sua necessidade.
4. Ao executar o Bake, use a opção `--theme` para especificar o tema. Para evitar repetir isso a cada chamada, também é possível definir o tema padrão:

```php
<?php
// no config/bootstrap.php ou no config/bootstrap_cli.php
Configure::write('Bake.theme', 'MyTheme');
```

## Customizando os Templates do Bake

Se você deseja modificar a saída padrão produzida pelo comando `bake`, também pode criar seus próprios templates diretamente na aplicação.
Nesse caso, você não precisa usar `--theme` na linha de comando.

1. Crie o diretório `/Template/Bake/`.
2. Copie os arquivos que deseja sobrescrever de `vendor/cakephp/bake/src/Template/Bake/` e modifique-os.

## Criando Novos Comandos Bake

É possível adicionar novas opções de comando ou sobrescrever algumas providas pelo CakePHP criando tarefas na sua aplicação ou plugin.
Ao estender `Bake\Shell\Task\BakeTask`, o Bake encontra a nova tarefa e a inclui em sua lista.

Como exemplo, criaremos uma tarefa que gera uma classe `foo`.
Primeiro, crie `src/Shell/Task/FooTask.php`.
Vamos estender `SimpleBakeTask` porque a nova shell task será simples.

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

Depois, crie `/Template/Bake/foo.twig`:

```php
<?php
namespace {{ namespace }}\Foo;

/**
 * {{ $name }} foo
 */
class {{ name }}Foo
{
    // Adicione código.
}
```

Agora a nova tarefa deve aparecer na saída de `bin/cake bake`.
Você pode executá-la com `bin/cake bake foo Example`, o que gerará `src/Foo/ExampleFoo.php`.

Se você quiser que o `bake` também crie um arquivo de teste para `ExampleFoo`, sobrescreva o método `bakeTest()` em `FooTask`:

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

- O **sufixo da classe** será anexado ao nome fornecido na chamada ao Bake. No exemplo acima, isso criaria `ExampleFooTest.php`.
- O **tipo de classe** será o subnamespace usado para levar ao arquivo relativo à aplicação ou plugin. No exemplo acima, isso criaria o namespace `App\Test\TestCase\Foo`.
