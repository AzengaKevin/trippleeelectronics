<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReceiptSettings extends Settings
{
    public ?string $receipt_footer = null;

    public ?bool $show_receipt_header = true;

    public ?bool $show_receipt_footer = true;

    public static function group(): string
    {
        return 'receipt';
    }
}
