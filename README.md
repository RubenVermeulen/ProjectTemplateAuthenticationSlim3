# Slim 3 Project Template

This is a base template to start some of my projects. It contains a basic authentication functionality. I choose Slim 3 as my framework and Twig as template engine.

## Installation
### Install dependencies
Run following code. Make sure you have [composer](https://getcomposer.org/) installed.

```
composer install
```
### Create a configuration file
Create a new php file in the config foler, for example "production.php". Copy the content for "example.php" (also in config folder) into your newly created file. Add or change al necessary config. Finally in the root directory you find a "mode.php". Edit the contents matching the name of your configuration file (no extension, only the name). If you have a "production.php" file you fill in "production".
