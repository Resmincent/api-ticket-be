<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatistic()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();


        $totalTicket = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->count();

        $activedTicket = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'open')
            ->count();

        $resolvedTicket = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->count();

        $avgResolutionTime = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', 'resolved')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_resolution_time'))
            ->value('avg_resolution_time') ?? 0;

        $statusDistribution = [
            'open' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->where('status', 'open')
                ->count(),
            'on_progress' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->where('status', 'on_progress')
                ->count(),
            'resolved' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->where('status', 'resolved')
                ->count(),
            'rejected' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->where('status', 'rejected')
                ->count(),
            'closed' => Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->where('status', 'closed')
                ->count(),

        ];

        $dashboardData = [
            'total_ticket' => $totalTicket,
            'actived_ticket' => $activedTicket,
            'resolved_ticket' => $resolvedTicket,
            'avg_resolution_time' => round($avgResolutionTime, 1),
            'status_distribution' => $statusDistribution,
        ];


        return response()->json([
            'message' => 'Dashboard statistics retrieved successfully',
            'data' => new DashboardResource($dashboardData),
        ]);
    }
}
