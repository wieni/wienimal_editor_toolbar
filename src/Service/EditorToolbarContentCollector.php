<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\EckEntityContentSource;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\NodeContentSource;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\TaxonomyTermContentSource;

class EditorToolbarContentCollector
{
    /** @var EckEntityContentSource $eckContentSource */
    private $eckContentSource;
    /** @var NodeContentSource $nodeContentSource */
    private $nodeContentSource;
    /** @var TaxonomyTermContentSource $taxonomyTermContentSource */
    private $taxonomyTermContentSource;
    /** @var ConfigFactory $configFactory */
    private $configFactory;
    /** @var ImmutableConfig $config */
    private $config;

    /**
     * EditorToolbarContentCollector constructor.
     * @param NodeContentSource $nodeContentSource
     * @param TaxonomyTermContentSource $taxonomyTermContentSource
     * @param EckEntityContentSource $eckContentSource
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        NodeContentSource $nodeContentSource,
        TaxonomyTermContentSource $taxonomyTermContentSource,
        EckEntityContentSource $eckContentSource,
        ConfigFactory $configFactory
    ) {
        $this->nodeContentSource = $nodeContentSource;
        $this->taxonomyTermContentSource = $taxonomyTermContentSource;
        $this->eckContentSource = $eckContentSource;
        $this->configFactory = $configFactory;

        $this->config = $this->configFactory->get('wienimal_editor_toolbar.settings');
    }

    /**
     * @param array $basePluginDefinition
     * @return array
     */
    public function getOverviewMenu(array $basePluginDefinition) {
        if (!$this->config->get('show_combined_content_overview')) {
            return [];
        }

        $output = [];
        $sources = [
            $this->eckContentSource,
            $this->nodeContentSource,
            $this->taxonomyTermContentSource
        ];

        foreach ($sources as $source) {
            $content = $source->getContent(
                $basePluginDefinition,
                $this->getContentConfig()[$source->getKey()]
            );

            foreach ($content as $item) {
                $customRoute = $this->getCustomOverviewRoute($item['id']);

                if ($customRoute) {
                    $item['route_name'] = $customRoute;
                } else {
                    $item['route_name'] = $source->getOverviewRoute($item);
                    $item['route_parameters'] = $source->getOverviewRouteParameters($item);
                }

                $output[$item['id']] = $item;
            }
        }

        return $output;
    }

    /**
     * @param array $basePluginDefinition
     * @return array
     */
    public function getCreateMenu(array $basePluginDefinition) {
        if (!$this->config->get('show_combined_add_content')) {
            return [];
        }

        $output = [];
        $sources = [
            $this->eckContentSource,
            $this->nodeContentSource,
            $this->taxonomyTermContentSource
        ];

        foreach ($sources as $source) {
            $content = $source->getContent(
                $basePluginDefinition,
                $this->getContentConfig()[$source->getKey()]
            );

            foreach ($content as $item) {
                $customRoute = $this->getCustomCreateRoute($item['id']);

                if ($customRoute) {
                    $item['route_name'] = $customRoute;
                } else {
                    $item['route_name'] = $source->getCreateRoute($item);
                    $item['route_parameters'] = $source->getCreateRouteParameters($item);
                }

                $output[$item['id']] = $item;
            }
        }

        return $output;
    }

    private function getContentConfig() {
        return $this->config->get('content') ?? [];
    }

    private function getCustomOverviewRoutes() {
        return $this->config->get('custom_routes.overview') ?? [];
    }

    private function getCustomOverviewRoute(string $toFind) {
        return $this->getCustomRoute($this->getCustomOverviewRoutes(), $toFind);
    }

    private function getCustomCreateRoutes() {
        return $this->config->get('custom_routes.create') ?? [];
    }

    private function getCustomCreateRoute(string $toFind) {
        return $this->getCustomRoute($this->getCustomCreateRoutes(), $toFind);
    }

    private function getCustomRoute(array $routes, string $toFind) {
        $found = false;

        array_walk_recursive(
            $routes,
            function ($value, $key) use ($toFind, &$found) {
                if ($key === $toFind) {
                    $found = $value;
                }
            }
        );

        return $found;
    }
}
