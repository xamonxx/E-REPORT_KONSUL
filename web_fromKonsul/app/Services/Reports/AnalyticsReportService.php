<?php

namespace App\Services\Reports;

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Consultation;
use App\Models\NeedsCategory;
use App\Models\StatusCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AnalyticsReportService
{
    public function __construct(
        private readonly ReportPeriodResolver $periodResolver,
    ) {
    }

    public function buildForUser(User $user, array $filters): array
    {
        $period = $this->periodResolver->resolve($filters);
        $selectedAccount = $user->isSuperAdmin() ? ($filters['account'] ?? null) : $user->account_id;

        $query = $this->baseQuery($user, $selectedAccount, $period['start'], $period['end']);

        $totalLeads = (clone $query)->count();
        $statusDistribution = $this->buildStatusDistribution($query);
        $needsDistribution = $this->buildNeedsDistribution($query);
        $provinceDistribution = $user->isSuperAdmin() ? $this->buildLocationDistribution($query, 'province') : collect();
        $cityDistribution = $user->isSuperAdmin() ? $this->buildLocationDistribution($query, 'city') : collect();
        $westJavaSegmentDistribution = $user->isSuperAdmin() ? $this->buildWestJavaSegmentDistribution($query) : collect();
        $accountRanking = $user->isSuperAdmin() ? $this->buildAccountRanking($period, $selectedAccount) : collect();
        $adminRanking = $user->isSuperAdmin() ? $this->buildAdminRanking($period, $selectedAccount) : collect();

        $totalSurveys = $this->countByStatusName($statusDistribution, config('statuses.survey', 'Masuk Survey'));
        $totalDeals = $this->countByStatusName($statusDistribution, config('statuses.deal', 'Selesai/Deal'));
        $conversionRate = $totalLeads > 0 ? round(($totalSurveys / $totalLeads) * 100, 1) : 0;
        $growthPercent = $this->buildGrowthPercent($user, $selectedAccount, $period);
        $rawRows = $this->buildRawRows($query);

        return [
            'period' => $period,
            'periodLabel' => $period['label'],
            'comparisonLabel' => $period['comparison_label'],
            'selectedAccount' => $user->isSuperAdmin() ? $selectedAccount : null,
            'selectedPeriodType' => $period['type'],
            'selectedWeekDate' => $period['anchor_date'],
            'selectedMonth' => $period['month'] ?? now()->month,
            'selectedYear' => $period['year'],
            'totalLeads' => $totalLeads,
            'totalSurveys' => $totalSurveys,
            'totalDeals' => $totalDeals,
            'conversionRate' => $conversionRate,
            'growthPercent' => $growthPercent,
            'statusDistribution' => $statusDistribution,
            'needsDistribution' => $needsDistribution,
            'provinceDistribution' => $provinceDistribution,
            'cityDistribution' => $cityDistribution,
            'westJavaSegmentDistribution' => $westJavaSegmentDistribution,
            'accountRanking' => $accountRanking,
            'adminRanking' => $adminRanking,
            'insights' => $this->buildInsights(
                $statusDistribution,
                $needsDistribution,
                $provinceDistribution,
                $accountRanking,
                $adminRanking,
                $growthPercent
            ),
            'rawRows' => $rawRows,
            'selectedAccountName' => $this->resolveAccountName($user, $selectedAccount),
        ];
    }

    private function baseQuery(User $user, ?int $selectedAccount, $start, $end): Builder
    {
        $query = Consultation::query()->forUser($user);

        if ($user->isSuperAdmin() && $selectedAccount) {
            $query->where('account_id', $selectedAccount);
        }

        return $query->whereBetween('consultation_date', [$start->toDateString(), $end->toDateString()]);
    }

    private function buildStatusDistribution(Builder $query): Collection
    {
        $counts = (clone $query)
            ->selectRaw('status_category_id, count(*) as count')
            ->groupBy('status_category_id')
            ->pluck('count', 'status_category_id');

        return StatusCategory::orderBy('sort_order')->get()->map(fn ($status) => [
            'name' => $status->name,
            'color' => $status->color,
            'count' => $counts[$status->id] ?? 0,
        ]);
    }

    private function buildNeedsDistribution(Builder $query): Collection
    {
        $counts = (clone $query)
            ->selectRaw('needs_category_id, count(*) as count')
            ->groupBy('needs_category_id')
            ->pluck('count', 'needs_category_id');

        return NeedsCategory::all()
            ->map(fn ($need) => [
                'name' => $need->name,
                'count' => $counts[$need->id] ?? 0,
            ])
            ->filter(fn ($item) => $item['count'] > 0)
            ->sortByDesc('count')
            ->values();
    }

    private function buildLocationDistribution(Builder $query, string $column, int $limit = 10): Collection
    {
        $items = (clone $query)
            ->whereNotNull($column)
            ->pluck($column);

        $distribution = $items->reduce(function (array $carry, $value) {
            $label = $this->cleanLocationLabel($value);

            if ($label === null) {
                return $carry;
            }

            $key = $this->normalizeLocation($label);

            if (! isset($carry[$key])) {
                $carry[$key] = [
                    'name' => $label,
                    'count' => 0,
                ];
            }

            $carry[$key]['count']++;

            return $carry;
        }, []);

        $total = array_sum(array_column($distribution, 'count')) ?: 1;

        return collect($distribution)
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->map(fn (array $item) => [
                'name' => $item['name'],
                'count' => $item['count'],
                'percentage' => round(($item['count'] / $total) * 100, 1),
            ]);
    }

    private function buildWestJavaSegmentDistribution(Builder $query): Collection
    {
        $segments = collect($this->westJavaSegments())->map(
            fn (array $config, string $name) => [
                'name' => $name,
                'count' => 0,
                'color' => $config['color'],
            ]
        );

        $rows = (clone $query)->get(['province', 'city']);

        foreach ($rows as $row) {
            $province = $this->normalizeLocation($row->province);
            $city = $this->normalizeLocation($row->city);

            if (! $this->isWestJavaLead($province, $city)) {
                continue;
            }

            $segmentName = $this->resolveWestJavaSegment($city) ?? 'Lainnya Jawa Barat';
            $segment = $segments->get($segmentName);
            $segment['count']++;
            $segments->put($segmentName, $segment);
        }

        return $segments->values();
    }

    private function buildAccountRanking(array $period, ?int $selectedAccount = null): Collection
    {
        $surveyStatusId = StatusCategory::where('name', config('statuses.survey', 'Masuk Survey'))->value('id');

        $query = Account::query();

        if ($selectedAccount) {
            $query->whereKey($selectedAccount);
        }

        $query->withCount([
            'consultations as total_leads' => function ($builder) use ($period) {
                $builder->whereBetween('consultation_date', [
                    $period['start']->toDateString(),
                    $period['end']->toDateString(),
                ]);
            },
        ]);

        if ($surveyStatusId) {
            $query->withCount([
                'consultations as surveys_count' => function ($builder) use ($period, $surveyStatusId) {
                    $builder->whereBetween('consultation_date', [
                        $period['start']->toDateString(),
                        $period['end']->toDateString(),
                    ])->where('status_category_id', $surveyStatusId);
                },
            ]);
        }

        return $query->get()->map(function ($account) {
            $total = $account->total_leads ?? 0;
            $surveys = $account->surveys_count ?? 0;
            $rate = $total > 0 ? round(($surveys / $total) * 100, 1) : 0;

            return [
                'name' => $account->name,
                'total' => $total,
                'surveys' => $surveys,
                'rate' => $rate,
            ];
        })->sortByDesc('rate')->values();
    }

    private function buildAdminRanking(array $period, ?int $selectedAccount = null): Collection
    {
        $query = User::where('role', UserRole::Admin)
            ->with('account')
            ->withCount([
                'consultations' => function ($builder) use ($period) {
                    $builder->whereBetween('consultation_date', [
                        $period['start']->toDateString(),
                        $period['end']->toDateString(),
                    ]);
                },
            ]);

        if ($selectedAccount) {
            $query->where('account_id', $selectedAccount);
        }

        return $query->get()
            ->map(fn ($admin) => [
                'name' => $admin->name,
                'account' => $admin->account?->name ?? '-',
                'total' => $admin->consultations_count,
            ])
            ->sortByDesc('total')
            ->values();
    }

    private function buildRawRows(Builder $query): Collection
    {
        return (clone $query)
            ->with(['account', 'needsCategory', 'statusCategory', 'creator'])
            ->orderBy('consultation_date', 'desc')
            ->get()
            ->map(fn ($consultation) => [
                'consultation_id' => $consultation->consultation_id,
                'client_name' => $consultation->client_name,
                'phone' => $consultation->phone,
                'province' => $consultation->province,
                'city' => $consultation->city,
                'account' => $consultation->account?->name,
                'need' => $consultation->needsCategory?->name,
                'status' => $consultation->statusCategory?->name,
                'notes' => $consultation->notes,
                'consultation_date' => $consultation->consultation_date?->format('d/m/Y'),
                'creator' => $consultation->creator?->name,
                'updated_at' => $consultation->updated_at?->format('d/m/Y H:i'),
            ]);
    }

    private function buildGrowthPercent(User $user, ?int $selectedAccount, array $period): float
    {
        $current = $this->baseQuery($user, $selectedAccount, $period['start'], $period['end'])->count();
        $previous = $this->baseQuery(
            $user,
            $selectedAccount,
            $period['comparison_start'],
            $period['comparison_end']
        )->count();

        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    private function buildInsights(
        Collection $statusDistribution,
        Collection $needsDistribution,
        Collection $provinceDistribution,
        Collection $accountRanking,
        Collection $adminRanking,
        float $growthPercent
    ): Collection {
        $insights = collect();

        if ($topStatus = $statusDistribution->sortByDesc('count')->first()) {
            $insights->push(sprintf(
                'Status terbanyak pada periode ini adalah %s dengan %s konsultasi.',
                $topStatus['name'],
                number_format($topStatus['count'])
            ));
        }

        if ($topNeed = $needsDistribution->first()) {
            $insights->push(sprintf(
                'Kategori kebutuhan teratas adalah %s dengan %s lead.',
                $topNeed['name'],
                number_format($topNeed['count'])
            ));
        }

        if ($topProvince = $provinceDistribution->first()) {
            $insights->push(sprintf(
                'Wilayah terkuat datang dari %s dengan kontribusi %s%%.',
                $topProvince['name'],
                $topProvince['percentage']
            ));
        }

        if ($topAccount = $accountRanking->first()) {
            $insights->push(sprintf(
                'Akun dengan rasio survey terbaik adalah %s di angka %s%%.',
                $topAccount['name'],
                $topAccount['rate']
            ));
        }

        if ($topAdmin = $adminRanking->first()) {
            $insights->push(sprintf(
                'Admin dengan volume input tertinggi adalah %s dengan %s lead.',
                $topAdmin['name'],
                number_format($topAdmin['total'])
            ));
        }

        $direction = $growthPercent >= 0 ? 'naik' : 'turun';
        $insights->push(sprintf(
            'Jumlah konsultasi %s %s%% dibanding periode pembanding.',
            $direction,
            abs($growthPercent)
        ));

        return $insights->filter()->values();
    }

    private function resolveAccountName(User $user, ?int $selectedAccount): string
    {
        if ($user->isSuperAdmin()) {
            if (! $selectedAccount) {
                return 'Semua Akun';
            }

            return Account::whereKey($selectedAccount)->value('name') ?? 'Akun Tidak Ditemukan';
        }

        return $user->account?->name ?? 'Akun Admin';
    }

    private function countByStatusName(Collection $statusDistribution, string $statusName): int
    {
        return (int) ($statusDistribution->firstWhere('name', $statusName)['count'] ?? 0);
    }

    private function cleanLocationLabel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $label = trim(preg_replace('/\s+/', ' ', $value));

        return $label !== '' ? $label : null;
    }

    private function normalizeLocation(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish();
    }

    private function isWestJavaLead(string $province, string $city): bool
    {
        if (str_contains($province, 'jawa barat') || str_contains($province, 'jabar')) {
            return true;
        }

        return $this->resolveWestJavaSegment($city) !== null;
    }

    private function resolveWestJavaSegment(string $normalizedCity): ?string
    {
        if ($normalizedCity === '') {
            return null;
        }

        foreach ($this->westJavaSegments() as $segmentName => $config) {
            foreach ($config['aliases'] as $alias) {
                if (str_contains($normalizedCity, $alias)) {
                    return $segmentName;
                }
            }
        }

        return null;
    }

    private function westJavaSegments(): array
    {
        return [
            'Bandung Raya' => [
                'aliases' => ['bandung barat', 'kbb', 'cimahi', 'kab bandung', 'kabupaten bandung', 'bandung'],
                'color' => '#2563eb',
            ],
            'Jabar Timur' => [
                'aliases' => ['pangandaran', 'ciamis', 'banjar', 'tasikmalaya', 'tasik', 'garut'],
                'color' => '#16a34a',
            ],
            'Jabar Pantura' => [
                'aliases' => ['indramayu', 'cirebon', 'kuningan', 'sumedang', 'majalengka', 'subang', 'purwakarta'],
                'color' => '#f59e0b',
            ],
            'Jabar Kulon' => [
                'aliases' => ['sukabumi', 'cianjur'],
                'color' => '#7c3aed',
            ],
            'Jabodetabek' => [
                'aliases' => ['bogor', 'depok', 'bekasi', 'karawang'],
                'color' => '#ef4444',
            ],
            'Lainnya Jawa Barat' => [
                'aliases' => [],
                'color' => '#64748b',
            ],
        ];
    }
}
