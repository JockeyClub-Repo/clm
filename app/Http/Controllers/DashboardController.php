<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Provider;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboardRouter()
    {
        return view('dashboard');
    }

    public function adminData(Request $request)
    {
        $filter = $request->filter;

        $contracts = Contract::with('provider');

        switch ($filter) {

    case 'active':
        $contracts->where('status', 'active');
        break;

    case 'renewed':
        $contracts->where('status', 'renewed');
        break;

    case 'not_renewed':
        $contracts->where('status', 'not_renewed');
        break;

    case 'expiring':
        $contracts->where('status', 'active');
        break;

    case 'expired':
        break;
}

        $contracts = $contracts->get();
        $today = Carbon::today();

if ($filter == 'expiring') {
    $contracts = $contracts->filter(function ($c) use ($today) {
        return $c->end_date >= $today
            && $c->end_date <= $today->copy()->addDays(30);
    })->values();
}

if ($filter == 'expired') {
    $contracts = $contracts->filter(function ($c) use ($today) {
        return $c->end_date < $today;
    })->values();
}

        /*
        |--------------------------------------------------------------------------
        | KPIS
        |--------------------------------------------------------------------------
        */
        $allContracts = Contract::all();

        $today = Carbon::today();

$kpis = [

    'total' => $allContracts->count(),

    'active' => $allContracts
        ->where('status', 'active')
        ->filter(function ($c) use ($today) {
            return $c->end_date >= $today;
        })
        ->count(),

    'renewed' => $allContracts
        ->where('status', 'renewed')
        ->count(),

    'notRenewed' => $allContracts
        ->where('status', 'not_renewed')
        ->count(),

    'expiring' => $allContracts
        ->where('status', 'active')
        ->filter(function ($c) use ($today) {
            return $c->end_date >= $today
                && $c->end_date <= $today->copy()->addDays(30);
        })
        ->count(),

    'expired' => $allContracts
        ->filter(function ($c) use ($today) {
            return $c->end_date < $today;
        })
        ->count(),
];
        /*
        |--------------------------------------------------------------------------
        | CALENDAR
        |--------------------------------------------------------------------------
        */

        $calendar = Contract::with('provider')
            ->get()
            ->map(function ($contract) {

                $color = '#34c38f';

                if (
                    $contract->status_label === 'Próximo a Vencer'
                ) {
                    $color = '#f1b44c';
                }

                if (
                    $contract->status_label === 'Vence Hoy'
                ) {
                    $color = '#f46a6a';
                }

                if (
                    $contract->status === 'not_renewed'
                ) {
                    $color = '#f46a6a';
                }

                return [
                    'id' => $contract->id,
                    'title' => $contract->name,
                    'start' => $contract->end_date->format('Y-m-d'),
                    'color' => $color
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | PROVEEDORES
        |--------------------------------------------------------------------------
        */

        $providers = Provider::withCount([
            'contracts as active_count' => function ($q) {
                $q->where('status', 'active');
            },

            'contracts as renewed_count' => function ($q) {
                $q->where('status', 'renewed');
            },

            'contracts as not_renewed_count' => function ($q) {
                $q->where('status', 'not_renewed');
            }
        ])->get();

        /*
        |--------------------------------------------------------------------------
        | PROXIMOS VENCIMIENTOS
        |--------------------------------------------------------------------------
        */

        $nextExpirations = Contract::with('provider')
    ->where('status', 'active')
    ->get()
    ->filter(function ($contract) {

        return in_array(
            $contract->status_label,
            [
                'Próximo a Renovar',
                'Renovación Pendiente',
                'Próximo a Vencer',
                'Vence Hoy'
            ]
        );
    })
    ->sortBy('end_date')
    ->take(10)
    ->map(function ($contract) {

        return [
            'id' => $contract->id,
            'name' => $contract->name,
            'provider' => $contract->provider,
            'end_date' => $contract->end_date->format('d/m/Y'),
            'days_remaining' => $contract->days_remaining
        ];
    })
    ->values();

$timeline = [];

$grouped = $contracts->groupBy('provider_id');

foreach ($grouped as $providerId => $items) {

    $provider = Provider::find($providerId);

    $chain = [];

    foreach ($items as $current) {

        $chain[] = [

            'id' => $current->id,
            'name' => $current->name,
            'currency' => $current->currency,
            'amount' => $current->amount,
            'previous_amount' => optional(
                Contract::find($current->previous_contract_id)
            )->amount,
            'status' => $current->status,
            'status_label' => $current->status_label,
            'start_date' => $current->start_date->format('d/m/Y'),
            'end_date' => $current->end_date->format('d/m/Y'),
        ];
    }

    $timeline[] = [

        'provider' => $provider ? $provider->name : 'Sin proveedor',
        'service' => '',
        'contracts' => $chain
    ];
}
        return response()->json([

            'kpis' => $kpis,

            'calendar' => $calendar,

            'providers' => $providers,

            'nextExpirations' => $nextExpirations,
            'timeline' => $timeline
        ]);
    }
}