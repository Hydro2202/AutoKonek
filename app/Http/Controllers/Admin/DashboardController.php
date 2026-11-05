<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const CHART_DAYS = 14;

    public function index(): View
    {
        // Every number here is counted from the database.
        $stats = [
            'users' => User::count(),
            'owners' => User::where('role', User::ROLE_OWNER)->count(),
            'establishments' => Establishment::count(),
            'pending' => Establishment::where('status', Establishment::STATUS_PENDING)->count(),
            'reviews' => Review::count(),
            'reports' => Report::where('status', Report::STATUS_PENDING)->count(),
        ];

        $weekAgo = now()->subDays(7);
        $newThisWeek = [
            'users' => User::where('created_at', '>=', $weekAgo)->count(),
            'establishments' => Establishment::where('created_at', '>=', $weekAgo)->count(),
            'reviews' => Review::where('created_at', '>=', $weekAgo)->count(),
        ];

        $pending = Establishment::where('status', Establishment::STATUS_PENDING)
            ->with(['owner', 'category'])->latest()->take(6)->get();
        $recentReports = Report::with(['reporter', 'establishment', 'review'])->latest()->take(5)->get();
        $recentReviews = Review::with(['user', 'establishment'])->latest()->take(5)->get();

        $activity = $this->activity();

        return view('admin.dashboard', compact('stats', 'newThisWeek', 'pending', 'recentReports', 'recentReviews', 'activity'));
    }

    /**
     * Daily counts for the last 14 days, oldest first.
     *
     * @return Collection<int, array{date: Carbon, users: int, reviews: int}>
     */
    private function activity(): Collection
    {
        $from = today()->subDays(self::CHART_DAYS - 1);

        $users = $this->perDay(User::class, $from);
        $reviews = $this->perDay(Review::class, $from);

        return collect(range(0, self::CHART_DAYS - 1))->map(function (int $offset) use ($from, $users, $reviews) {
            $date = $from->copy()->addDays($offset);
            $key = $date->toDateString();

            return ['date' => $date, 'users' => (int) ($users[$key] ?? 0), 'reviews' => (int) ($reviews[$key] ?? 0)];
        });
    }

    /**
     * @param  class-string<Model>  $model
     * @return Collection<string, int>
     */
    private function perDay(string $model, $from): Collection
    {
        return $model::query()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
    }
}
