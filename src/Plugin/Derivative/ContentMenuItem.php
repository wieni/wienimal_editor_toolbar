<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\wienimal_editor_toolbar\TranslatableEntityLabelMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ContentMenuItem extends DeriverBase implements ContainerDeriverInterface
{
    /** @var ConfigFactoryInterface */
    protected $configFactory;
    /** @var EntityTypeManagerInterface */
    protected $entityTypeManager;
    /** @var ModuleHandlerInterface */
    protected $moduleHandler;

    public function __construct(
        ConfigFactoryInterface $configFactory,
        EntityTypeManagerInterface $entityTypeManager,
        ModuleHandlerInterface $moduleHandler
    ) {
        $this->configFactory = $configFactory;
        $this->entityTypeManager = $entityTypeManager;
        $this->moduleHandler = $moduleHandler;
    }

    public static function create(ContainerInterface $container, $base_plugin_id)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('entity_type.manager'),
            $container->get('module_handler'),
        );
    }

    public function getDerivativeDefinitions($basePluginDefinition)
    {
        $config = $this->configFactory->get('wienimal_editor_toolbar.settings');
        $menu = [];

        foreach ($config->get('content') as $entityTypeId => $bundleValues) {
            try {
                $definition = $this->entityTypeManager->getDefinition($entityTypeId);
            } catch (PluginNotFoundException $e) {
                continue;
            }

            $bundleStorage = $this->entityTypeManager->getStorage($definition->getBundleEntityType());
            $bundles = $this->getBundleInfo($definition);

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
                $bundleEntity = $bundleStorage->load($bundle);

                if (
                    $this->moduleHandler->moduleExists('wmsingles')
                    && $bundleEntity->getThirdPartySetting('wmsingles', 'isSingle')
                ) {
                    continue;
                }

                $menu[$id] = [
                        'id' => $id,
                        'title' => $info['label'],
                    ] + $route + $basePluginDefinition;
            }
        }

        return $menu;
    }

    protected function getBundleInfo(EntityTypeInterface $entityType): array
    {
        $bundles = [];

        if ($bundleEntityType = $entityType->getBundleEntityType()) {
            foreach ($this->entityTypeManager->getStorage($bundleEntityType)->loadMultiple() as $entity) {
                $bundles[$entity->id()]['label'] = new TranslatableEntityLabelMarkup($entity->getEntityTypeId(), $entity->id());
            }
        }

        return $bundles;
    }

    abstract protected function getRoute(EntityTypeInterface $entityType, string $bundle): array;
}
