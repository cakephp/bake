# Bake でコード生成

Bake コンソールは PHP CLI で実行します。
スクリプトの実行に問題がある場合は、次を確認してください。

1. PHP CLI がインストールされていて、必要なモジュールが有効になっていること。例: MySQL、`intl`。
2. データベースホストが `localhost` の場合は、代わりに `127.0.0.1` を試すこと。PHP CLI で問題になることがあります。
3. コンピューターの設定によっては、`bin/cake bake` で使用する Cake スクリプトに実行権限を付ける必要があること。

Bake を実行する前に、少なくとも 1 つのデータベース接続が設定されていることを確認してください。

`bin/cake bake --help` を実行すると、利用可能な Bake コマンドを表示できます。
Windows では `bin\cake bake --help` を使用します。

```bash
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
```

## Bake モデル

モデルは既存のデータベーステーブルから生成されます。
規約が適用されるため、外部キー `thing_id` とテーブル `things` の主キー `id` に基づいてリレーションが検出されます。

規約から外れたリレーションの場合は、制約や外部キー定義の参照を使って Bake にリレーションを検出させることができます。

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Bake テーマ

テーマオプションはすべての Bake コマンドで共通です。
Bake 時に使用するテンプレートファイルを変更できます。
テーマを作るには、[Bake テーマの作成](/ja/development#bake-テーマの作成) を参照してください。
