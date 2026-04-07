<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildCustomerDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request, BuildCustomerDashboard $buildCustomerDashboard): View
    {
        return view('dashboard.index', [
            ...$buildCustomerDashboard->handle($request->user()),
            'activeSection' => $request->string('section', 'overview')->value(),
        ]);
    }
}
