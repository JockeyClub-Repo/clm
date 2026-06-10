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
        }

        $contracts = $contracts->get();

        /*
        |--------------------------------------------------------------------------
        | KPIS
        |--------------------------------------------------------------------------
        */

        $kpis = [

            'total' => Contract::count(),

            'active' => Contract::where(
                'status',
                'active'
            )->count(),

            'renewed' => Contract::where(
                'status',
                'renewed'
            )->count(),

            'notRenewed' => Contract::where(
                'status',
                'not_renewed'
            )->count(),

            'expiring' => Contract::all()
                ->filter(function ($contract) {

                    return $contract->status_label ===
                        'Próximo a Vencer'

                        ||

                        $contract->status_label ===
                        'Renovación Pendiente'

                        ||

                        $contract->status_label ===
                        'Vence Hoy';

                })
                ->count()
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

            $roots = Contract::with('provider')
    ->whereNull('previous_contract_id')
    ->get();

$timeline = [];

foreach ($roots as $root) {

    $chain = [];

    $current = $root;

    while ($current) {

        $chain[] = [

            'id' => $current->id,
            'name' => $current->name,
            'currency' => $current->currency,
            'amount' => $current->amount,
            'previous_amount' => optional(
        Contract::find($current->previous_contract_id)
    )->amount,
            'status' => $current->status,
            'start_date' => $current->start_date->format('d/m/Y'),
            'end_date' => $current->end_date->format('d/m/Y'),
        ];

        $current = Contract::where(
            'previous_contract_id',
            $current->id
        )->first();
    }

    $timeline[] = [

        'provider' => $root->provider->name,

        // nombre de la línea
        'service' => $root->name,

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