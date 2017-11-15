<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountProxyInterface;

class EditorToolbarAccessCheck implements AccessInterface {
    /** @var AccountProxyInterface $currentUser */
    private $currentUser;

    /**
     * CleanToolbarMenuBuilder constructor.
     * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
     */
    public function __construct(
        AccountProxyInterface $currentUser
    ) {
        $this->currentUser = $currentUser;
    }

    public function access() {
        return AccessResult::allowedIf(
            $this->currentUser->hasPermission('access editor toolbar')
            && in_array('editor', $this->currentUser->getRoles())
        );
    }
}
