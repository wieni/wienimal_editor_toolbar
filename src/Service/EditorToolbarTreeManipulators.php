<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Menu\InaccessibleMenuLink;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\StaticMenuLinkOverrides;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Plugin\Derivative\ViewsMenuLink;

class EditorToolbarTreeManipulators
{
    /** @var ConfigFactory */
    private $configFactory;

    /**
     * CleanToolbarTreeManipulators constructor.
     * @param \Drupal\Core\Config\ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Remove certain unneeded menu items for editors
     * @param array $tree
     * @return array
     */
    public function removeMenuItems(array $tree)
    {
        $items = $this->getMenuItemsToRemove();

        foreach ($items as $item) {
            if (!is_array($item)) {
                unset($tree[$item]);
            } else if (count($item) === 1) {
                unset($tree[$item[0]]);
            } else if (count($item) === 2) {
                $subTree = $tree[$item[0]]->subtree;
                unset($subTree[$item[1]]);
                $tree[$item[0]]->subtree = $subTree;
            }
        }

        return $tree;
    }

    /**
     * Remove menu item and move subtree items to root
     * @param array $tree
     * @return array
     */
    public function expandMenuItem(array $tree)
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

                $link = $contentMenu[$menuItem]->link;
                $link = $this->updateMenuLinkPluginDefinition($link, [
                    'parent' => '',
                ]);

                $tree[$menuItem] = $contentMenu[$menuItem];
            }

            unset($tree[$item]);
        }

        return $tree;
    }

    /**
     * Add icons to the content types under the 'Add content' menu
     * @param array $tree
     * @return array
     */
    public function addContentTypeIcons(array $tree)
    {
        $nestedMenuItems = [
            'node.add_page' => '/node\.add\.(.+)/',
            'wienimal_editor_toolbar.content_overview.derivatives' => '/wienimal_editor_toolbar\.content_overview\.derivatives\:(.+)/',
            'wienimal_editor_toolbar.content_add.derivatives' => '/wienimal_editor_toolbar\.content_add\.derivatives\:(.+)/',
        ];

        menu_walk_recursive(
            $tree,
            function (&$value) use ($nestedMenuItems) {
                foreach ($nestedMenuItems as $item => $pattern) {
                    if (!$value instanceof MenuLinkTreeElement) {
                        continue;
                    }

                    if (preg_match($pattern, $value->link->getPluginId(), $matches)) {
                        $name = Html::cleanCssIdentifier($matches[1]);

                        $value->options = [
                            'attributes' => [
                                'class' => [
                                    'icon',
                                    'icon--s',
                                    'icon--' . $name,
                                ],
                            ],
                        ];
                    }
                }
            }
        );

        return $tree;
    }

    /**
     * Make the 'Add content' menu item not clickable
     * @param array $tree
     * @return array
     */
    public function makeMenuItemsNotClickable(array $tree)
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
     * Make changes to the plugin definition of a menu link
     * @param \Drupal\Core\Menu\MenuLinkDefault|\Drupal\views\Plugin\Menu\ViewsMenuLink $link
     * @param array $newDefinition
     * @return \Drupal\Core\Menu\MenuLinkDefault|\Drupal\views\Plugin\Menu\ViewsMenuLink
     */
    private function updateMenuLinkPluginDefinition($link, array $newDefinition)
    {
        if ($link instanceof ViewsMenuLink) {
            $link->updateLink($newDefinition, false);
            return $link;
        } elseif ($link instanceof MenuLinkDefault) {
            return new MenuLinkDefault(
                [],
                $link->getPluginId(),
                array_merge($link->getPluginDefinition(), $newDefinition),
                new StaticMenuLinkOverrides($this->configFactory)
            );
        }
    }

    /**
     * @return array
     */
    private function getMenuItemsToExpand() {
        if (function_exists('wienimal_editor_toolbar_expand_menu_items')) {
            return wienimal_editor_toolbar_expand_menu_items();
        }

        return [];
    }

    /**
     * @return array
     */
    private function getMenuItemsToRemove() {
        if (function_exists('wienimal_editor_toolbar_remove_menu_items')) {
            return wienimal_editor_toolbar_remove_menu_items();
        }

        return [];
    }

    /**
     * @return array
     */
    private function getMenuItemsToMakeUnClickable() {
        if (function_exists('wienimal_editor_toolbar_make_menu_items_unclickable')) {
            return wienimal_editor_toolbar_make_menu_items_unclickable();
        }

        return [];
    }
}
