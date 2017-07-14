<?php

namespace Drupal\wienimal_editor_toolbar\Service\ContentSource;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eck\EckEntityTypeBundleInfo;

class EckEntityContentSource extends AbstractContentSource {

    /** @var EckEntityTypeBundleInfo $bundleInfo */
    private $bundleInfo;

    /**
     * EditorToolbarContentCollector constructor.
     * @param EckEntityTypeBundleInfo $bundleInfo
     */
    public function __construct(EckEntityTypeBundleInfo $bundleInfo)
    {
        $this->bundleInfo = $bundleInfo;
    }

    /**
     * @param array $basePluginDefinition
     * @param array|string $config
     * @return array
     */
    public function getContent(array $basePluginDefinition, $config)
    {
        $content = [];

        // Get ECK bundles
        $types = array_filter(
            $this->bundleInfo->getAllBundleInfo(),
            function ($key) {
                return !in_array($key, ['taxonomy_term', 'node']);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (is_array($config)) {
            foreach ($types as $entityType => &$bundles) {
                if (!isset($config[$entityType])) {
                    unset($types[$entityType]);
                    continue;
                }

                // Only bundles from config
                $bundles = array_filter(
                    $bundles,
                    function ($bundle) use ($config, $entityType) {
                        return in_array($bundle, $config[$entityType]);
                    },
                    ARRAY_FILTER_USE_KEY
                );

                // Map to menu item
                foreach ($bundles as $bundleName => $bundle) {
                    $id = sprintf('eck-%s-%s', $entityType, $bundleName);
                    array_push($content, [
                            'id' => $id,
                            'entity_type' => $entityType,
                            'title' => new TranslatableMarkup($bundle['label']),
                        ] + $basePluginDefinition);
                }
            }
        }

        return $content;
    }

    /**
     * @param array $menuItem
     * @return string
     */
    public function getOverviewRoute(array $menuItem)
    {
        return "eck.entity.{$menuItem['entity_type']}.list";
    }

    /**
     * @param array $menuItem
     * @return array
     */
    public function getOverviewRouteParameters(array $menuItem)
    {
        return [
            'eck_entity_type' => $menuItem['entity_type'],
        ];
    }

    /**
     * @param array $menuItem
     * @return string
     */
    public function getCreateRoute(array $menuItem)
    {
        return 'eck.entity.add';
    }

    /**
     * @param array $menuItem
     * @return array
     */
    public function getCreateRouteParameters(array $menuItem)
    {
        return [
            'eck_entity_type' => $menuItem['entity_type'],
            'eck_entity_bundle' => $menuItem['id'],
        ];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'eck';
    }
}
