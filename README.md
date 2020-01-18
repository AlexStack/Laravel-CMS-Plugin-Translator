# Translate content online by Baidu AI or Google AI

-   This is an Amila Laravel CMS Plugin
-   Translate content online

## Install it via the backend

-   Go to the CMS settings page -> Plugin -> search for remote image
-   Find alexstack/laravel-cms-plugin-translator
-   Click the Install button

## What the plugin do for us?

-   Translate content online
-   Add the translate result to the page content

## Install it via command line manually

```php
composer require alexstack/laravel-cms-plugin-translator

php artisan migrate --path=./vendor/alexstack/laravel-cms-plugin-translator/src/database/migrations

php artisan vendor:publish --force --tag=translator-views

php artisan laravelcms --action=clear

```

## How to use it?

-   It's enabled after install by default. You can see a Translator tab when you edit a page.
-   You don't need to do anything after install

## How to change the settings?

-   You can change the settings by edit plugin.page-tab-translator

```json

```

## Improve this plugin & documents

-   You are very welcome to improve this plugin and how to use documents

## License

-   This Amila Laravel CMS plugin is an open-source software licensed under the MIT license.
