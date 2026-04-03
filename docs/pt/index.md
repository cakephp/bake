# Console Bake

O console do Bake é uma ferramenta para você sair produzindo em CakePHP rapidamente.
Ele pode criar os itens básicos do CakePHP, como models, behaviors, views, helpers,
controllers, components, test cases, fixtures e plugins.
Bake pode ir além de classes esqueleto e gerar uma base funcional em poucos minutos.

## Instalação

Antes de usar ou estender o Bake, tenha certeza de que ele está instalado em sua aplicação.
Bake é distribuído como um plugin que você pode instalar com Composer:

```bash
composer require --dev cakephp/bake:~2.0
```

Isso instala o Bake como dependência de desenvolvimento, portanto ele não será instalado em produção.

Ao usar templates Twig, verifique se você está carregando o plugin `Cake/TwigView` com seu bootstrap.
Você também pode omiti-lo completamente, o que faz com que o plugin Bake carregue esse plugin sob demanda.

## Mapa da documentação

- [Geração de código com Bake](/pt/usage) cobre a execução do CLI, os comandos disponíveis e os temas do Bake.
- [Estendendo o Bake](/pt/development) cobre eventos, templates, temas e novos comandos de Bake.
