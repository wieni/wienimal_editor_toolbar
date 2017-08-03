<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\eck\EckEntityTypeBundleInfo;
use Drupal\eck\Entity\EckEntityType;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\EckEntityContentSource;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\NodeContentSource;
use Drupal\wienimal_editor_toolbar\Service\ContentSource\TaxonomyTermContentSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EditorToolbarContentInfo
{
    /** @var CurrentRouteMatch $currentRouteMatch */
    protected $currentRouteMatch;
    /** @var EckEntityTypeBundleInfo $entityTypeBundleInfo */
    protected $entityTypeBundleInfo;
    /** @var RequestStack $requestStack */
    protected $requestStack;
    /** @var Request */
    protected $request;
    /** @var EckEntityContentSource $eckContentSource */
    private $eckContentSource;
    /** @var NodeContentSource $nodeContentSource */
    private $nodeContentSource;
    /** @var TaxonomyTermContentSource $taxonomyTermContentSource */
    private $taxonomyTermContentSource;

    /**
     * EditorToolbarContentCollector constructor.
     * @param CurrentRouteMatch $currentRouteMatch
     * @param EckEntityTypeBundleInfo $entityTypeBundleInfo
     * @param RequestStack $requestStack
     * @param NodeContentSource $nodeContentSource
     * @param TaxonomyTermContentSource $taxonomyTermContentSource
     * @param EckEntityContentSource $eckContentSource
     */
    public function __construct(
        CurrentRouteMatch $currentRouteMatch,
        EckEntityTypeBundleInfo $entityTypeBundleInfo,
        RequestStack $requestStack,
        NodeContentSource $nodeContentSource,
        TaxonomyTermContentSource $taxonomyTermContentSource,
        EckEntityContentSource $eckContentSource
    ) {
        $this->currentRouteMatch = $currentRouteMatch;
        $this->entityTypeBundleInfo = $entityTypeBundleInfo;
        $this->requestStack = $requestStack;

        $this->nodeContentSource = $nodeContentSource;
        $this->taxonomyTermContentSource = $taxonomyTermContentSource;
        $this->eckContentSource = $eckContentSource;

        $this->request = $requestStack->getCurrentRequest();
    }

    public function getContentIdFromRoute()
    {
        switch ($this->currentRouteMatch->getRouteName()) {
            case 'node.add':
            case 'system.admin_content':
                return $this->getContentId('node');
            case 'entity.taxonomy_term.add_form':
            case 'entity.taxonomy_vocabulary.overview_form':
                return $this->getContentId('taxonomy');
            case 'eck.entity.add':
                return $this->getContentId('eck');
            default:
                return false;
        }
    }

    public function getInfo(string $source)
    {
        switch($source) {
            case 'eck':
                return $this->getEckInfoFromRoute();
            case 'taxonomy':
                return $this->getTaxonomyInfoFromRoute();
            case 'node':
                return $this->getNodeInfoFromRoute();
            default:
                return false;
        }
    }

    public function getContentId(string $source)
    {
        $info = $this->getInfo($source);

        switch($source) {
            case 'eck':
                return $this->eckContentSource->buildId($info);
            case 'taxonomy':
                return $this->taxonomyTermContentSource->buildId($info);
            case 'node':
                return $this->nodeContentSource->buildId($info);
            default:
                return false;
        }
    }

    /**
     * @return array
     */
    protected function getEckInfoFromRoute()
    {
        $result = [];
        $entityTypeBundle = $this->currentRouteMatch->getParameter('eck_entity_bundle');

        // From request
        $entityTypeFromRequest = $this->request->attributes->get('entity_type');
        if (!empty($entityTypeFromRequest)) {
            $entityType = $entityTypeFromRequest;
        }

        // From route
        $entityTypeFromRoute = $this->currentRouteMatch->getParameter('eck_entity_type');
        if (!empty($entityTypeFromRoute)) {
            $entityType = $entityTypeFromRoute->id();
        }

        if (!empty($entityType)) {
            $result['entityType'] = $entityType;
            $result['entityTypeTitle'] = EckEntityType::load($entityType)->label();
        }

        if (!empty($entityTypeBundle)) {
            $result['bundle'] = $entityTypeBundle;

            $bundleInfo = $this->entityTypeBundleInfo->getAllBundleInfo();
            $result['bundleTitle'] = $bundleInfo[$result['entityType']][$result['bundle']]['label'];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getTaxonomyInfoFromRoute()
    {
        /** @var Vocabulary $vocabulary */
        $vocabulary = $this->currentRouteMatch->getParameter('taxonomy_vocabulary');

        return [
            'vocabulary' => $vocabulary->get('vid'),
            'title' => $vocabulary->get('name')
        ];
    }

    /**
     * @return array
     */
    protected function getNodeInfoFromRoute()
    {
        /** @var NodeType $nodeType */
        $nodeTypeFromRoute = $this->currentRouteMatch->getParameter('node_type');
        $nodeTypeFromRequest = $this->request->get('type');
        $bundles = $this->entityTypeBundleInfo->getBundleInfo('node');
        $result = [];

        if (
            $this->currentRouteMatch->getRouteName() === 'system.admin_content'
                && array_key_exists($nodeTypeFromRequest, $bundles)
        ) {
            $entity = NodeType::load($nodeTypeFromRequest);
            return [
                'type' => $entity->get('type'),
                'typeTitle' => $entity->get('name')
            ];
        }

        if (!empty($nodeTypeFromRoute)) {
            $result['type'] = $nodeTypeFromRoute->id();
            $result['typeTitle'] = $nodeTypeFromRoute->get('name');
        }

        $subType = $this->request->get('type');
        if (!empty($subType)) {
            $result['subType'] = $subType;
            $result['subTypeTitle'] = ucfirst($subType);
        }

        return $result;
    }
}
