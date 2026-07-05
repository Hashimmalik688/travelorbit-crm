<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;

class CallCenterController extends Controller
{
    public function dashboard()
    {
        return view('content.callcenter.dashboard');
    }

    public function newCall()
    {
        return view('content.callcenter.new-call');
    }

    public function inquiries()
    {
        return view('content.callcenter.inquiries');
    }

    public function callbacks()
    {
        return view('content.callcenter.callbacks');
    }
}
