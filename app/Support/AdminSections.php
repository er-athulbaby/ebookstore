<?php

namespace App\Support;

/** The admin areas a limited admin can be given access to (Users → edit → Admin access). */
class AdminSections
{
    public const ALL = [
        'dashboard' => 'Dashboard (sales numbers)',
        'authors' => 'Authors',
        'categories' => 'Categories',
        'books' => 'Books',
        'bundles' => 'Bundles',
        'orders' => 'Orders',
        'coupons' => 'Coupons',
        'gift_cards' => 'Gift cards (designs & vouchers)',
        'banners' => 'Banners',
        'offer_popup' => 'Offer popup',
        'users' => 'Users / customers',
        'site_settings' => 'Site settings (logo, email, tracking)',
        'shipping' => 'Shipping charges',
    ];
}
