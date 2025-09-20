<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        return Inertia::render('Dashboards/AdminDashboard');
    }


}
