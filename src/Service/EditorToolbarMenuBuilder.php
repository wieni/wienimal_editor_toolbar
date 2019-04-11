<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Session\AccountProxyInterface;

class EditorToolbarMenuBuilder
{
    /** @var MenuLinkTreeInterface */
    protected $menuTree;
    /** @var AccountProxyInterface */
    protected $currentUser;
    /** @var ThemeHandlerInterface */
    protected $themeHandler;
    /** @var MenuActiveTrailInterface */
    protected $menuActiveTrail;

    public function __construct(
        MenuLinkTreeInterface $menuTree,
        AccountProxyInterface $currentUser,
        ThemeHandlerInterface $themeHandler,
        MenuActiveTrailInterface $menuActiveTrail
    ) {
        $this->menuTree = $menuTree;
        $this->currentUser = $currentUser;
        $this->themeHandler = $themeHandler;
        $this->menuActiveTrail = $menuActiveTrail;
    }

    public function buildPageTop(array &$page_top)
    {
        $page_top['wienimal_editor_toolbar'] = [
            '#theme' => 'wienimal_editor_toolbar',
            '#links' => $this->buildMenu(),
            '#access' => $this->showToolbar(),
            '#attached' => [
                'library' => [
                    'wienimal_editor_toolbar/editbar-top',
                ],
            ],
        ];
    }

    /**
     * Get the name of the menu
     */
    public function getMenuName(): string
    {
        return 'admin';
    }

    /**
     * Load, transform and return the menu
     */
    public function buildMenu(): array
    {
        $activeTrail = $this->menuActiveTrail->getActiveTrailIds('admin');

        // Build the typical default set of menu tree parameters.
        $parameters = new MenuTreeParameters();
        $parameters
            ->setRoot('system.admin')
            ->setActiveTrail($activeTrail)
            ->excludeRoot()
            ->setMaxDepth(4)
            ->onlyEnabledLinks();

        // Load the tree based on this set of parameters.
        $tree = $this->menuTree->load($this->getMenuName(), $parameters);

        // Transform the tree using the manipulators you want.
        $manipulators = [
            // Only show links that are accessible for the current user.
            ['callable' => 'menu.default_tree_manipulators:checkAccess'],
            // Check if 'Content overview' and 'Add content' menu items have to be shown
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:checkCustomMenuItemsAccess'],
            // Move certain menu items to the root of the toolbar
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:expandMenuItem'],
            // Remove certain unneeded menu items for editors
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:removeMenuItems'],
            // Make certain menu items not clickable
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:makeMenuItemsNotClickable'],
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
     */
    private function showToolbar(): bool
    {
        return $this->currentUser->hasPermission('access editor toolbar')
            && !$this->currentUser->hasPermission('access administration menu');
    }
}
