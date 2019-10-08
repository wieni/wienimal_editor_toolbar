<?php

namespace Drupal\wienimal_editor_toolbar\Menu;

use Drupal\Core\Menu\MenuLinkTree;

class EditorToolbarMenuLinkTree extends MenuLinkTree
{
    public function build(array $tree)
    {
        $build = parent::build($tree);
        $build['#theme'] = 'wienimal_editor_toolbar_menu';

        return $build;
    }
}
