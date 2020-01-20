# Translate content online by Baidu AI or Google AI

-   This is an Amila Laravel CMS Plugin
-   Automatically translate content online via Baidu FanYi API or Google Translate API or Google Translate Free Version

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

*   Note: Google translate API not support before 1.0 version.

## How to change the settings?

-   You can change the settings by edit plugin.page-tab-translator
-   Change the api_provider to baidu or google
-   Set up app_id and app_key from the baidu or google translate api page. eg.

```json
{
    "blade_file": "translator",
    "tab_name": "<i class='fas fa-language mr-1'></i>__(translator)",
    "php_class": "App\\LaravelCms\\Plugins\\Translator\\Controllers\\TranslatorController",
    "api_provider": "baidu",
    "app_id": "201911000357",
    "app_key": "cX6xUKysHBXaH"
}
```

## Where can I get the app_id & app_key

-   Option 1: Provider Baidu, From https://api.fanyi.baidu.com/, FREE, only 1 translate allowed per second

```json
{
    "api_provider": "Baidu",
    "app_id": "201911000357",
    "app_key": "cX6xUKysHBXaH"
}
```

-   Option 2: Provider Google, Advanced version. NOT SUPPORT YET. From https://cloud.google.com/translate/docs/, Need to pay for what you use, price is \$20 per million characters in 2020.

```json
{
    "api_provider": "google",
    "app_id": "AizSxUKysHBsds2",
    "app_key": "AizSxUKysHBsds2"
}
```

## Use the free Google translate

-   Set provider to Google_Free
-   Option 1: Set both app_id & app_key to google_free_001, then our CMS will use package https://github.com/dejurin/php-google-translate-for-free to do the translate. (It's the default setting after install)

```json
{
    "api_provider": "Google_Free",
    "app_id": "google_free_001",
    "app_key": "google_free_001"
}
```

-   Option 2: Set both app_id & app_key to google_free_002, then our CMS will use the package https://github.com/Stichoza/google-translate-php to do the translate, YOU NEED INSTALL THE PACKAGE VIA COMMAND LINE FIRST: composer require stichoza/google-translate-php

```json
{
    "api_provider": "google_free",
    "app_id": "google_free_002",
    "app_key": "google_free_002"
}
```

-   Limitations of free Google translate: (1) Google only allows a maximum of 5000 characters to be translated at once. If you want to translate a longer text, you can split it to shorter parts, and translate them one-by-one. (2) 503,429,403 error it is most likely that Google has banned your external IP address and/or requires you to solve a CAPTCHA. This is not a bug in this package. Google has become stricter, and it seems like they keep lowering the number of allowed requests per IP per a certain amount of time. You may need to wait 12-36 hours. [More details](https://github.com/Stichoza/google-translate-php#known-limitations)

## Improve this plugin & documents

-   You are very welcome to improve this plugin and how to use documents

## License

-   This Amila Laravel CMS plugin is an open-source software licensed under the MIT license.
