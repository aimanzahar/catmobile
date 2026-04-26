<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\CancelBooking;
use App\Actions\Dashboard\BuildCustomerDashboard;
use Illuminate\Http\RedirectResponse;
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

    public function cancelBooking(Request $request, string $booking, CancelBooking $cancelBooking): RedirectResponse
    {
        $cancelBooking->handle($request->user(), $booking);

        return redirect()->route('dashboard')->with('status', 'Booking cancelled.');
    }
}
