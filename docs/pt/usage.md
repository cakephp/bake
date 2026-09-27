# Geração de Código com Bake

O console do Bake é executado usando o PHP CLI.
Se você tiver problemas para executar o script, assegure-se de que:

1. Você tem o PHP CLI instalado e com os módulos apropriados habilitados, como MySQL e `intl`.
2. Se o host do banco de dados for `localhost`, tente `127.0.0.1` em vez disso, pois `localhost` pode causar problemas no PHP CLI.
3. Dependendo de como o seu computador está configurado, pode ser necessário definir permissões de execução no script do Cake para chamá-lo usando `bin/cake bake`.

Antes de executar o Bake, você deve ter pelo menos uma conexão de banco de dados configurada.

Você pode obter a lista de comandos de bake disponíveis executando `bin/cake bake --help`.
No Windows, use `bin\cake bake --help`:

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

## Models com Bake

Os models são gerados a partir das tabelas existentes no banco de dados.
As convenções do CakePHP se aplicam, então o Bake detecta relações baseadas em chaves estrangeiras `thing_id` para tabelas `things` com suas chaves primárias `id`.

Para relações não convencionais, você pode usar referências nas definições de constraints ou de chaves estrangeiras para que o Bake detecte as relações:

```php
->addForeignKey('billing_country_id', 'countries') // defaults to `id`
->addForeignKey('shipping_country_id', 'countries', 'cid')
```

## Enums com Bake

Você pode usar o Bake para gerar [enums com valor (backed enums)](https://www.php.net/manual/en/language.enumerations.backed.php) para uso em seus models.
Os enums são colocados em `src/Model/Enum/`, implementam `EnumLabelInterface` e usam `EnumLabelTrait`, que fornece o método `label()` para exibição legível.

Para gerar um enum com suporte a string:

```bash
bin/cake bake enum ArticleStatus draft,published,archived
```

Isso gera `src/Model/Enum/ArticleStatus.php`:

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

Para enums com suporte a int, use a opção `-i` e forneça os valores separados por dois-pontos:

```bash
bin/cake bake enum Priority low:1,medium:2,high:3 -i
```

Isso gera um enum com suporte a int:

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

Você também pode gerar enums em plugins:

```bash
bin/cake bake enum MyPlugin.OrderStatus pending,processing,shipped
```

## Temas do Bake

A opção `theme` é comum a todos os comandos de bake e permite mudar os arquivos de template usados na geração.
Para criar seus próprios templates, veja [Criando um Tema Bake](/pt/development#criando-um-tema-bake).
