<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\LanguageNegotiator;
use Drupal\language\LanguageNegotiatorInterface;

class EditorToolbarMenuBuilder
{
    /** @var MenuLinkTreeInterface */
    protected $menuTree;
    /** @var AccountProxyInterface */
    protected $currentUser;
    /** @var ThemeHandlerInterface */
    protected $themeHandler;
    /** @var MenuActiveTrailInterface */
    protected $menuActiveTrail;
    /** @var LanguageManagerInterface */
    protected $languageManager;
    /** @var TranslationManager */
    protected $translationManager;
	/** @var ConfigFactoryInterface */
	protected $configFactory;

    /** @var LanguageNegotiatorInterface **/
    protected $originalNegotiator;
    /** @var LanguageNegotiator **/
    protected $defaultNegotiator;
    /** @var EditorToolbarLanguageNegotiator **/
    protected $customNegotiator;

    public function __construct(
        MenuLinkTreeInterface $menuTree,
        AccountProxyInterface $currentUser,
        ThemeHandlerInterface $themeHandler,
        MenuActiveTrailInterface $menuActiveTrail,
        LanguageManagerInterface $languageManager,
        TranslationManager $translationManager,
		ConfigFactoryInterface $configFactory
    ) {
        $this->menuTree = $menuTree;
        $this->currentUser = $currentUser;
        $this->themeHandler = $themeHandler;
        $this->menuActiveTrail = $menuActiveTrail;
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
		$this->configFactory = $configFactory;
    }

    public function setDefaultLanguageNegotiator(LanguageNegotiator $languageNegotiator)
    {
        $this->defaultNegotiator = $languageNegotiator;
    }

    public function setCustomLanguageNegotiator(EditorToolbarLanguageNegotiator $languageNegotiator)
    {
        $this->customNegotiator = $languageNegotiator;
    }

    public function buildPageTop(array &$page_top)
    {
        $this->switchToUserAdminLanguage();

        $page_top['wienimal_editor_toolbar'] = [
            '#theme' => 'wienimal_editor_toolbar',
            '#links' => $this->buildMenu(),
            '#access' => $this->showToolbar(),
        ];

        $this->restoreLanguage();
    }

    protected function getMenuName(): ?string
    {
        if (!$this->showToolbar()) {
            return null;
        }

		return $this->configFactory
            ->get('wienimal_editor_toolbar.settings')
            ->get('menu');
    }

    protected function getRootMenuLink(): ?string
    {
        if (!$this->showToolbar()) {
            return 'system.admin';
        }

		return $this->configFactory
            ->get('wienimal_editor_toolbar.settings')
            ->get('root_menu_link');
    }

    protected function getMenuTreeParameters(): MenuTreeParameters
    {
		$activeTrail = $this->menuActiveTrail->getActiveTrailIds($this->getMenuName());

        $parameters = (new MenuTreeParameters)
            ->setActiveTrail($activeTrail)
            ->excludeRoot()
            ->setMaxDepth(3)
            ->onlyEnabledLinks();

        if ($rootMenuLink = $this->getRootMenuLink()) {
            $parameters->setRoot($rootMenuLink);
        }

        return $parameters;
    }

    /**
     * Load, transform and return the menu
     */
    public function buildMenu(): array
    {
        $tree = $this->menuTree->load($this->getMenuName(), $this->getMenuTreeParameters());

        // Transform the tree using the manipulators you want.
        $manipulators = [
            // Only show links that are accessible for the current user.
            ['callable' => 'menu.default_tree_manipulators:checkAccess'],
            // Check if 'Content overview' and 'Add content' menu items have to be shown
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:checkCustomMenuItemsAccess'],
            // Move certain menu items to the root of the toolbar
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:expandMenuItem'],
            // Remove certain unneeded menu items for editors
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:removeMenuItems'],
            // Make certain menu items not clickable
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:makeMenuItemsNotClickable'],
            // Remove menu links without link and without children
            ['callable' => 'wienimal_editor_toolbar.tree_manipulators:removeEmptyMenuItems'],
            // Use the default sorting of menu links.
            ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
        ];
        $tree = $this->menuTree->transform($tree, $manipulators);

        // Finally, build a renderable array from the transformed tree.
        $menu = $this->menuTree->build($tree);

        return $menu;
    }

    public function preRenderTray(array $build)
    {
        $build['administration_menu'] = $this->buildMenu();

        return $build;
    }

    /**
     * Check if the current user has permission to see the toolbar
     */
    public function showToolbar(): bool
    {
        return $this->currentUser->hasPermission('access editor toolbar')
            && !$this->currentUser->hasPermission('access administration menu');
    }

    protected function switchToUserAdminLanguage()
    {
        if (
            !isset($this->customNegotiator)
            || !$this->languageManager instanceof ConfigurableLanguageManagerInterface
        ) {
            return;
        }

        $this->originalNegotiator = $this->languageManager->getNegotiator();
        $this->languageManager->setNegotiator($this->customNegotiator);
        $adminLangcode = $this->languageManager
            ->reset(LanguageInterface::TYPE_INTERFACE)
            ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
            ->getId();
        $this->translationManager->setDefaultLangcode($adminLangcode);
    }

    protected function restoreLanguage()
    {
        if (
            !isset($this->customNegotiator)
            || !$this->languageManager instanceof ConfigurableLanguageManagerInterface
        ) {
            return;
        }

        $this->languageManager->setNegotiator($this->originalNegotiator ?? $this->defaultNegotiator);
        $adminLangcode = $this->languageManager
            ->reset(LanguageInterface::TYPE_INTERFACE)
            ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
            ->getId();
        $this->translationManager->setDefaultLangcode($adminLangcode);
    }
}
