<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->inGroup('general', function (SettingsBlueprint $settingsBlueprint) {
            $settingsBlueprint->add('site_name', config('app.name'));
            $settingsBlueprint->add('email', 'trippleeelectronics19@yahoo.com');
            $settingsBlueprint->add('phone', '+254 720 310 183');
            $settingsBlueprint->add('location', 'Opposite Arusha Hotel, Kisii, Kenya');
            $settingsBlueprint->add('show_categories_banner', true);
            $settingsBlueprint->add('facebook_link', 'https://facebook.com');
            $settingsBlueprint->add('tiktok_link', 'https://tiktok.com');
            $settingsBlueprint->add('instagram_link', 'https://instagram.com');
            $settingsBlueprint->add('whatsapp_link', 'https://whatsapp.com');
        });
    }
};
