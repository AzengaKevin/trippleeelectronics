<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('receipt', function ($settingsBlueprint) {
            $settingsBlueprint->add('receipt_footer', 'Thank you for shopping with us!');
            $settingsBlueprint->add('show_receipt_header', true);
            $settingsBlueprint->add('show_receipt_footer', true);
        });
    }
};
