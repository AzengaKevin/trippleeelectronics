<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use App\Settings\PaymentSettings;
use App\Settings\ReceiptSettings;

class SettingsService
{
    public function getSettingsGroups()
    {
        return collect([
            [
                'value' => 'general',
                'label' => 'General Settings',
            ],
            [
                'value' => 'receipt',
                'label' => 'Receipt Settings',
            ],
            [
                'value' => 'payment',
                'label' => 'Payment Settings',
            ],
        ]);
    }

    public function get(?string $group = 'general')
    {

        if ($group === 'general') {
            return $this->getGeneralSettings();
        }

        if ($group === 'receipt') {
            return $this->getReceiptSettings();
        }

        if ($group === 'payment') {
            return $this->getPaymentSettings();
        }

        return [
            ...$this->getGeneralSettings(),
            ...$this->getReceiptSettings(),
            ...$this->getPaymentSettings(),
        ];
    }

    public function update(array $data, string $group = 'general')
    {
        if ($group === 'general') {

            $this->updateGeneralSettings($data);
        } elseif ($group === 'receipt') {

            $this->updateReceiptSettings($data);
        } elseif ($group === 'payment') {

            $this->updatePaymentSettings($data);
        }
    }

    private function getGeneralSettings()
    {
        /** @var GeneralSettings $generalSettings */
        $generalSettings = app(GeneralSettings::class);

        return [
            'site_name' => $generalSettings->site_name,
            'email' => $generalSettings->email,
            'phone' => $generalSettings->phone,
            'location' => $generalSettings->location,
            'show_categories_banner' => $generalSettings->show_categories_banner,
            'facebook_link' => $generalSettings->facebook_link,
            'tiktok_link' => $generalSettings->tiktok_link,
            'instagram_link' => $generalSettings->instagram_link,
            'whatsapp_link' => $generalSettings->whatsapp_link,
            'kra_pin' => $generalSettings->kra_pin,
        ];
    }

    private function getReceiptSettings()
    {
        /** @var ReceiptSettings $receiptSettings */
        $receiptSettings = app(ReceiptSettings::class);

        return [
            'receipt_footer' => $receiptSettings->receipt_footer,
            'show_receipt_header' => $receiptSettings->show_receipt_header,
            'show_receipt_footer' => $receiptSettings->show_receipt_footer,
        ];
    }

    private function getPaymentSettings()
    {
        /** @var PaymentSettings $paymentSettings */
        $paymentSettings = app(PaymentSettings::class);

        return [
            'mpesa_payment_method' => $paymentSettings->mpesa_payment_method,
            'cash_payment_method' => $paymentSettings->cash_payment_method,
        ];
    }

    private function updateGeneralSettings(array $data)
    {
        /** @var GeneralSettings $generalSettings */
        $generalSettings = app(GeneralSettings::class);

        $generalSettings->site_name = data_get($data, 'site_name', $generalSettings->site_name);
        $generalSettings->kra_pin = data_get($data, 'kra_pin', $generalSettings->kra_pin);
        $generalSettings->email = data_get($data, 'email', $generalSettings->email);
        $generalSettings->phone = data_get($data, 'phone', $generalSettings->phone);
        $generalSettings->location = data_get($data, 'location', $generalSettings->location);
        $generalSettings->show_categories_banner = data_get($data, 'show_categories_banner', $generalSettings->show_categories_banner);
        $generalSettings->facebook_link = data_get($data, 'facebook_link', $generalSettings->facebook_link);
        $generalSettings->tiktok_link = data_get($data, 'tiktok_link', $generalSettings->tiktok_link);
        $generalSettings->instagram_link = data_get($data, 'instagram_link', $generalSettings->instagram_link);
        $generalSettings->whatsapp_link = data_get($data, 'whatsapp_link', $generalSettings->whatsapp_link);

        $generalSettings->save();
    }

    private function updateReceiptSettings(array $data)
    {

        /** @var ReceiptSettings $receiptSettings */
        $receiptSettings = app(ReceiptSettings::class);

        $receiptSettings->receipt_footer = data_get($data, 'receipt_footer', $receiptSettings->receipt_footer);
        $receiptSettings->show_receipt_header = data_get($data, 'show_receipt_header', $receiptSettings->show_receipt_header);
        $receiptSettings->show_receipt_footer = data_get($data, 'show_receipt_footer', $receiptSettings->show_receipt_footer);

        $receiptSettings->save();
    }

    private function updatePaymentSettings(array $data)
    {
        /** @var PaymentSettings $paymentSettings */
        $paymentSettings = app(PaymentSettings::class);

        $paymentSettings->mpesa_payment_method = data_get($data, 'mpesa_payment_method', $paymentSettings->mpesa_payment_method);
        $paymentSettings->cash_payment_method = data_get($data, 'cash_payment_method', $paymentSettings->cash_payment_method);

        $paymentSettings->save();
    }
}
