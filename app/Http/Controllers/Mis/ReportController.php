<?php

namespace App\Http\Controllers\Mis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales()
    {
        return view('content.reports.sales');
    }

    public function performance()
    {
        return view('content.reports.performance');
    }
}
