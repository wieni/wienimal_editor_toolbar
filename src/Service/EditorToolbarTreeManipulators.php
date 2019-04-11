<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Menu\InaccessibleMenuLink;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\StaticMenuLinkOverrides;
use Drupal\views\Plugin\Derivative\ViewsMenuLink;

class EditorToolbarTreeManipulators
{
    /** @var ConfigFactory */
    protected $configFactory;
    /** @var ImmutableConfig */
    protected $config;

    public function __construct(
        ConfigFactory $configFactory
    ) {
        $this->configFactory = $configFactory;
        $this->config = $this->configFactory->get('wienimal_editor_toolbar.settings');
    }

    /**
     * Remove certain unneeded menu items for editors
     */
    public function removeMenuItems(array $tree): array
    {
        foreach ($this->getMenuItemsToRemove() as $item) {
            $tree = $this->removeMenuItem($tree, $item);
        }

        return $tree;
    }

    /**
     * Remove a menu item from a menu tree
     */
    public function removeMenuItem(array $tree, string $item): array
    {
        menu_walk_recursive(
            $tree,
            function (&$value) use ($item) {
                /** @var MenuLinkTreeElement $value */
                if ($value->link->getPluginId() === $item) {
                    $value->access = AccessResult::forbidden();
                }
            }
        );

        return $tree;
    }

    /**
     * Remove menu item and move subtree items to root
     */
    public function expandMenuItem(array $tree): array
    {
        $items = $this->getMenuItemsToExpand();

        foreach ($items as $item) {
            if (!isset($tree[$item])) {
                continue;
            }

            $contentMenu = $tree[$item]->subtree;

            foreach ($contentMenu as $menuItem => $value) {
                if ($contentMenu[$menuItem]->link instanceof InaccessibleMenuLink) {
                    continue;
                }

                $tree[$menuItem] = $contentMenu[$menuItem];
            }

            unset($tree[$item]);
        }

        return $tree;
    }

    /**
     * Make the 'Add content' menu item not clickable
     */
    public function makeMenuItemsNotClickable(array $tree): array
    {
        $items = $this->getMenuItemsToMakeUnClickable();

        menu_walk_recursive(
            $tree,
            function (&$value) use ($items) {
                if (
                    !$value->link instanceof MenuLinkDefault
                    || !in_array($value->link->getPluginId(), $items)
                ) {
                    return;
                }

                $value->link = $this->updateMenuLinkPluginDefinition($value->link, [
                    'route_name' => '<nolink>',
                    'parent' => '',
                ]);
            }
        );

        return $tree;
    }

    /**
     * Check if 'Content overview' and 'Add content' menu items have to be shown
     */
    public function checkCustomMenuItemsAccess(array $tree): array
    {
        if (!$this->getShowContentOverview()) {
            $tree = $this->removeMenuItem($tree, 'wienimal_editor_toolbar.content_overview');
        }

        if ($this->getShowContentAdd()) {
            $tree = $this->removeMenuItem($tree, 'admin_toolbar_tools.add_content');
        } else {
            $tree = $this->removeMenuItem($tree, 'wienimal_editor_toolbar.content_add');
        }

        return $tree;
    }

    /**
     * Make changes to the plugin definition of a menu link
     * @param MenuLinkDefault|ViewsMenuLink $link
     * @param array $newDefinition
     * @return MenuLinkDefault|ViewsMenuLink|false
     */
    protected function updateMenuLinkPluginDefinition($link, array $newDefinition)
    {
        if ($link instanceof ViewsMenuLink) {
            $link->updateLink($newDefinition, false);
            return $link;

        }

        if ($link instanceof MenuLinkInterface) {
            return new MenuLinkDefault(
                [],
                $link->getPluginId(),
                array_merge($link->getPluginDefinition(), $newDefinition),
                new StaticMenuLinkOverrides($this->configFactory)
            );
        }

        return false;
    }

    protected function getShowContentAdd(): bool
    {
        return $this->config->get('show_combined_add_content') ?? false;
    }

    protected function getShowContentOverview(): bool
    {
        return $this->config->get('show_combined_content_overview') ?? false;
    }

    protected function getMenuItemsToExpand(): array
    {
        return $this->config->get('menu_items.expand') ?? [];
    }

    protected function getMenuItemsToRemove(): array
    {
        return $this->config->get('menu_items.remove') ?? [];
    }

    protected function getMenuItemsToMakeUnClickable(): array
    {
        return $this->config->get('menu_items.unclickable') ?? [];
    }
}
