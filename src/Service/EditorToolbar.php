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

    public function getVersionInfo()
    {
        $path = DRUPAL_ROOT . '/version.json';

        if (file_exists($path)) {
			return json_decode(file_get_contents($path), true);
        }

        return false;
    }
}
