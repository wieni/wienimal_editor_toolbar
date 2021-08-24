<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeInterface;

class ContentAddMenuItem extends ContentMenuItem
{
    protected function getMenuItemName(): string
    {
        return 'content_add';
    }

    protected function getRoute(EntityTypeInterface $entityType, string $bundle): array
    {
        if ($entityType->getProvider() === 'eck') {
            return [
                'route_name' => 'eck.entity.add',
                'route_parameters' => [
                    'eck_entity_type' => $entityType->id(),
                    'eck_entity_bundle' => $bundle,
                ],
            ];
        }

        if ($entityType->id() === 'taxonomy_term') {
            return [
                'route_name' => 'entity.taxonomy_term.add_form',
                'route_parameters' => [
                    $entityType->getBundleEntityType() => $bundle,
                ],
            ];
        }

        if ($entityType->id() === 'node') {
            return [
                'route_name' => 'node.add',
                'route_parameters' => [
                    $entityType->getBundleEntityType() => $bundle,
                ],
            ];
        }

        foreach (['add-form', 'add-page'] as $linkTemplate) {
            if ($entityType->hasLinkTemplate($linkTemplate)) {
                return [
                    'route_name' => sprintf('entity.%s.%s', $entityType->id(), $linkTemplate),
                    'route_parameters' => [
                        'entity_type_id' => $entityType->id(),
                    ],
                ];
            }
        }

        return [
            'route_name' => '<nolink>',
        ];
    }
}
