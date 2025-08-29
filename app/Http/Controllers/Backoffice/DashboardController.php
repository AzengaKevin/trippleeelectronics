<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        activity()->log('Accessed the backoffice dashboard');

        return Inertia::render('backoffice/DashboardPage');
    }
}
