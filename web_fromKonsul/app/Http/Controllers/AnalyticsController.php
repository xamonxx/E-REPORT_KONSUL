<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Consultation;
use App\Models\NeedsCategory;
use App\Models\StatusCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $selectedAccount = $request->get('account');
        $selectedMonth = $request->get('month', now()->month);
        $selectedYear = $request->get('year', now()->year);

        // Validasi month dan year untuk mencegah query error
        if ($selectedMonth < 1 || $selectedMonth > 12 || $selectedMonth > now()->month + 1) {
            $selectedMonth = now()->month;
        }
        if ($selectedYear < 2020 || $selectedYear > now()->year + 1) {
            $selectedYear = now()->year;
        }

        // Base query: scoped by user + optional account filter + period
        $query = Consultation::query()->forUser($user);

        if ($user->isSuperAdmin() && $selectedAccount) {
            $query->where('account_id', $selectedAccount);
        }

        $query->whereMonth('consultation_date', $selectedMonth)
              ->whereYear('consultation_date', $selectedYear);

        $totalLeads         = (clone $query)->count();
        $statusDistribution = $this->buildStatusDistribution($query);
        $needsDistribution  = $this->buildNeedsDistribution($query);
        $accountRanking     = $user->isSuperAdmin() ? $this->buildAccountRanking($selectedMonth, $selectedYear) : collect();
        $adminRanking       = $user->isSuperAdmin() ? $this->buildAdminRanking($selectedMonth, $selectedYear) : collect();

        $accounts = $user->isSuperAdmin() ? Account::orderBy('name')->get() : collect();
        $months = collect(range(1, 12))->map(fn($m) => ['value' => $m, 'label' => Carbon::create()->month($m)->translatedFormat('F')]);
        $years = collect(range(now()->year - 2, now()->year));

        return view('analytics.index', compact(
            'totalLeads', 'statusDistribution', 'needsDistribution', 'accountRanking', 'adminRanking',
            'accounts', 'months', 'years',
            'selectedAccount', 'selectedMonth', 'selectedYear'
        ));
    }

    /**
     * Distribusi lead per status category.
     */
    private function buildStatusDistribution($query)
    {
        $counts = (clone $query)
            ->selectRaw('status_category_id, count(*) as count')
            ->groupBy('status_category_id')
            ->pluck('count', 'status_category_id');

        return StatusCategory::orderBy('sort_order')->get()->map(fn($status) => [
            'name'  => $status->name,
            'color' => $status->color,
            'count' => $counts[$status->id] ?? 0,
        ]);
    }

    /**
     * Distribusi lead per needs category (hanya yang count > 0).
     */
    private function buildNeedsDistribution($query)
    {
        $counts = (clone $query)
            ->selectRaw('needs_category_id, count(*) as count')
            ->groupBy('needs_category_id')
            ->pluck('count', 'needs_category_id');

        return NeedsCategory::all()->map(fn($need) => [
            'name'  => $need->name,
            'count' => $counts[$need->id] ?? 0,
        ])->filter(fn($item) => $item['count'] > 0)->sortByDesc('count')->values();
    }

    /**
     * Ranking akun berdasarkan total leads & conversion rate ke survey.
     */
    private function buildAccountRanking(int $month, int $year)
    {
        $surveyStatusId = StatusCategory::where('name', config('statuses.survey', 'Masuk Survey'))->value('id');

        $query = Account::withCount([
            'consultations as total_leads' => function ($q) use ($month, $year) {
                $q->whereMonth('consultation_date', $month)
                  ->whereYear('consultation_date', $year);
            },
        ]);

        if ($surveyStatusId) {
            $query->withCount([
                'consultations as surveys_count' => function ($q) use ($month, $year, $surveyStatusId) {
                    $q->whereMonth('consultation_date', $month)
                      ->whereYear('consultation_date', $year)
                      ->where('status_category_id', $surveyStatusId);
                },
            ]);
        }

        return $query->get()->map(function ($account) {
            $total   = $account->total_leads ?? 0;
            $surveys = $account->surveys_count ?? 0;
            $rate    = $total > 0 ? round(($surveys / $total) * 100, 1) : 0;

            return ['name' => $account->name, 'total' => $total, 'surveys' => $surveys, 'rate' => $rate];
        })->sortByDesc('rate')->values();
    }

    /**
     * Ranking admin berdasarkan jumlah lead yang di-handle.
     */
    private function buildAdminRanking(int $month, int $year)
    {
        return User::where('role', UserRole::Admin)
            ->with('account')
            ->withCount(['consultations' => function ($q) use ($month, $year) {
                $q->whereMonth('consultation_date', $month)
                  ->whereYear('consultation_date', $year);
            }])
            ->get()
            ->map(fn($admin) => [
                'name'    => $admin->name,
                'account' => $admin->account?->name ?? '-',
                'total'   => $admin->consultations_count,
            ])->sortByDesc('total')->values();
    }
}
