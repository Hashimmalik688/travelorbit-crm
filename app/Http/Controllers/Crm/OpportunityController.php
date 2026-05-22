<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index()
    {
        return view('content.crm.opportunities');
    }
}
