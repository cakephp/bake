# Bake Console

CakePHP's bake console is another effort to get you up and running in CakePHP fast.
The bake console can create any of CakePHP's basic ingredients: models,
behaviors, views, helpers, controllers, components, test cases, fixtures, and plugins.
Bake can create far more than skeleton classes and is a natural next step once an application has been scaffolded.

## Installation

Before trying to use or extend Bake, make sure it is installed in your application.
Bake is provided as a plugin that you can install with Composer:

```bash
composer require --dev cakephp/bake:"^3.0"
```

The above installs Bake as a development dependency, so it will not be installed during production deployments.

When using Twig templates, make sure you are loading the `Cake/TwigView` plugin with its bootstrap.
You can also omit it completely, which makes the Bake plugin load it on demand.

## Documentation Map

- [Code Generation with Bake](/usage) covers running the console, listing commands, baking models and enums, and changing bake themes.
- [Extending Bake](/development) covers events, Twig templates, custom themes, application template overrides, and creating custom bake commands.
