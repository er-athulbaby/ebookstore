<?php

namespace App\Http\Controllers;

class GiftCardController extends Controller
{
    public function index()
    {
        $denominations = config('redcherry.gift_card_denominations', []);

        return view('gift-cards.index', compact('denominations'));
    }
}
