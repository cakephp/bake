# Console Bake

La console Bake de CakePHP permet de lancer rapidement une application CakePHP.
Elle peut créer les éléments de base de CakePHP, comme les models, behaviors, views,
helpers, controllers, components, cas de tests, fixtures et plugins.
Bake peut aller bien au-delà des classes squelettes et constitue une étape naturelle après un premier prototypage.

## Installation

Avant d'utiliser ou d'étendre Bake, assurez-vous qu'il est installé dans votre application.
Bake est disponible sous forme de plugin que vous pouvez installer avec Composer :

```bash
composer require --dev cakephp/bake:"^2.0"
```

Cela installe Bake comme dépendance de développement, et il ne sera donc pas déployé en production.

Quand vous utilisez les templates Twig, vérifiez que vous chargez le plugin `Cake/TwigView` avec son bootstrap.
Vous pouvez aussi l'omettre complètement, ce qui fera charger ce plugin à la demande par Bake.

## Plan de la documentation

- [Génération de code avec Bake](/fr/usage) couvre l'exécution du CLI, les commandes disponibles et les thèmes Bake.
- [Étendre Bake](/fr/development) couvre les events, les templates Twig, les thèmes et les commandes Bake personnalisées.
