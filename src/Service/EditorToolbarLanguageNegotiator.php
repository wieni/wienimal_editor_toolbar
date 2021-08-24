<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\LanguageNegotiator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Render the editor toolbar in the user's preferred admin language, if set.
 * This should be removed if ever fixed in core.
 * @see https://www.drupal.org/project/drupal/issues/2313309
 */
class EditorToolbarLanguageNegotiator extends LanguageNegotiator
{
    public function __construct(
        ConfigurableLanguageManagerInterface $languageManager,
        PluginManagerInterface $negotiatorManager,
        ConfigFactoryInterface $configFactory,
        Settings $settings,
        RequestStack $requestStack,
        AccountProxyInterface $currentUser
    ) {
        parent::__construct($languageManager, $negotiatorManager, $configFactory, $settings, $requestStack);
        $this->currentUser = $currentUser;
    }

    public function initializeType($type): array
    {
        $language = $this->languageManager->getDefaultLanguage();

        if (
            $type === LanguageInterface::TYPE_INTERFACE
            && ($adminLangcode = $this->currentUser->getPreferredAdminLangcode(false))
            && $this->currentUser->hasPermission('access administration pages')
        ) {
            $language = $this->languageManager->getLanguage($adminLangcode);
        }

        return [static::METHOD_ID => $language];
    }
}
