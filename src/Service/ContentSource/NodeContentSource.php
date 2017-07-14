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
                $type = $nodeType->get('type');
                $id = sprintf('node-%s', $type);
                return [
                        'id' => $id,
                        'type' => $type,
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
            'type' => $menuItem['type'],
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
            'node_type' => $menuItem['type'],
        ];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'node';
    }

    /**
     * @param array $info
     * @return string
     */
    public function buildId(array $info)
    {
        if (isset($info['subType'])) {
            return sprintf(
                'node-%s_%s',
                $info['type'],
                $info['subType']
            );
        }

        return sprintf(
            'node-%s',
            $info['type']
        );
    }
}
