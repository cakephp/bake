Bake の拡張
###########

Bake は、アプリケーションやプラグインが基本機能に対して変更または追加を可能にする
拡張可能なアーキテクチャーを備えています。Bake は、 `Twig <https://twig.symfony.com/>`_
テンプレートエンジンを使用したビュークラスを利用します。

Bake イベント
=============

``BakeView`` は、ビュークラスとして、他のビュークラスと同様のイベントに加え、
1つの特別な初期化 (initialize) イベントを発します。しかし、一方で標準ビュークラスは、
イベントのプレフィックス "View." を使用しますが、 ``BakeView`` は、
イベントのプレフィックス "Bake." を使用しています。

初期化イベントは、すべての bake の出力に対して変更を加えるために使用できます。
例えば、bake ビュークラスに他のヘルパーを追加するためにこのイベントは使用されます。 ::

    <?php
    use Cake\Event\EventInterface;
    use Cake\Event\EventManager;

    // in src/Application::bootstrapCli()

    EventManager::instance()->on('Bake.initialize', function (EventInterface $event) {
        $view = $event->getSubject();

        // bake テンプレートの中で MySpecial ヘルパーの使用を可能にします
        $view->loadHelper('MySpecial', ['some' => 'config']);

        // そして、$author 変数を利用可能にするために追加
\        $view->set('author', 'Andy');
    });

別のプラグインの中から bake を変更したい場合は、プラグインの ``config/bootstrap.php``
ファイルでプラグインの Bake イベントを置くことは良いアイデアです。

