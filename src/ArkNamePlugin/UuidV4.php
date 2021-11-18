<?php

namespace Quark\ArkNamePlugin;

use Ark\Name\Plugin\PluginInterface;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Settings\Settings;

class UuidV4 implements PluginInterface
{
    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function isFullArk()
    {
        return true;
    }

    public function create(AbstractResourceEntityRepresentation $resource)
    {
        $uuid = $this->uuid();
        $uuid = str_replace('-', '', $uuid);
        return $this->settings->get('ark_naan') . '/' . $uuid;
    }

    // Not part of the interface, but required nevertheless
    public function isDatabaseCreated()
    {
        return true;
    }

    public function infoDatabase($level = 'meta')
    {
        if ($level === 'meta') {
            return [
                'naan' => $this->settings->get('ark_naan'),
                'naa' => $this->settings->get('ark_naa'),
                'subnaa' => $this->settings->get('ark_subnaa'),
                'template' => $this->settings->get('ark_name_noid_template'),
            ];
        }
    }

    protected function uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
