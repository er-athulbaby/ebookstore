<?php

// Store-specific configuration for RedCherry Books.
return [

    'store_name' => env('STORE_NAME', 'RedCherry Books'),

    'currency' => env('STORE_CURRENCY', 'INR'),
    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '₹'),

    // Orders at or above this subtotal ship free (physical items only).
    'free_delivery_threshold' => (int) env('FREE_DELIVERY_THRESHOLD', 799),

    // Flat shipping fee charged below the free-delivery threshold.
    'flat_shipping_fee' => (int) env('FLAT_SHIPPING_FEE', 60),

    'return_window_days' => (int) env('RETURN_WINDOW_DAYS', 7),

    // How many times a customer may re-download a purchased ebook,
    // and how long each generated signed link stays valid (minutes).
    'ebook_download_limit' => (int) env('EBOOK_DOWNLOAD_LIMIT', 5),
    'ebook_download_link_minutes' => (int) env('EBOOK_DOWNLOAD_LINK_MINUTES', 30),

    // Range (₹) for the "Custom amount" gift card; fixed-value designs are managed in admin.
    'gift_card_custom_min' => 100,
    'gift_card_custom_max' => 10000,

    // Testing only: lets eBook orders use Cash on Delivery (the eBook is unlocked without payment).
    'cod_for_ebooks' => env('COD_FOR_EBOOKS', false),

    'support_whatsapp' => env('SUPPORT_WHATSAPP', ''),
    'support_email' => env('SUPPORT_EMAIL', 'support@redcherrybooks.example'),
    'support_phone' => env('SUPPORT_PHONE', ''),

];
