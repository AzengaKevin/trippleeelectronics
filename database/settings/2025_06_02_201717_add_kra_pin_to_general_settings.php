<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $settingsBlueprint) {
            $settingsBlueprint->add('kra_pin', 'P052166084G');
        });
    }
};
