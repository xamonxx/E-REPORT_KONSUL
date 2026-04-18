<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Consultation;
use App\Models\NeedsCategory;
use App\Models\ReportAttendance;
use App\Models\StatusCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return $this->adminDashboard($user);
    }

    private function superAdminDashboard()
    {
        $cachedData = Cache::remember('dashboard:super_admin', 15 * 60, function () {
            return [
                ...$this->buildOverviewStats(),
                'topAdmin' => $this->findTopAdmin(),
                'accounts' => $this->buildAccountRanking(),
                'statusDistribution' => StatusCategory::withCount('consultations')->orderBy('sort_order')->get(),
                'needsDistribution' => NeedsCategory::withCount('consultations')->having('consultations_count', '>', 0)->orderByDesc('consultations_count')->get(),
                'recentConsultations' => Consultation::with(['account', 'statusCategory', 'needsCategory'])->latest()->take(5)->get(),
                'adminAttendances' => $this->buildAdminAttendances(),
                'today' => Carbon::today(),
            ];
        });

        return view('dashboard.super-admin', $cachedData);
    }

    private function buildOverviewStats(): array
    {
        $totalLeads = Consultation::count();
        $dealStatusId = $this->resolveStatusId('deal');

        $totalDeals = $dealStatusId
            ? Consultation::where('status_category_id', $dealStatusId)->count()
            : 0;
        $avgConversion = $totalLeads > 0 ? round(($totalDeals / $totalLeads) * 100, 1) : 0;

        $now = Carbon::now();
        $thisMonth = Consultation::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        $prev = $now->copy()->subMonth();
        $lastMonth = Consultation::whereMonth('created_at', $prev->month)->whereYear('created_at', $prev->year)->count();

        $growthPercent = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        return [
            'totalLeads' => $totalLeads,
            'activeAccounts' => Account::count(),
            'avgConversion' => $avgConversion,
            'growthPercent' => $growthPercent,
        ];
    }

    private function findTopAdmin(): ?User
    {
        $dealStatusId = $this->resolveStatusId('deal');

        if (!$dealStatusId) {
            return null;
        }

        $topAdmin = User::where('role', UserRole::Admin)
            ->withCount(['consultations as deal_count' => fn($q) => $q->where('status_category_id', $dealStatusId)])
            ->orderByDesc('deal_count')
            ->first();

        return ($topAdmin && $topAdmin->deal_count > 0) ? $topAdmin : null;
    }

    private function buildAccountRanking()
    {
        $dealStatusId = $this->resolveStatusId('deal');

        $query = Account::withCount(['consultations']);

        if ($dealStatusId) {
            $query->withCount(['consultations as deals_count' => fn($q) => $q->where('status_category_id', $dealStatusId)]);
        }

        return $query
            ->with(['admins:id,name,account_id'])
            ->get()
            ->sortByDesc('conversion_rate')
            ->values();
    }

    private function buildAdminAttendances()
    {
        $todayStr = Carbon::today()->format('Y-m-d');

        return User::where('role', UserRole::Admin)
            ->with(['account', 'reportAttendances' => fn($q) => $q->where('report_date', $todayStr)])
            ->get()
            ->map(function ($admin) {
                $attendance = $admin->reportAttendances->first();
                return (object) [
                    'admin' => $admin,
                    'account' => $admin->account,
                    'has_reported' => $attendance !== null,
                    'reported_at' => $attendance?->created_at,
                    'report_category' => $attendance?->report_category,
                ];
            });
    }

    private function adminDashboard(User $user)
    {
        $accountId = $user->account_id;

        if (!$accountId) {
            abort(403, 'Akun cabang belum di-assign ke user Anda. Hubungi Super Admin.');
        }

        // Cache admin dashboard per account for 5 minutes
        $cacheKey = "dashboard:admin:{$accountId}";
        $cachedData = Cache::remember($cacheKey, 5 * 60, function () use ($accountId) {
            $dealStatusId = $this->resolveStatusId('deal');
            $surveyStatusId = $this->resolveStatusId('survey');

            $totalLeads = Consultation::where('account_id', $accountId)->count();

            $totalDeals = $dealStatusId
                ? Consultation::where('account_id', $accountId)->where('status_category_id', $dealStatusId)->count()
                : 0;
            $conversionRate = $totalLeads > 0 ? round(($totalDeals / $totalLeads) * 100, 1) : 0;

            $pendingSurveys = $surveyStatusId
                ? Consultation::where('account_id', $accountId)->where('status_category_id', $surveyStatusId)->count()
                : 0;

            return [
                'account' => Account::findOrFail($accountId),
                'totalLeads' => $totalLeads,
                'conversionRate' => $conversionRate,
                'pendingSurveys' => $pendingSurveys,
                'statusDistribution' => StatusCategory::withCount(['consultations' => fn($q) => $q->where('account_id', $accountId)])
                    ->orderBy('sort_order')->get(),
                'needsDistribution' => NeedsCategory::withCount(['consultations' => fn($q) => $q->where('account_id', $accountId)])
                    ->having('consultations_count', '>', 0)->orderByDesc('consultations_count')->get(),
                'latestLeads' => Consultation::where('account_id', $accountId)
                    ->with(['statusCategory', 'needsCategory'])->latest()->take(5)->get(),
                'recentActivity' => Consultation::where('account_id', $accountId)
                    ->with(['statusCategory', 'needsCategory'])->latest('updated_at')->take(5)->get(),
            ];
        });

        $cachedData['hasReportedToday'] = ReportAttendance::where('user_id', $user->id)
            ->where('report_date', Carbon::today())->exists();

        return view('dashboard.admin', $cachedData);
    }

    /**
     * Resolve status category ID by config key.
     * Cached per-request to avoid repeated DB lookups.
     */
    private function resolveStatusId(string $configKey): ?int
    {
        static $cache = [];

        if (!isset($cache[$configKey])) {
            $statusName = config("statuses.{$configKey}");
            $cache[$configKey] = $statusName
                ? StatusCategory::where('name', $statusName)->value('id')
                : null;
        }

        return $cache[$configKey];
    }
}