Bake イベントは、既存のテンプレートに小さな変更を行うための便利なことができます。
例えば、コントローラーやテンプレートファイルを bake する際に使用される変数名を
変更するために、bake テンプレートで使用される変数を変更するために
``Bake.beforeRender`` で呼び出される関数を使用することができます。 ::

    <?php
    use Cake\Event\EventInterface;
    use Cake\Event\EventManager;

    // in src/Application::bootstrapCli()

    EventManager::instance()->on('Bake.beforeRender', function (EventInterface $event) {
        $view = $event->getSubject();

        // indexes の中のメインデータ変数に $rows を使用
        if ($view->get('pluralName')) {
            $view->set('pluralName', 'rows');
        }
        if ($view->get('pluralVar')) {
            $view->set('pluralVar', 'rows');
        }

        // view と edit の中のメインデータ変数に $theOne を使用
        if ($view->get('singularName')) {
            $view->set('singularName', 'theOne');
        }
        if ($view->get('singularVar')) {
            $view->set('singularVar', 'theOne');
        }
 
特定の生成されたファイルへの ``Bake.beforeRender`` と ``Bake.afterRender``
イベントを指定することもあるでしょう。例えば、
**Controller/controller.twig** ファイルから生成する際、 UsersController
に特定のアクションを追加したい場合、以下のイベントを使用することができます。 ::

    <?php
    use Cake\Event\EventInterface;
    use Cake\Event\EventManager;
    use Cake\Utility\Hash;

    // in src/Application::bootstrapCli()

    EventManager::instance()->on(
        'Bake.beforeRender.Controller.controller',
        function (EventInterface $event) {
            $view = $event->getSubject();
            if ($view->get('name') === 'Users') {
                // Users コントローラーに login と logout を追加
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

特定の bake テンプレートのためのイベントリスナーを指定することによって、
bake 関連のイベント・ロジックを簡素化し、テストするのが容易であるコールバックを
提供することができます。

Bake テンプレート構文
=====================

Bake テンプレートファイルは、 `Twig <https://twig.symfony.com/doc/2.x/>`__
テンプレート構文を使用します。

だから、例えば、以下のようにコマンドを bake した場合:

.. code-block:: bash

    bin/cake bake command Foo

(**vendor/cakephp/bake/templates/bake/Command/command.twig**) を使用した
テンプレートは、以下のようになります。 ::

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
        * Hook method for defining this command's option parser.
        *
        * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
        * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
        * @return \Cake\Console\ConsoleOptionParser The built parser.
        */
        public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
        {
            $parser = parent::buildOptionParser($parser);

            return $parser;
        }

        /**
        * Implement this method with your command's logic.
        *
        * @param \Cake\Console\Arguments $args The command arguments.
        * @param \Cake\Console\ConsoleIo $io The console io
        * @return int|null|void The exit code or null for success
        */
        public function execute(Arguments $args, ConsoleIo $io)
        {
        }
    }

そして、 bake で得られたクラス (**src/Command/FooCommand.php**) は、
このようになります。 ::

    <?php
    declare(strict_types=1);

    namespace App\Command;

    use Cake\Command\Command;
    use Cake\Console\Arguments;
    use Cake\Console\ConsoleIo;
    use Cake\Console\ConsoleOptionParser;

    /**
    * Foo command.
    */
    class FooCommand extends Command
    {
        /**
        * Hook method for defining this command's option parser.
        *
        * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
        * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
        * @return \Cake\Console\ConsoleOptionParser The built parser.
        */
        public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
        {
            $parser = parent::buildOptionParser($parser);

            return $parser;
        }

        /**
        * Implement this method with your command's logic.
        *
        * @param \Cake\Console\Arguments $args The command arguments.
        * @param \Cake\Console\ConsoleIo $io The console io
        * @return int|null|void The exit code or null for success
        */
        public function execute(Arguments $args, ConsoleIo $io)
        {
        }
    }

.. _creating-a-bake-theme:

Bake テーマの作成
=================

"bake" コマンドによって生成された出力を変更したい場合、bake が使用するテンプレートの
一部または全部を置き換えることができる、独自の bake の「テーマ」を作成することができます。
これを行うための最善の方法は、次のとおりです。

#. 新しいプラグインを bake します。プラグインの名前は bake の「テーマ」名になります。
   例 ``bin/cake bake plugin custom_bake``
#. 新しいディレクトリー **plugins/CustomBake/templates/bake/** を作成します。
#. **vendor/cakephp/bake/templates/bake** から上書きしたい
   テンプレートをあなたのプラグインの中の適切なファイルにコピーしてください。
#. bake を実行するときに、必要であれば、 bake のテーマを指定するための ``--theme CustomBake``
   オプションを使用してください。各呼び出しでこのオプションを指定しなくても済むように、
   カスタムテーマをデフォルトテーマとして使用するように設定することもできます。 ::

        <?php
        // src/Application::bootstrapCli()の中の'Bake'プラグインを読み込む前に
        Configure::write('Bake.theme', 'MyTheme');

アプリケーション Bake テンプレート
===============================

幾つかのbakeテンプレートのカスタマイズが必要か、もしくはアプリケーション依存のテンプレートを使う必要がある場合、アプリケーションテンプレートを上書きするテンプレートを含めることができます。この上書きは他のプラグインテンプレートの上書きと同様に機能します。

#. 新しいディレクトリー **/templates/plugin/Bake/** を作成します。
#. **vendor/cakephp/bake/templates/bake/** から上書きしたいテンプレートを
   あなたのアプリケーションの中の適切なファイルにコピーします。

アプリケーションテンプレートの使用には``--theme`` オプションを使う必要はありません。

Bake コマンドオプションの新規作成
=================================

あなたのアプリケーションやプラグインで、新しい bake コマンドのオプションを追加したり、
CakePHP が提供するオプションを上書きすることが可能です。
``Bake\Command\BakeCommand`` を継承することで、bake は、あなたの新しいタスクを見つけて
bake の一部としてそれを含めます。

例として、任意の foo クラスを作成するタスクを作ります。
まず、 **src/Command/Bake/FooCommand.php** コマンドファイルを作成します。
私たちのコマンドが単純になるように、 ``SimpleBakeCommand`` を継承します。
``SimpleBakeCommand`` は抽象クラスで、どのタスクが呼ばれるか、どこにファイルを生成するか、
どのテンプレートを使用するかを bake に伝える３つのメソッドを定義することが必要です。
FooCommand.php ファイルは次のようになります。 ::

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

このファイルが作成されたら、コードを生成する際に bake 使用することができるテンプレートを
作成する必要があります。 **templates/bake/foo_template.twig** を作成してください。
このファイルに、以下の内容を追加します。 ::

    <?php
    namespace {{ namespace }}\FooPath;

    /**
     * {{ name }} fooOut
     */
    class {{ name }}FooOut
    {
        // Add code.
    }

これで、``bin/cake bake`` の出力に新しいコマンドが表示されるはずです。
``bin/cake bake foo Example`` を実行して、新しいタスクを実行することができます。
これは、使用するアプリケーションの **src/FooPath/ExampleFooOut.php** で
新しい ``ExampleFooOut`` クラスを生成します。

また、 ``ExampleFooOut`` クラスのテストファイルを作成するために ``bake`` を呼びたい場合は、
カスタムコマンド名のクラスサフィックスと名前空間を登録するために `FooCommand`` クラスの
``bakeTest()`` メソッドをオーバーライドする必要があります。 ::

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

* **class suffix** は ``bake`` 呼び出しで与えられた名前に追加します。前の例では、
  ``ExampleFooTest.php`` ファイルを作成します。
* **class type** は、（あなたが bake するアプリやプラグインに関連する）
  あなたのファイルを導くために使用されるサブ名前空間です。
  前の例では、名前空間 ``App\Test\TestCase\Foo`` でテストを作成します。

BakeView クラスの設定
==============================

bake コマンドは ``BakeView`` クラスをテンプレートをレンダリングするために使います。 You can
access the instance by listening to the ``Bake.initialize`` イベントを監視するためにこのインスタンスにアクセスできます。 例えば、以下の様にして独自のヘルパーを読み込みbakeテンプレートで使用できます::

    <?php
    \Cake\Event\EventManager::instance()->on(
        'Bake.initialize',
        function ($event, $view) {
            $view->loadHelper('Foo');
        }
    );

.. meta::
    :title lang=ja: Bake の拡張
    :keywords lang=ja: command line interface,development,bake view, bake template syntax,twig,erb tags,percent tags
