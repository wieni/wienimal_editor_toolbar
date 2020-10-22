<?php

namespace Drupal\wienimal_editor_toolbar\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

class EditorToolbar
{
	/** @var ConfigFactoryInterface */
	protected $configFactory;

    public function __construct(
		ConfigFactoryInterface $configFactory
    ) {
		$this->configFactory = $configFactory;
    }

    public function getLogo()
    {
		$themeConfig = $this->configFactory->get('system.theme');

        if ($url = theme_get_setting('logo.url', $themeConfig->get('admin'))) {
            return $url;
        }

        if ($url = theme_get_setting('logo.url', $themeConfig->get('default'))) {
            return $url;
        }

        return null;
    }
}
