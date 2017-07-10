<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\NodeType;

class NodeTypesMenuLink extends DeriverBase
{

    public function getDerivativeDefinitions($basePluginDefinition)
    {
        $links = [];

        foreach (NodeType::loadMultiple() as $nodeType) {
            $id = $nodeType->get('type');
            $link = [
                'id' => $id,
                'title' => new TranslatableMarkup($nodeType->get('name')),
                'route_parameters' => [
                    'type' => $id,
                ],
            ];

            $links[$id] = $link + $basePluginDefinition;
        }

        return $links;
    }
}
