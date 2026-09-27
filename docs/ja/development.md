# Bake の拡張

Bake は、アプリケーションやプラグインが基本機能を変更または追加できる拡張可能なアーキテクチャーを備えています。
Bake は [Twig](https://twig.symfony.com/) テンプレートエンジンを使う専用のビュークラスを利用します。

## Bake イベント

`BakeView` は、他のビュークラスと同様のイベントに加え、特別な initialize イベントを発します。
標準ビュークラスはイベントプレフィックス `View.` を使いますが、`BakeView` は `Bake.` を使います。

initialize イベントは、すべての Bake 出力に対する変更に使用できます。
たとえば、Bake ビュークラスに別のヘルパーを追加するには次のようにします。

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

Bake イベントは、既存テンプレートへの小さな変更にも役立ちます。
たとえば、コントローラーやテンプレートファイルを Bake するときに使う変数名を変更するには `Bake.beforeRender` を利用します。

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

`Bake.beforeRender` と `Bake.afterRender` を特定の生成ファイルに限定することもできます。
たとえば `Controller/controller.twig` から生成する際に `UsersController` に特定アクションを追加したい場合は次のイベントを使用できます。

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

特定の Bake テンプレートにイベントリスナーを絞ることで、Bake 関連のイベントロジックを単純にし、テストしやすいコールバックを提供できます。

## Bake テンプレート構文

Bake テンプレートファイルは [Twig](https://twig.symfony.com/) 構文を使用します。

たとえば、次のようにコマンドを Bake した場合:

```bash
bin/cake bake command Foo
```

`vendor/cakephp/bake/templates/bake/Command/command.twig` のテンプレートは次のようになります。

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

`element()` 呼び出しは `<?php declare(strict_types=1);` ヘッダー、名前空間、`use` 文を出力します。
なお、コマンドは `execute()` にパラメータとして受け取る代わりに、`$this->args` と `$this->io` を使用します。

生成される `src/Command/FooCommand.php` は次のようになります。

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

## Bake テーマの作成

`bake` コマンドで生成された出力を変更したい場合は、一部または全部のテンプレートを置き換える独自テーマを作成できます。

1. 新しいプラグインを Bake します。プラグイン名が Bake テーマ名になります。例: `bin/cake bake plugin custom_bake`
2. `plugins/CustomBake/templates/bake` を作成します。
3. 上書きしたいテンプレートを `vendor/cakephp/bake/templates/bake` から対応する場所へコピーします。
4. Bake 実行時に `--theme CustomBake` オプションで bake テーマを使います。毎回指定しなくても、カスタムテーマをデフォルトに設定しておくこともできます。

```php
<?php
// in src/Application::bootstrapCli() before loading the 'Bake' plugin.
Configure::write('Bake.theme', 'MyTheme');
```

## アプリケーション Bake テンプレート

一部の Bake テンプレートだけをカスタマイズしたい場合や、アプリケーション依存のテンプレートを使いたい場合は、アプリケーション側にオーバーライドテンプレートを置けます。
このオーバーライドは、他のプラグインテンプレートの上書きと同様に機能します。

1. `/templates/plugin/Bake/` を作成します。
2. `vendor/cakephp/bake/templates/bake/` から上書きしたいテンプレートをアプリケーション内の対応する場所にコピーします。

アプリケーションテンプレートを使う場合は `--theme` オプションは不要です。

## Bake コマンドオプションの新規作成

アプリケーションやプラグインで新しい Bake コマンドオプションを追加したり、CakePHP が提供するオプションを上書きしたりできます。
`Bake\Command\BakeCommand` を継承すると、Bake はその新しいコマンドを見つけて Bake の一部として扱います。

例として、コマンドファイル `src/Command/Bake/FooCommand.php` を作成します。
コマンドがシンプルなため、`SimpleBakeCommand` を継承します:

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

次に `templates/bake/foo_template.twig` を作成します。

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

これで `bin/cake bake` の出力に新しいコマンドが表示されるはずです。
`bin/cake bake foo Example` を実行してください。
`src/FooPath/ExampleFooOut.php` が生成されます。

また、`ExampleFooOut` クラスのテストファイルも生成したい場合は、`FooCommand` クラスで `bakeTest()` をオーバーライドします。

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

- **class suffix** は `bake` 呼び出しで与えた名前に追加されます。上の例では、`ExampleFooOut` とそのテストファイル `tests/TestCase/FooPath/ExampleFooOutTest.php` が作成されます。
- **class type** の値は、Bake 対象のアプリケーションまたはプラグインを基準に、ファイルへ到達するために使うサブ名前空間です。上の例では、テストの名前空間 `App\Test\TestCase\FooPath` が作成されます。

## BakeView クラスの設定

Bake コマンドは、テンプレートをレンダリングするために `BakeView` クラスを使います。
`Bake.initialize` イベントを監視するとインスタンスにアクセスできます。

```php
<?php
\Cake\Event\EventManager::instance()->on(
    'Bake.initialize',
    function ($event, $view) {
        $view->loadHelper('Foo');
    }
);
```
