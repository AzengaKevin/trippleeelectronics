<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->inGroup('payment', function ($settingsBlueprint) {
            $settingsBlueprint->add('mpesa_payment_method', null);
            $settingsBlueprint->add('cash_payment_method', null);
        });
    }
};
