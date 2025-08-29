<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $site_name = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $location = null;

    public bool $show_categories_banner = true;

    public ?string $facebook_link = null;

    public ?string $tiktok_link = null;

    public ?string $instagram_link = null;

    public ?string $whatsapp_link = null;

    public ?string $kra_pin = null;

    public static function group(): string
    {
        return 'general';
    }
}
