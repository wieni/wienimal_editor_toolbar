<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeInterface;

class ContentOverviewMenuItem extends ContentMenuItem
{
    protected function getMenuItemName(): string
    {
        return 'content_overview';
    }

    protected function getRoute(EntityTypeInterface $entityType, string $bundle): array
    {
        if ($entityType->getProvider() === 'eck') {
            return [
                'route_name' => sprintf('eck.entity.%s.list', $entityType->id()),
                'route_parameters' => [],
                'options' => [
                    'query' => [
                        'type' => $bundle,
                    ],
                ],
            ];
        }

        if ($entityType->id() === 'taxonomy_term') {
            return [
                'route_name' => 'entity.taxonomy_vocabulary.overview_form',
                'route_parameters' => [
                    $entityType->getBundleEntityType() => $bundle,
                ],
            ];
        }

        if ($bundleKey = $entityType->getKey('bundle')) {
            return [
                'route_name' => 'system.admin_content',
                'route_parameters' => [
                    $bundleKey => $bundle,
                ],
            ];
        }

        return [
            'route_name' => 'system.admin_content',
        ];
    }
}
