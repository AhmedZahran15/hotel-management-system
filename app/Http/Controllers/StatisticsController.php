<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index()
{
    //male to female reservation ratio pie chart
    $maleCount = Reservation::whereHas('client', fn($q) => $q->where('gender', 'male'))->count();
    $femaleCount = Reservation::whereHas('client', fn($q) => $q->where('gender', 'female'))->count();
    
    //reservation revenue by month line chart
    $months = range(1, 12);
    $revenueData = [];
    $monthLabels = [];
    foreach ($months as $month) {
        $monthLabels[] = date('F', mktime(0, 0, 0, $month, 10));
        $revenueData[] = Reservation::whereYear('reservation_date', now()->year)
            ->whereMonth('reservation_date', $month)
            ->sum('reservation_price');
    }
    
    //reservation count by country pie chart
    $countries = Reservation::with('client')
        ->get()
        ->groupBy(fn($r) => $r->client->country ?? 'Unknown');
    $countryLabels = array_keys($countries->toArray());
    $countryData = array_map(fn($group) => count($group), $countries->toArray());
    
    //top clients reservation count pie chart
    $topClients = Reservation::selectRaw('client_id, count(*) as reservations_count')
        ->groupBy('client_id')
        ->orderByDesc('reservations_count')
        ->limit(10)
        ->with('client')
        ->get();
    $clientLabels = $topClients->pluck('client.name')->toArray();
    $clientData = $topClients->pluck('reservations_count')->toArray();
return [$maleCount, $femaleCount, $revenueData, $monthLabels, $countryLabels, $countryData, $clientLabels, $clientData];
    // return Inertia::render('Statistics', [
    //     'statistics' => [
    //         'maleFemale' => ['male' => $maleCount, 'female' => $femaleCount],
    //         'revenue' => ['labels' => $monthLabels, 'data' => $revenueData],
    //         'countries' => ['labels' => $countryLabels, 'data' => $countryData],
    //         'topClients' => ['labels' => $clientLabels, 'data' => $clientData],
    //     ],
    // ]);
}

}
