<?php

namespace Drupal\wienimal_editor_toolbar\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Component\Utility\NestedArray;

class ChainedDiscovery implements DiscoveryInterface
{
    use DiscoveryTrait;

    /** @var DiscoveryInterface[] */
    protected $discoveries;

    public function __construct(...$discoveries)
    {
        $this->discoveries = $discoveries;
    }

    public function getDefinitions()
    {
        return array_reduce(
            $this->discoveries,
            static function (array $definitions, DiscoveryInterface $discovery): array {
                return NestedArray::mergeDeep($definitions, $discovery->getDefinitions());
            },
            []
        );
    }
}
