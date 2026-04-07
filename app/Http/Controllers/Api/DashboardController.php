<?php

namespace App\Http\Controllers\Api;

use App\Actions\Dashboard\BuildCustomerDashboard;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request, BuildCustomerDashboard $buildCustomerDashboard): DashboardResource
    {
        return new DashboardResource($buildCustomerDashboard->handle($request->user()));
    }
}
