<?php

namespace Drupal\wienimal_editor_toolbar;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\wienimal_editor_toolbar\Service\EditorToolbarLanguageNegotiator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
                    new Reference('language_manager'),
                    new Reference('plugin.manager.language_negotiation_method'),
                    new Reference('config.factory'),
                    new Reference('settings'),
                    new Reference('request_stack'),
                    new Reference('current_user'),
                ])
            );
        }
    }
}
