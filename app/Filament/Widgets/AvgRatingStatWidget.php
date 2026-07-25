<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Branch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AvgRatingStatWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $query = Booking::whereMonth('booking_date', $currentMonth)
            ->whereYear('booking_date', $currentYear)
            ->where('status', 'completed')
            ->whereNotNull('rating');

        if ($branchIds && $branchIds->isNotEmpty()) {
            $query->whereIn('branch_id', $branchIds);
        }

        $avgRating = $query->avg('rating');
        $totalReviews = $query->count();

        $ratingText = $avgRating ? round($avgRating, 1) . ' / 5.0' : '—';
        $description = $totalReviews > 0
            ? "Based on {$totalReviews} review" . ($totalReviews > 1 ? 's' : '') . " this month"
            : 'No reviews this month';

        $color = $avgRating ? ($avgRating >= 4.5 ? 'success' : ($avgRating >= 3.5 ? 'warning' : 'danger')) : 'gray';

        return [
            Stat::make('Avg. Rating (This Month)', $ratingText)
                ->description($description)
                ->descriptionIcon('heroicon-m-star')
                ->color($color)
                ->chart($this->getRatingTrend())
                ->url(route('filament.admin.pages.financial-report')),
        ];
    }

    protected function getRatingTrend(): array
    {
        $user = Auth::user();
        $branchIds = $user->hasRole('super_admin') ? null : $user->branches->pluck('id');

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $query = Booking::whereMonth('booking_date', $month->month)
                ->whereYear('booking_date', $month->year)
                ->where('status', 'completed')
                ->whereNotNull('rating');

            if ($branchIds && $branchIds->isNotEmpty()) {
                $query->whereIn('branch_id', $branchIds);
            }

            $avg = $query->avg('rating');
            $data[] = round($avg ?? 0, 1);
        }

        return $data;
    }
}