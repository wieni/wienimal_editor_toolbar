<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\wienimal_editor_toolbar\Service\EditorToolbarContentCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentAddMenuItem extends DeriverBase implements ContainerDeriverInterface
{
    /** @var EditorToolbarContentCollector $contentCollector */
    private $contentCollector;

    /**
     * ContentOverviewMenuItem constructor.
     * @param EditorToolbarContentCollector $contentCollector
     */
    public function __construct(EditorToolbarContentCollector $contentCollector)
    {
        $this->contentCollector = $contentCollector;
    }

    public static function create(ContainerInterface $container, $base_plugin_id)
    {
        return new static(
            $container->get('wienimal_editor_toolbar.content_collector')
        );
    }

    public function getDerivativeDefinitions($basePluginDefinition)
    {
        return $this->contentCollector->getCreateMenu($basePluginDefinition);
    }
}
