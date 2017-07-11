<?php

namespace Drupal\wienimal_editor_toolbar\Service\ContentSource;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\NodeType;

class NodeContentSource extends AbstractContentSource {

    /**
     * @param array $basePluginDefinition
     * @param array|string $config
     * @return array
     */
    public function getContent(array $basePluginDefinition, $config)
    {
        $nodeTypes = NodeType::loadMultiple();

        if (is_array($config)) {
            $nodeTypes = array_filter(
                $nodeTypes,
                function ($nodeType) use ($config) {
                    return in_array($nodeType->get('type'), $config);
                }
            );
        }

        // Map to menu item
        return array_map(
            function ($nodeType) use ($basePluginDefinition) {
                return [
                        'id' => $nodeType->get('type'),
                        'title' => new TranslatableMarkup($nodeType->get('name')),
                    ] + $basePluginDefinition;
            },
            $nodeTypes
        );
    }

    /**
     * @param array $menuItem
     * @return string
     */
    public function getOverviewRoute(array $menuItem)
    {
        return 'system.admin_content';
    }

    /**
     * @param array $menuItem
     * @return array
     */
    public function getOverviewRouteParameters(array $menuItem)
    {
        return [
            'type' => $menuItem['id'],
        ];
    }

    /**
     * @param array $menuItem
     * @return string
     */
    public function getCreateRoute(array $menuItem)
    {
        return 'node.add';
    }

    /**
     * @param array $menuItem
     * @return array
     */
    public function getCreateRouteParameters(array $menuItem)
    {
        return [
            'node_type' => $menuItem['id'],
        ];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'node';
    }
}
