<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Plugin\views\wizard\TaxonomyTerm;
use Drupal\wienimal_editor_toolbar\Service\EditorToolbarContentCollector;

class ContentOverviewMenuItem extends DeriverBase
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

    public function getDerivativeDefinitions($basePluginDefinition)
    {
        return $this->contentCollector->getOverviewMenu($basePluginDefinition);
    }
}
