<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class RefundController extends Controller
{
    public function index()
    {
        return view('content.finance.refunds');
    }
}
