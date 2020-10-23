<?php

namespace Drupal\wienimal_editor_toolbar\Service;

class VersionInfo
{
    public function get(): ?array
    {
        $path = DRUPAL_ROOT . '/version.json';

        if (!file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);

        if (!isset($data['version'], $data['date'])) {
            return null;
        }

        return [
            'version' => $data['version'],
            'date' => \DateTime::createFromFormat(\DateTime::ISO8601, $data['date'])
                ->setTimezone(new \DateTimeZone('Europe/Brussels')),
        ];
    }
}
