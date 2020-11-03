Editor Toolbar
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wienimal_editor_toolbar/v/stable)](https://packagist.org/packages/wieni/wienimal)
[![Total Downloads](https://poser.pugx.org/wieni/wienimal_editor_toolbar/downloads)](https://packagist.org/packages/wieni/wienimal)
[![License](https://poser.pugx.org/wieni/wienimal_editor_toolbar/license)](https://packagist.org/packages/wieni/wienimal)

> A toolbar built for editors, not developers.

## Why?
- The structure of the default admin menu is a bit too complex for the limited things editors are permitted to do
- Drupal has some long-standing admin language related issues that are fixed in this module, until an official solution 
exists ([#2313309](https://www.drupal.org/project/drupal/issues/2313309), 
[#3038717](https://www.drupal.org/project/drupal/issues/3038717))  

## Installation

This package requires PHP 7.1 and Drupal 8.8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wienimal_editor_toolbar
```

## How does it work?
Only users with the `access toolbar` and `access editor toolbar` permissions will be able to view the 
 editor toolbar. The editor toolbar is disabled by default for user 1.

### Customizing the default administration menu
TODO

### Creating a custom administration menu for editors
TODO

### Enabling the improved content add/overview menu items
TODO

### Showing version information in the toolbar
TODO

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
