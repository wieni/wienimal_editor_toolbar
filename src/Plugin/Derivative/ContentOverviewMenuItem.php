<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeInterface;

class ContentOverviewMenuItem extends ContentMenuItem
{
    protected function getRoute(EntityTypeInterface $entityType, string $bundle): array
    {
        if ($entityType->getProvider() === 'eck') {
            return [
                'route_name' => "eck.entity.{$entityType->id()}.list",
                'route_parameters' => [],
                'options' => [
                    'query' => [
                        'type' => $bundle,
                    ]
                ]
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
                    $entityType->getKey('bundle') => $bundle,
                ],
            ];
        }

        return [
            'route_name' => 'system.admin_content',
        ];
    }
}
