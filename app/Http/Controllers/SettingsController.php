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

    public function vendors()
    {
        return view('content.settings.vendors');
    }

    public function gds()
    {
        return view('content.settings.gds');
    }

    /**
     * The standalone e-ticket template.
     *
     * Served as a raw file rather than a Blade view on purpose: it is a complete,
     * self-contained HTML document whose CSS uses @import, @media and @page, all of
     * which Blade would try to parse as directives. Agents edit the DATA block at the
     * bottom and print to PDF, so it must also render outside the CRM layout.
     */
    public function eticketTemplate()
    {
        $path = resource_path('eticket/travel-orbit-eticket-template.html');

        abort_unless(is_file($path), 404, 'E-ticket template not found.');

        return response()->file($path, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
