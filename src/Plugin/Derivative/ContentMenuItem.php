<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
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

    public static function create(ContainerInterface $container, $base_plugin_id)
    {
        $instance = new static();
        $instance->configFactory = $container->get('config.factory');
        $instance->entityTypeManager = $container->get('entity_type.manager');
        $instance->moduleHandler = $container->get('module_handler');

        return $instance;
    }

    public function getDerivativeDefinitions($basePluginDefinition): array
    {
        $menuItemName = $this->getMenuItemName();
        $config = $this->configFactory->get('wienimal_editor_toolbar.settings');
        $entityTypes = $config->get(sprintf('menu_items.%s.entity_types', $menuItemName)) ?? [];
        $overrides = $config->get(sprintf('menu_items.%s.overrides', $menuItemName));
        $menu = [];

        foreach ($entityTypes as $entityTypeId => $bundles) {
            $definition = $this->entityTypeManager->getDefinition($entityTypeId, false);

            if (!$definition) {
                continue;
            }

            $bundleStorage = $this->entityTypeManager->getStorage($definition->getBundleEntityType());
            $allBundles = $this->getBundleInfo($definition);

            if (is_array($bundles)) {
                $bundles = array_intersect_key($allBundles, array_flip($bundles));
            } elseif ($bundles) {
                $bundles = $allBundles;
            } else {
                continue;
            }

            foreach ($bundles as $bundle => $info) {
                $id = sprintf('%s.%s', $entityTypeId, $bundle);
                $bundleEntity = $bundleStorage->load($bundle);

                if (
                    $this->moduleHandler->moduleExists('wmsingles')
                    && $bundleEntity->getThirdPartySetting('wmsingles', 'isSingle')
                ) {
                    continue;
                }

                if (
                    $this->moduleHandler->moduleExists('node_singles')
                    && $bundleEntity->getThirdPartySetting('node_singles', 'is_single')
                ) {
                    continue;
                }

                if (isset($overrides[$entityTypeId][$bundle]) && is_array($overrides[$entityTypeId][$bundle])) {
                    $route = $overrides[$entityTypeId][$bundle];
                } else {
                    $route = $this->getRoute($definition, $bundle);
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

    abstract protected function getMenuItemName(): string;

    abstract protected function getRoute(EntityTypeInterface $entityType, string $bundle): array;
}
