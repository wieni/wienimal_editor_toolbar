<?php

/**
 * Alter the menu tree manipulators used by the editor toolbar.
 *
 * @see \Drupal\wienimal_editor_toolbar\Service\EditorToolbarMenuBuilder::buildMenu()
 * @param array $manipulators
 * @param string $menuName
 */
function hook_wienimal_editor_toolbar_manipulators_alter(array &$manipulators, string $menuName): void
{
    $manipulators[] = [
        'callable' => 'my_module.my_service:hideMenuItems',
    ];
}
