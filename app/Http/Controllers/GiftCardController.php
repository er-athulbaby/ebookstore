<?php

namespace App\Http\Controllers;

use App\Models\GiftCardDesign;

class GiftCardController extends Controller
{
    public function index()
    {
        $designs = GiftCardDesign::where('is_active', true)->orderBy('sort_order')->orderBy('value')->get();

        return view('gift-cards.index', [
            'designs' => $designs,
            'customMin' => config('redcherry.gift_card_custom_min'),
            'customMax' => config('redcherry.gift_card_custom_max'),
        ]);
    }
}
