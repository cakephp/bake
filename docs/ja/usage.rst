Bake でコード生成
##################

cake コンソールは、 PHP CLI (command line interface) で実行します。
もしスクリプトの実行に問題があるなら、以下を満たしてください。

#. PHP CLI がインストールされているか適切なモジュールが有効か確認してください (例：MySQL, intl)。
#. データベースのホストが 'localhost' で問題があるなら、代わりに '127.0.0.1' を使って下さい。
   PHP CLI でこの問題がおこる可能性があります。
#. 使っているコンピューターの設定に応じて、 ``bin/cake bake`` で使用する cake bash スクリプトの
   実行権限を設定する必要があります。

bake を実行する前にデータベースとの接続を確認しましょう。

``bin/cake bake --help`` を実行すると可能なbakeコマンドを表示できます。
(Windows システムの場合、 ``bin\cake bake --help`` を使います。)::

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

Bake モデル
===========

モデルは、既存のデータベーステーブルから一般的に生成（bake）されます。
規約が適用されるため、外部キー ``thing_id`` とテーブル ``things`` の主キー ``id`` に基づいてリレーションが検出されます。

規約から外れたリレーションの場合、Bake がリレーションを検出するために、制約/外部キー定義でリレーションを使用できます。例::

    ->addForeignKey('billing_country_id', 'countries') // defaults to `id`
    ->addForeignKey('shipping_country_id', 'countries', 'cid')

Bake テーマ
=====================

テーマオプションは全 bake コマンドで共通です。また、bakeする際のbake テンプレートファイルを変更することができます。
テーマを作るには、 :ref:`Bake テーマ作成ドキュメント <creating-a-bake-theme>` をご覧ください。

.. meta::
    :title lang=ja: Code Generation with Bake
    :keywords lang=ja: command line interface,functional application,database,database configuration,bash script,basic ingredients,project,model,path path,code generation,scaffolding,windows users,configuration file,few minutes,config,view,models,running,mysql
