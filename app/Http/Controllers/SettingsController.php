<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('content.settings.index');
    }

    public function users()
    {
        return view('content.settings.users');
    }

    public function auditLog()
    {
        return view('content.settings.audit-log');
    }

    public function profile()
    {
        return view('content.settings.profile');
    }
}
