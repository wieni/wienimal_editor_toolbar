<?php

namespace Drupal\wienimal_editor_toolbar;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\wienimal_editor_toolbar\Service\EditorToolbarLanguageNegotiator;
use Symfony\Component\DependencyInjection\Definition;

class WienimalEditorToolbarServiceProvider implements ServiceModifierInterface
{
    public function alter(ContainerBuilder $container)
    {
        if (
            $container->has('language_negotiator')
            && $container->has('plugin.manager.language_negotiation_method')
        ) {
            $container->setDefinition(
                'wienimal_editor_toolbar.language_negotiator',
                new Definition(EditorToolbarLanguageNegotiator::class, [
                    'language_manager',
                    'plugin.manager.language_negotiation_method',
                    'config.factory',
                    'settings',
                    'request_stack',
                    'current_user',
                ])
            );
        }
    }
}
