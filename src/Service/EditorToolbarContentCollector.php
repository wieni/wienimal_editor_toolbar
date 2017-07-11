<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\eck\EckEntityTypeBundleInfo;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

class EditorToolbarContentCollector {

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
     * @return array
     */
    public function getOverviewMenu(array $basePluginDefinition) {
        $config = $this->getConfig();
        $output = [];

        $content = array_merge(
            $this->getEckEntityTypes($basePluginDefinition, $config['eck']),
            $this->getNodeTypes($basePluginDefinition, $config['node']),
            $this->getTaxonomyTerms($basePluginDefinition, $config['taxonomy'])
        );

        foreach ($content as $item) {
            $output[$item['id']] = $item;
        }

        return $output;
    }

    /**
     * @param array $basePluginDefinition
     * @param $config
     * @return array
     */
    private function getNodeTypes(array $basePluginDefinition, $config) {
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
                        'route_name' => 'system.admin_content',
                        'route_parameters' => [
                            'type' => $nodeType->get('type'),
                        ],
                    ] + $basePluginDefinition;
            },
            $nodeTypes
        );
    }

    /**
     * @param array $basePluginDefinition
     * @param $config
     * @return array
     */
    private function getTaxonomyTerms(array $basePluginDefinition, $config) {
        $taxonomyTerms = Vocabulary::loadMultiple();

        if (is_array($config)) {
            $taxonomyTerms = array_filter(
                $taxonomyTerms,
                function ($taxonomyTerm) use ($config) {
                    return in_array($taxonomyTerm->get('type'), $config);
                }
            );
        }

        // Map to menu item
        return array_map(
            function ($taxonomyTerm) use ($basePluginDefinition) {
                return [
                        'id' => $taxonomyTerm->get('vid'),
                        'title' => new TranslatableMarkup($taxonomyTerm->get('name')),
                        'route_name' => 'entity.taxonomy_vocabulary.overview_form',
                        'route_parameters' => [
                            'taxonomy_vocabulary' => $taxonomyTerm->get('vid'),
                        ],
                    ] + $basePluginDefinition;
            },
            $taxonomyTerms
        );
    }

    /**
     * @param array $basePluginDefinition
     * @param $config
     * @return array
     */
    public function getEckEntityTypes(array $basePluginDefinition, $config) {
        $customRoutes = $this->getCustomRoutes();
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
                    array_push($content, [
                            'id' => $bundleName,
                            'title' => new TranslatableMarkup($bundle['label']),
                            'route_name' => $customRoutes['eck'][$entityType][$bundleName] ?? "eck.entity.{$entityType}.list",
                            'route_parameters' => [
                                'type' => $bundleName,
                            ],
                        ] + $basePluginDefinition);
                }
            }
        }

        return $content;
    }

    /**
     * @param $basePluginDefinition
     * @param $id
     * @param string $label
     * @param string $routeName
     * @param array $routeParameters
     * @return array
     */
    private function buildMenuItem($basePluginDefinition, $id, string $label, string $routeName, array $routeParameters = []) {
        return [
                'id' => $id,
                'title' => new TranslatableMarkup($label),
                'route_name' => $routeName,
                'route_parameters' => $routeParameters,
            ] + $basePluginDefinition;
    }

    /**
     * @return array
     */
    private function getConfig() {
        if (function_exists('wienimal_editor_toolbar_content')) {
            return wienimal_editor_toolbar_content();
        }

        return [];
    }

    /**
     * @return array
     */
    private function getCustomRoutes() {
        if (function_exists('wienimal_editor_toolbar_custom_routes')) {
            return wienimal_editor_toolbar_custom_routes();
        }

        return [];
    }
}
