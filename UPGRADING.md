# Upgrade Guide

This document describes breaking changes and how to upgrade. For a complete list of changes including minor and patch releases, please refer to the [`CHANGELOG`](CHANGELOG.md).

## v4
All references to the `wienimal_editor_toolbar.admin_content` route should be replaced with the original content overview route, `system.admin_content`.

The `access administration menu` permission used in previous versions is removed. The editor is now never rendered for user 1.  

Since the default toolbar is now rendered, editors also need the `access toolbar` permission.

The `wienimal_editor_toolbar` service is removed. Version info can now be loaded with the 
 `wienimal_editor_toolbar.version_info`, there is no replacement for the other methods.

The `wienimal_editor_toolbar.menu.link_tree` service is removed without replacement.

The `version`, `versionDate` & `username` default variables are removed. Make sure no other code (eg. wienimal) is 
 using these variables.

If the `plugin.manager.menu.link` service is already overrided in custom code, make sure to remove the custom code or 
 to combine both.
