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
		$adminTheme = drupal_get_path('theme', $themeConfig->get('admin'));
		$activeTheme = drupal_get_path('theme', $themeConfig->get('default'));
        $module = drupal_get_path('module', 'wienimal_editor_toolbar');

        $possibilities = array_reduce(
            [
                "$activeTheme/logo-admin",
                "$adminTheme/logo",
                "$activeTheme/logo",
                "$module/logo"
            ],
            function ($carry, $item) {
                return array_merge(
                    $carry,
                    [
                        "$item.svg",
                        "$item.png",
                        "$item.jpg",
                    ]
                );
            },
            []
        );

        foreach ($possibilities as $possibility) {
            if (file_exists($possibility)) {
                return '/' . $possibility;
            }
        }

        return false;
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
