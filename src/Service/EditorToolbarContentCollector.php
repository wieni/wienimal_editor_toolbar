<?php

namespace Drupal\wienimal_editor_toolbar\Service;

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

    /**
     * EditorToolbarContentCollector constructor.
     * @param NodeContentSource $nodeContentSource
     * @param TaxonomyTermContentSource $taxonomyTermContentSource
     * @param EckEntityContentSource $eckContentSource
     */
    public function __construct(
        NodeContentSource $nodeContentSource,
        TaxonomyTermContentSource $taxonomyTermContentSource,
        EckEntityContentSource $eckContentSource
    ) {
        $this->nodeContentSource = $nodeContentSource;
        $this->taxonomyTermContentSource = $taxonomyTermContentSource;
        $this->eckContentSource = $eckContentSource;
    }

    /**
     * @param array $basePluginDefinition
     * @return array
     */
    public function getOverviewMenu(array $basePluginDefinition) {
        $output = [];

        $sources = [
            $this->eckContentSource,
            $this->nodeContentSource,
            $this->taxonomyTermContentSource
        ];

        foreach ($sources as $source) {
            $content = $source->getContent(
                $basePluginDefinition,
                $this->getConfig()[$source->getKey()]
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
        $output = [];

        $sources = [
            $this->eckContentSource,
            $this->nodeContentSource,
            $this->taxonomyTermContentSource
        ];

        foreach ($sources as $source) {
            $content = $source->getContent(
                $basePluginDefinition,
                $this->getConfig()[$source->getKey()]
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

    /**
     * @return array
     */
    private function getConfig() {
        if (function_exists('wienimal_editor_toolbar_content')) {
            return wienimal_editor_toolbar_content();
        }

        return [];
    }

    private function getCustomOverviewRoutes() {
        if (function_exists('wienimal_editor_toolbar_custom_overview_routes')) {
            return wienimal_editor_toolbar_custom_overview_routes();
        }

        return [];
    }

    private function getCustomOverviewRoute(string $toFind) {
        return $this->getCustomRoute($this->getCustomOverviewRoutes(), $toFind);
    }

    private function getCustomCreateRoutes() {
        if (function_exists('wienimal_editor_toolbar_custom_create_routes')) {
            return wienimal_editor_toolbar_custom_create_routes();
        }

        return [];
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
