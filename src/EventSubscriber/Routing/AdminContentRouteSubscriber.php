<?php

namespace Drupal\wienimal_editor_toolbar\EventSubscriber\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

class AdminContentRouteSubscriber extends RouteSubscriberBase
{
    public static function getSubscribedEvents(): array
    {
        $events[RoutingEvents::ALTER] = ['onAlterRoutes'];

        return $events;
    }

    protected function alterRoutes(RouteCollection $collection): void
    {
        if ($route = $collection->get('system.admin_content')) {
            $permission = implode(',', array_filter([
                $route->getRequirement('_permission'),
                'access editor toolbar',
            ]));

            $route->setRequirement('_permission', $permission);
        }
    }
}
