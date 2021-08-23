# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.3] - 2021-08-23
### Changed
- Make language module dependency optional

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
