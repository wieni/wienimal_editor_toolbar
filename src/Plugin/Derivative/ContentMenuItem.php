<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContentMenuItem extends DeriverBase implements ContainerDeriverInterface
{
    /** @var ConfigFactoryInterface */
    protected $configFactory;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var EntityTypeBundleInfoInterface */
    protected $entityTypeBundleInfo;

    public function __construct(
        ConfigFactoryInterface $configFactory,
        EntityTypeManagerInterface $entityTypeManager,
        EntityTypeBundleInfoInterface $entityTypeBundleInfo
    ) {
        $this->configFactory = $configFactory;
        $this->entityTypeManager = $entityTypeManager;
        $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    }

    public static function create(ContainerInterface $container, $base_plugin_id)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('entity_type.manager'),
            $container->get('entity_type.bundle.info')
        );
    }

    public function getDerivativeDefinitions($basePluginDefinition)
    {
        $config = $this->configFactory->get('wienimal_editor_toolbar.settings');
        $menu = [];

        foreach ($config->get('content') as $entityTypeId => $bundleValues) {
            $definition = $this->entityTypeManager->getDefinition($entityTypeId);

            if (!$definition instanceof EntityTypeInterface) {
                throw new \UnexpectedValueException(
                    sprintf('%s is not a valid entity type.', $entityTypeId)
                );
            }

            $bundles = $this->entityTypeBundleInfo->getBundleInfo($entityTypeId);

            if (is_array($bundleValues)) {
                $bundles = array_intersect_key($bundles, $bundleValues);

                foreach ($bundleValues as $bundleName => $bundleValue) {
                    if (is_array($bundleValue)) {
                        // A custom menu item is provided
                        $bundles[$bundleName]['route'] = $bundleValue;
                    }
                }
            }

            foreach ($bundles as $bundle => $info) {
                $id = "{$entityTypeId}.{$bundle}";
                $route = $info['route'] ?? $this->getRoute($definition, $bundle);

                $menu[$id] = [
                    'id' => $id,
                    'title' => $info['label'],
                    'route_name' => $route['route_name'],
                    'route_parameters' => $route['route_parameters'] ?? [],
                ] + $basePluginDefinition;
            }
        }

        return $menu;
    }

    abstract protected function getRoute(EntityTypeInterface $entityType, string $bundle): array;
}
