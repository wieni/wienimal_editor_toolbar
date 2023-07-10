# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.1.5] - 2023-07-10
### Fixed
- **BC** Use AND conjunction in Route Alter ([#22](https://github.com/wieni/wienimal_editor_toolbar/pull/22))
    - Editors now require `access content overview` _and_ `access editor toolbar` to visit the `/admin/content` route

## [4.1.4] - 2022-03-26
### Added
- Add support for the [Node Singles](https://www.drupal.org/project/node_singles) module

## [4.1.3] - 2021-12-16
### Fixed
- Fix the following notice appearing when using PHP 8.1:
```
Deprecated function: Return type of Drupal\wienimal_editor_toolbar\TranslatableEntityLabelMarkup::jsonSerialize() should either be compatible with JsonSerializable::jsonSerialize(): mixed, or the #[\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice
```

## [4.1.2] - 2021-08-30
### Fixed
- Update default config to new format

## [4.1.1] - 2021-08-25
### Fixed
- Fix warning when still using old config format

## [4.1.0] - 2021-08-24
### Added
- Support overriding content overview & content add routes separately
- Support hiding/showing content overview & content add routes separately ([#7](https://github.com/wieni/wienimal_editor_toolbar/issues/7))
- Support overriding a content menu link route without having to specify all bundles that have to be included
- Add Rector dev dependency

### Changed
- Change settings config structure to be more flexible
- Update dev dependencies
- Apply coding standard fixes

## [4.0.4] - 2021-08-23
### Fixed
- Fix missing variable

## [4.0.3] - 2021-08-23
### Changed
- Make language module dependency optional

### Fixed
- Add cache metadata to rendered toolbar

## [4.0.2] - 2020-11-03
### Changed
- Increase minimum core version to 8.8

## [4.0.1] - 2020-10-29
### Fixed
- Fix double content add/overview for non-editors
- Fix show_version_info setting not being considered

### Removed
- Remove show_logo setting

## [4.0.0] - 2020-10-23
### Added
- Add toolbar item with version info
- Add the possibility to provide custom editor menu items through `<module>.links.menu.editor.yml`.
- Add `wienimal_editor_toolbar.version_info` service
- Add changelog & upgrade guide

### Changed
- Modify existing toolbar instead of rendering a new one
- Move altering of /admin/content route from routing.yml to an event subscriber
- Require the access toolbar permission
- Add the `language` and `toolbar` modules as dependencies
- Update README & module info

### Removed
- Remove custom toolbar template and theming
- Remove `wienimal_editor_toolbar` service
- Remove `wienimal_editor_toolbar.menu.link_tree` service
- Remove `access administration menu` permission
