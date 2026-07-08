<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;

class CallCenterController extends Controller
{
    public function dashboard()
    {
        return view('content.calldesk.dashboard');
    }

    public function newCall()
    {
        return view('content.calldesk.new-call');
    }

    public function inquiries()
    {
        return view('content.calldesk.inquiries');
    }

    public function callbacks()
    {
        return view('content.calldesk.callbacks');
    }
}
