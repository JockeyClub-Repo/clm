<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function dashboardRouter()
  {
    $role = auth()->user()->role;
    return match ($role) {
      'admin' => $this->adminDashboard(),
      'agent' => $this->agentDashboard(),
      default => abort(403, 'Rol no autorizado.')
    };
  }

  public function adminDashboard()
  {
    return view('dashboard', [
      'view' => 'admin'
    ]);
  }

  public function agentDashboard()
  {
    return view('dashboard', [
      'view' => 'agent'
    ]);
  }

  public function adminData(Request $request)
  {
    $filter = $request->filter ?? 'active';
    $contracts = Contract::with('provider');

    switch ($filter) {
      case 'active': 
        $contracts->where('status', 'active')->whereDate('end_date', '>=', now());
        break;
      case 'expiring': 
        $contracts->where('status', 'active')->get()->filter(function ($contract) {
          $days = now()->diffInDays($contract->end_date, false);
          return $days <= $contract->renewal_notice_days && $days >= 0; 
        });
        break;
      case 'expired':
        $contracts->whereDate('end_date', '<', now());
        break;
        case 'not_renewed':
          $contracts->whereDate('end_date', '<', now())->where('auto_renewal', false);
        break;
      default:
        break;
    }

    /*
    |--------------------------------------------------------------------------
    | COLLECTION
    |--------------------------------------------------------------------------
    */

    if ($filter === 'expiring') {

        $contracts = Contract::with('provider')
            ->get()
            ->filter(function ($contract) {

                $days = now()
                    ->diffInDays($contract->end_date, false);

                return $days <= $contract->renewal_notice_days
                    && $days >= 0;
            });

    } else {

        $contracts = $contracts->get();
    }

    /*
    |--------------------------------------------------------------------------
    | KPIS
    |--------------------------------------------------------------------------
    */

    $totalContracts = Contract::count();

    $activeContracts = Contract::where('status', 'active')
        ->whereDate('end_date', '>=', now())
        ->count();

    $expiredContracts = Contract::whereDate('end_date', '<', now())
        ->count();

    $expiringSoon = Contract::get()
        ->filter(function ($contract) {

            $days = now()
                ->diffInDays($contract->end_date, false);

            return $days <= $contract->renewal_notice_days
                && $days >= 0;
        })
        ->count();

    /*
    |--------------------------------------------------------------------------
    | CALENDAR
    |--------------------------------------------------------------------------
    */

    $calendar = $contracts->map(function ($contract) {

        $days = now()
            ->diffInDays($contract->end_date, false);

        $color = '#34c38f';

        if ($days <= $contract->renewal_notice_days) {
            $color = '#f1b44c';
        }

        if ($days < 0) {
            $color = '#f46a6a';
        }

        return [
            'title' => $contract->name,
            'start' => $contract->end_date,
            'color' => $color,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | TIMELINE
    |--------------------------------------------------------------------------
    */

    $providers = $contracts
        ->groupBy(fn($c) => optional($c->provider)->name ?? 'Sin proveedor')
        ->map(function ($items, $providerName) {

            return [
                'provider' => $providerName,

                'contracts' => $items
                    ->sortBy('start_date')
                    ->map(function ($contract) {

                        $status = 'Activo';

                        if (now()->gt($contract->end_date)) {
                            $status = 'Vencido';
                        }

                        if (
                            now()->gt($contract->end_date)
                            && !$contract->auto_renewal
                        ) {
                            $status = 'No renovado';
                        }

                        return [
                            'name' => $contract->name,
                            'amount' => $contract->amount,
                            'currency' => $contract->currency,
                            'start_date' => $contract->start_date->format('Y-m-d'),
                            'end_date' => $contract->end_date->format('Y-m-d'),
                            'status' => $status,
                        ];
                    })->values()
            ];
        })->values();

    return response()->json([
        'kpis' => [
            'total' => $totalContracts,
            'active' => $activeContracts,
            'expired' => $expiredContracts,
            'expiring' => $expiringSoon,
        ],

        'calendar' => $calendar,

        'providersTimeline' => $providers,
    ]);
}
}