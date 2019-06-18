<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\language\ConfigurableLanguageManagerInterface;
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

    /** @var LanguageNegotiatorInterface **/
    protected $originalNegotiator;
    /** @var EditorToolbarLanguageNegotiator **/
    protected $customNegotiator;

    public function __construct(
        MenuLinkTreeInterface $menuTree,
        AccountProxyInterface $currentUser,
        ThemeHandlerInterface $themeHandler,
        MenuActiveTrailInterface $menuActiveTrail,
        LanguageManagerInterface $languageManager,
        TranslationManager $translationManager
    ) {
        $this->menuTree = $menuTree;
        $this->currentUser = $currentUser;
        $this->themeHandler = $themeHandler;
        $this->menuActiveTrail = $menuActiveTrail;
        $this->languageManager = $languageManager;
        $this->translationManager = $translationManager;
    }

    public function setLanguageNegotiator(EditorToolbarLanguageNegotiator $languageNegotiator)
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
            '#attached' => [
                'library' => [
                    'wienimal_editor_toolbar/editbar-top',
                ],
            ],
        ];

        $this->restoreLanguage();
    }

    /**
     * Get the name of the menu
     */
    public function getMenuName(): string
    {
        return 'admin';
    }

    /**
     * Load, transform and return the menu
     */
    public function buildMenu(): array
    {
        $activeTrail = $this->menuActiveTrail->getActiveTrailIds('admin');

        // Build the typical default set of menu tree parameters.
        $parameters = new MenuTreeParameters();
        $parameters
            ->setRoot('system.admin')
            ->setActiveTrail($activeTrail)
            ->excludeRoot()
            ->setMaxDepth(4)
            ->onlyEnabledLinks();

        // Load the tree based on this set of parameters.
        $tree = $this->menuTree->load($this->getMenuName(), $parameters);

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
            // Use the default sorting of menu links.
            ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
        ];
        $tree = $this->menuTree->transform($tree, $manipulators);

        // Finally, build a renderable array from the transformed tree.
        $menu = $this->menuTree->build($tree);

        return $menu;
    }

    /**
     * Check if the current user has permission to see the toolbar
     */
    private function showToolbar(): bool
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

        $this->languageManager->setNegotiator($this->originalNegotiator);
        $adminLangcode = $this->languageManager
            ->reset(LanguageInterface::TYPE_INTERFACE)
            ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
            ->getId();
        $this->translationManager->setDefaultLangcode($adminLangcode);
    }
}
