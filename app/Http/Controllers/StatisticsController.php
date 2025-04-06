<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatisticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Charts/Statistics');
    }
    public function maleFemaleChart()
    {
        $maleCount = Reservation::whereHas('client', fn($q) => $q->where('gender', 'male'))->count();
        $femaleCount = Reservation::whereHas('client', fn($q) => $q->where('gender', 'female'))->count();
        return response()->json([
            'male' => $maleCount,
            'female' => $femaleCount,
        ]);
    }
    public function revenueChart()
    {
        $months = range(1, 12);
        $revenueData = [];
        $monthLabels = [];
        foreach ($months as $month) {
            $monthLabels[] = date('F', mktime(0, 0, 0, $month, 10));
            $revenueData[] = Reservation::whereYear('reservation_date', now()->year)
                ->whereMonth('reservation_date', $month)
                ->sum('reservation_price');
        }
        return response()->json([
            'labels' => $monthLabels,
            'data' => $revenueData,
        ]);
    }
    public function countriesChart()
    {
        $countries = Reservation::with('client')
            ->get()
            ->groupBy(fn($r) => $r->client->country ?? 'Unknown');
        $countryLabels = array_keys($countries->toArray());
        $countryData = array_map(fn($group) => count($group), $countries->toArray());
        return response()->json([
            'labels' => $countryLabels,
            'data' => $countryData,
        ]);
    }
    public function topClientsChart()    
    {
        $topClients = Reservation::selectRaw('client_id, count(*) as reservations_count')
            ->groupBy('client_id')
            ->orderByDesc('reservations_count')
            ->limit(10)
            ->with('client')
            ->get();
        $clientLabels = $topClients->pluck('client.name')->toArray();
        $clientData = $topClients->pluck('reservations_count')->toArray();
        return response()->json([
            'labels' => $clientLabels,
            'data' => $clientData,
        ]);
    }

    

}
