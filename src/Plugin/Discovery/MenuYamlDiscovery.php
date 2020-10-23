<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\YamlDiscovery;

class MenuYamlDiscovery extends YamlDiscovery
{
    /** @var string */
    protected $menuName;

    public function __construct($name, array $directories, string $menuName)
    {
        parent::__construct($name, $directories);
        $this->menuName = $menuName;
    }

    public function getDefinitions()
    {
        return array_map(
            function (array $definition): array {
                $definition['menu_name'] = $this->menuName;
                return $definition;
            },
            parent::getDefinitions()
        );
    }
}
