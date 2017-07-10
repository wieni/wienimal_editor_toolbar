<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverrides;

class CleanToolbarTreeManipulators
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
    public function removeUnneededMenuItems(array $tree)
    {
        $items = [
            ['admin_toolbar_tools.help'],
            ['system.admin_config'],
        ];

        foreach ($items as $item) {
            if (count($item) === 1) {
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
     * Make 'Add content' and 'Settings' their own root menu item
     * @param array $tree
     * @return array
     */
    public function seperateContentSubtree(array $tree)
    {
        return $this->removeAndSeperateSubtree('system.admin_content', $tree);
    }

    /**
     * Make 'Taxonomy' its own root menu item
     * @param array $tree
     * @return array
     */
    public function seperateStructureSubtree(array $tree)
    {
        return $this->removeAndSeperateSubtree('system.admin_structure', $tree);
    }

    /**
     * Remove menu item and move subtree items to root
     * @param string $id
     * @param array $tree
     * @return array
     */
    private function removeAndSeperateSubtree(string $id, array $tree)
    {
        if (isset($tree[$id])) {
            $contentMenu = $tree[$id]->subtree;

            foreach ($contentMenu as $item => $value) {
                if (!$contentMenu[$item]->link instanceof MenuLinkDefault) {
                    continue;
                }

                $link = $contentMenu[$item]->link;
                $link = $this->updateMenuLinkPluginDefinition($link, [
                    'parent' => '',
                ]);

                $tree[$item] = $contentMenu[$item];
            }

            unset($tree[$id]);
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
        if (!isset($tree['system.admin_content'])) {
            return $tree;
        }

        $contentMenu = $tree['system.admin_content']->subtree;
        $menuItems = [
            'node.add_page' => '/node\.add\.(.+)/',
            'wienimal_editor_toolbar.content_overview' => '/wienimal_editor_toolbar\.content_types\:(.+)/',
        ];

        foreach ($menuItems as $item => $pattern) {
            if (!isset($contentMenu[$item])) {
                continue;
            }

            $contentTypes = $contentMenu[$item]->subtree;

            /** @var \Drupal\Core\Menu\MenuLinkTreeElement $contentType */
            foreach ($contentTypes as &$contentType) {
                if (preg_match($pattern, $contentType->link->getPluginId(), $matches)) {
                    $contentType->options = [
                        'attributes' => [
                            'class' => [
                                'icon',
                                'icon--s',
                                'icon--' . $matches[1],
                            ],
                        ],
                    ];
                }
            }

            $contentMenu[$item]->subtree = $contentTypes;
        }

        $tree['system.admin_content']->subtree = $contentMenu;

        return $tree;
    }

    /**
     * Make the 'Add content' menu item not clickable
     * @param array $tree
     * @return array
     */
    public function makeAddContentNotClickable(array $tree)
    {
        if (
            !isset($tree['system.admin_content'])
            || !isset($tree['system.admin_content']->subtree['node.add_page'])
        ) {
            return $tree;
        }

        $link = &$tree['system.admin_content']->subtree['node.add_page']->link;
        if ($link instanceof MenuLinkDefault) {
            $link = $this->updateMenuLinkPluginDefinition($link, [
                'route_name' => '<nolink>',
                'parent' => '',
            ]);
        }

        return $tree;
    }

    /**
     * Make changes to the plugin definition of a menu link
     * @param \Drupal\Core\Menu\MenuLinkDefault $link
     * @param array $newDefinition
     * @return \Drupal\Core\Menu\MenuLinkDefault
     */
    private function updateMenuLinkPluginDefinition(MenuLinkDefault $link, array $newDefinition)
    {
        return new MenuLinkDefault(
            [],
            $link->getPluginId(),
            array_merge($link->getPluginDefinition(), $newDefinition),
            new StaticMenuLinkOverrides($this->configFactory)
        );
    }
}
