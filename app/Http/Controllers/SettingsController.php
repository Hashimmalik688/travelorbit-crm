<?php

namespace App\Http\Controllers;

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

    public function vendors()
    {
        return view('content.settings.vendors');
    }

    public function gds()
    {
        return view('content.settings.gds');
    }
}
