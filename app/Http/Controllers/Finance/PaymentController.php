<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        return view('content.finance.payments');
    }
}
