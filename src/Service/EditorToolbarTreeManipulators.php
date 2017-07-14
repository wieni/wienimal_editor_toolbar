<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Menu\InaccessibleMenuLink;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\StaticMenuLinkOverrides;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\Derivative\ViewsMenuLink;

class EditorToolbarTreeManipulators
{
    /** @var ConfigFactory $configFactory */
    private $configFactory;
    /** @var ImmutableConfig $config */
    private $config;

    /**
     * CleanToolbarTreeManipulators constructor.
     * @param \Drupal\Core\Config\ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;

        $this->config = $this->configFactory->get('wienimal_editor_toolbar.settings');
    }

    /**
     * Remove certain unneeded menu items for editors
     * @param array $tree
     * @return array
     */
    public function removeMenuItems(array $tree)
    {
        foreach ($this->getMenuItemsToRemove() as $item) {
            $tree = $this->removeMenuItem($tree, $item);
        }

        return $tree;
    }

    /**
     * Remove a menu item from a menu tree
     * @param array $tree
     * @param $item
     * @return array
     */
    public function removeMenuItem(array $tree, $item)
    {
        menu_walk_recursive(
            $tree,
            function (&$value) use (&$tree, $item) {
                if ($value->link->getPluginId() === $item) {
                    unset($tree[$item]);
                }
            }
        );

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
            'node.add_page' => [
                'pattern' => '/node\.add\.(.+)/',
                'id' => function ($matches) {
                    return sprintf('node-%s', $matches[1]);
                }
            ],
            'entity.taxonomy_vocabulary.overview_form' => [
                'pattern' => '/entity\.taxonomy_vocabulary\.overview_form\.(.+)/',
                'id' => function ($matches) {
                    return sprintf('taxonomy-%s', $matches[1]);
                }
            ],
            'wienimal_editor_toolbar.content_overview.derivatives' => [
                'pattern' => '/wienimal_editor_toolbar\.content_overview\.derivatives\:(.+)/',
                'id' => function ($matches) {
                    return $matches[1];
                }
            ],
            'wienimal_editor_toolbar.content_add.derivatives' => [
                'pattern' => '/wienimal_editor_toolbar\.content_add\.derivatives\:(.+)/',
                'id' => function ($matches) {
                    return $matches[1];
                }
            ],
        ];

        menu_walk_recursive(
            $tree,
            function (&$value) use ($nestedMenuItems) {
                foreach ($nestedMenuItems as $item) {
                    if (!$value instanceof MenuLinkTreeElement) {
                        continue;
                    }

                    if (preg_match($item['pattern'], $value->link->getPluginId(), $matches)) {
                        $value->options = [
                            'attributes' => [
                                'class' => [
                                    'icon',
                                    'icon--s',
                                    'icon--' . $item['id']($matches),
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
     * Check if 'Content overview' and 'Add content' menu items have to be shown
     * @param array $tree
     * @return array
     */
    public function checkCustomMenuItemsAccess(array $tree)
    {
        if (!$this->getShowContentOverview()) {
            $tree = $this->removeMenuItem($tree, 'wienimal_editor_toolbar.content_overview');
        }

        if (!$this->getShowContentAdd()) {
            $tree = $this->removeMenuItem($tree, 'wienimal_editor_toolbar.content_add');
        }

        return $tree;
    }

    /**
     * Make changes to the plugin definition of a menu link
     * @param MenuLinkDefault|ViewsMenuLink $link
     * @param array $newDefinition
     * @return bool|MenuLinkDefault|ViewsMenuLink
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

        return false;
    }

    /**
     * @return boolean
     */
    private function getShowContentAdd() {
        return $this->config->get('show_combined_add_content') ?? false;
    }

    /**
     * @return boolean
     */
    private function getShowContentOverview() {
        return $this->config->get('show_combined_content_overview') ?? false;
    }

    /**
     * @return array
     */
    private function getMenuItemsToExpand() {
        return $this->config->get('menu_items.expand') ?? [];
    }

    /**
     * @return array
     */
    private function getMenuItemsToRemove() {
        return $this->config->get('menu_items.remove') ?? [];
    }

    /**
     * @return array
     */
    private function getMenuItemsToMakeUnClickable() {
        return $this->config->get('menu_items.unclickable') ?? [];
    }
}
