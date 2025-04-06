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
    public function revenueChart(Request $request, $year = null)
    {
        $year = $year ?? now()->year;
        $months = range(1, 12);
        $revenueData = [];
        $monthLabels = [];
        foreach ($months as $month) {
            $monthLabels[] = date('F', mktime(0, 0, 0, $month, 10));
            $revenueData[] = Reservation::whereYear('reservation_date', $year)
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
        $reservations = Reservation::with('client.countryInfo')->get();

        $grouped = $reservations->groupBy(function ($reservation) {
            return $reservation->client->countryInfo->name ?? 'Unknown';
        });

        $countryLabels = $grouped->keys();
        $countryData = $grouped->map->count()->values();

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
