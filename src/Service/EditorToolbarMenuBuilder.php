<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Session\AccountProxyInterface;

class EditorToolbarMenuBuilder
{
    /** @var MenuLinkTreeInterface */
    private $menuTree;
    /** @var AccountProxyInterface */
    private $currentUser;

    /**
     * CleanToolbarMenuBuilder constructor.
     * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuTree
     * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
     */
    public function __construct(
        MenuLinkTreeInterface $menuTree,
        AccountProxyInterface $currentUser
    ) {
        $this->menuTree = $menuTree;
        $this->currentUser = $currentUser;
    }

    public function buildPageTop(array &$page_top)
    {
        $page_top['wienimal_editor_toolbar'] = [
            '#theme' => 'wienimal_editor_toolbar',
            '#links' => $this->buildMenu(),
            '#access' => $this->showToolbar(),
            /*
            '#cache' => [
                'keys' => ['editbar'],
                'contexts' => ['user.permissions'],
            ],
            */
            '#attached' => [
                'library' => [
                    'wienimal_editor_toolbar/wienimal_editor_toolbar.default',
                ],
            ],
        ];
    }

    /**
     * Get the name of the menu
     * @return string
     */
    public function getMenuName()
    {
        return 'admin';
    }

    /**
     * Load, transform and return the menu
     * @return array
     */
    public function buildMenu()
    {
        // Build the typical default set of menu tree parameters.
        $parameters = new MenuTreeParameters();
        $parameters
            ->setRoot('system.admin')
            ->excludeRoot()
            ->setMaxDepth(4)
            ->onlyEnabledLinks();

        // Load the tree based on this set of parameters.
        $tree = $this->menuTree->load($this->getMenuName(), $parameters);

        // Transform the tree using the manipulators you want.
        $manipulators = [
            // Only show links that are accessible for the current user.
            ['callable' => 'menu.default_tree_manipulators:checkAccess'],
            // Remove certain unneeded menu items for editors
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:removeUnneededMenuItems'],
            // Add icons to the content type menu items
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:addContentTypeIcons'],
            // Make the 'Add content' menu item not clickable
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:makeAddContentNotClickable'],
            // Make 'Add content' and 'Settings' their own root menu item
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:seperateContentSubtree'],
            // Make 'Taxonomy' its own root menu item
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:seperateStructureSubtree'],
            // Use the default sorting of menu links.
            ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
        ];
        $tree = $this->menuTree->transform($tree, $manipulators);

        // Finally, build a renderable array from the transformed tree.
        $menu = $this->menuTree->build($tree);

        return $menu;
    }

    /**
     * Check if the current user has permission to see the toolbar
     * @return bool
     */
    private function showToolbar()
    {
        return $this->currentUser->hasPermission('access clean toolbar')
            && !$this->currentUser->hasPermission('access administration menu');
    }
}
