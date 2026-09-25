# Console Bake

O console do Bake do CakePHP é mais uma forma de você começar a trabalhar em CakePHP rapidamente.
O console do Bake pode criar qualquer um dos ingredientes básicos do CakePHP: models,
behaviors, views, helpers, controllers, components, test cases, fixtures e plugins.
O Bake pode criar muito mais do que classes esqueleto e é um próximo passo natural depois que uma aplicação passou por scaffolding.

## Instalação

Antes de usar ou estender o Bake, tenha certeza de que ele está instalado em sua aplicação.
O Bake é distribuído como um plugin que você pode instalar com Composer:

```bash
composer require --dev cakephp/bake:"^4.0"
```

O comando acima instala o Bake como dependência de desenvolvimento, portanto ele não será instalado em implantações de produção.

Ao usar templates Twig, verifique se você está carregando o plugin `Cake/TwigView` com seu bootstrap.
Você também pode omiti-lo completamente, o que faz com que o plugin Bake o carregue sob demanda.

## Mapa da documentação

- [Geração de código com Bake](/pt/usage) cobre a execução do console, a listagem de comandos, a geração de models e enums e a troca de temas do bake.
- [Estendendo o Bake](/pt/development) cobre eventos, templates Twig, temas personalizados, sobrescrita de templates pela aplicação e a criação de novos comandos de bake.
