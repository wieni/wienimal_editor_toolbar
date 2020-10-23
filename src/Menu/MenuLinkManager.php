<?php

namespace Drupal\wienimal_editor_toolbar\Menu;

use Drupal\Core\Menu\MenuLinkManager as MenuLinkManagerAliasBase;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\wienimal_editor_toolbar\Plugin\Discovery\ChainedDiscovery;
use Drupal\wienimal_editor_toolbar\Plugin\Discovery\MenuYamlDiscovery;

class MenuLinkManager extends MenuLinkManagerAliasBase
{
    protected function getDiscovery()
    {
        return new ChainedDiscovery(
            $this->createYamlDiscovery('links.menu', 'admin'),
            $this->createYamlDiscovery('links.menu.editor', 'editor')
        );
    }

    protected function createYamlDiscovery(string $name, string $menuName): ContainerDerivativeDiscoveryDecorator
    {
        $discovery = new MenuYamlDiscovery($name, $this->moduleHandler->getModuleDirectories(), $menuName);
        $discovery->addTranslatableProperty('title', 'title_context');
        $discovery->addTranslatableProperty('description', 'description_context');

        return new ContainerDerivativeDiscoveryDecorator($discovery);
    }
}
