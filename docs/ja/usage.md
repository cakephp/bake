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

## Bake モデル

モデルは既存のデータベーステーブルから汎用的に生成されます。
CakePHP の規約が適用されるため、外部キー `thing_id` とテーブル `things` の主キー `id` に基づいてリレーションが検出されます。

規約から外れたリレーションの場合は、制約や外部キー定義の参照を使って Bake にリレーションを検出させることができます。

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Bake Enum

Bake を使うと、モデルで使用する [backed enum](https://www.php.net/manual/en/language.enumerations.backed.php) を生成できます。
Enum は `src/Model/Enum/` に配置され、`EnumLabelInterface` を実装して `EnumLabelTrait` を使用します。このトレイトが提供する `label()` メソッドにより、人間が読みやすい表示ができます。

文字列をバッキングにした enum を Bake するには次のようにします:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

これにより `src/Model/Enum/ArticleStatus.php` が生成されます:

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

int をバッキングにした enum の場合は `-i` オプションを使い、コロン区切りで値を指定します:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

これにより int バッキングの enum が生成されます:

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

Enum はプラグインの中にも Bake できます:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Bake テーマ

テーマオプションはすべての bake コマンドで共通で、Bake 時に使用するテンプレートファイルを変更できます。
テーマを作るには、[Bake テーマの作成](/ja/development#bake-テーマの作成) を参照してください。
