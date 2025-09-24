<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitLog\StoreHabitLogRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Habit $habit)
    {
        return response()->json($habit->logs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHabitLogRequest $request, Habit $habit)
    {
        $queryLog = $habit->logs();

        if ($habit->frequency === \App\Enums\FrequencyHabitEnum::DAILY) {
            $log = $queryLog->whereDate('date', now()->toDateString())->first();
        } elseif ($habit->frequency === \App\Enums\FrequencyHabitEnum::WEEKLY) {
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            
            $log = $queryLog
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->first();
        }

        $body = [
            'date' => now(),
            'is_done' => $request->is_done,
        ];

        if (!$log) {
            $log = $queryLog->create($body);
        } else {
            $log->update($body);
        }

        return response()->json($log, 201);
    }

    public function getStats()
    {
        $user = Auth::user();
        $habits = $user->habits()->with('logs')->get();

        $total_habits = $habits->count();

        // =========================
        // 🔥 Hoje
        // =========================
        $completed_today = $habits->filter(function ($habit) {
            return $habit->logs
                ->where('date', now()->toDateString())
                ->where('is_done', true)
                ->isNotEmpty();
        })->count();

        $completion_rate_today = $total_habits > 0
            ? round(($completed_today / $total_habits) * 100, 2)
            : 0;

        // =========================
        // 🔥 Streaks (dias consecutivos com pelo menos 1 hábito concluído)
        // =========================
        $streaks = 0;
        $date = now();
        while (true) {
            $hasLogs = HabitLog::whereHas('habit', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereDate('date', $date->toDateString())
                ->where('is_done', true)
                ->exists();

            if ($hasLogs) {
                $streaks++;
                $date->subDay();
            } else {
                break;
            }
        }

        // =========================
        // 🔥 Semana
        // =========================
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $logsWeek = HabitLog::whereHas('habit', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('is_done', true)
            ->get();

        $habits_completed_week = $logsWeek->count();

        // Agrupa por dia da semana para descobrir best/worst
        $logsPerDay = $logsWeek->groupBy(function ($log) {
            return Carbon::parse($log->date)->translatedFormat('l'); // Monday, Tuesday...
        });

        $best_day = $logsPerDay->sortByDesc->count()->keys()->first();
        $worst_day = $logsPerDay->sortBy->count()->keys()->first();

        $week_progress = $total_habits > 0
            ? round(($habits_completed_week / ($total_habits * 7)) * 100, 2)
            : 0;

        // =========================
        // 🔥 Mês
        // =========================
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $logsMonth = HabitLog::whereHas('habit', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_done', true)
            ->get();

        $habits_completed_month = $logsMonth->count();

        // Agrupa por hábito para achar mais/menos consistente
        $logsByHabit = $logsMonth->groupBy('habit_id');
        $most_consistent_habit = null;
        $least_consistent_habit = null;

        if ($logsByHabit->isNotEmpty()) {
            $most_consistent_habit = Habit::find(
                $logsByHabit->sortByDesc->count()->keys()->first()
            )?->title;

            $least_consistent_habit = Habit::find(
                $logsByHabit->sortBy->count()->keys()->first()
            )?->title;
        }

        $month_progress = $total_habits > 0
            ? round(($habits_completed_month / ($total_habits * now()->daysInMonth)) * 100, 2)
            : 0;

        // =========================
        // 🔥 Estatísticas por hábito
        // =========================
        $habitStats = $habits->map(function ($habit) use ($startOfWeek, $endOfWeek, $startOfMonth, $endOfMonth) {
            $logsWeek = $habit->logs->whereBetween('date', [$startOfWeek, $endOfWeek])
                                    ->where('is_done', true);
            $logsMonth = $habit->logs->whereBetween('date', [$startOfMonth, $endOfMonth])
                                    ->where('is_done', true);

            // calcula streak específico do hábito
            $currentStreak = 0;
            $longestStreak = 0;
            $date = now();
            while (true) {
                $log = $habit->logs
                    ->where('date', $date->toDateString())
                    ->where('is_done', true)
                    ->first();

                if ($log) {
                    $currentStreak++;
                    $date->subDay();
                } else {
                    break;
                }
            }
            $longestStreak = max($longestStreak, $currentStreak);

            return [
                'habit_id' => $habit->id,
                'title' => $habit->title,
                'completion_rate_week' => round(($logsWeek->count() / 7) * 100, 2),
                'completion_rate_month' => round(($logsMonth->count() / now()->daysInMonth) * 100, 2),
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
            ];
        });

        // =========================
        // 🔥 Retorno final
        // =========================
        return response()->json([
            'total_habits' => $total_habits,
            'completed_today' => $completed_today,
            'completion_rate_today' => $completion_rate_today,
            'streaks' => $streaks,
            'week' => [
                'habits_completed_week' => $habits_completed_week,
                'week_progress' => $week_progress,
                'best_day' => $best_day,
                'worst_day' => $worst_day,
            ],
            'month' => [
                'habits_completed_month' => $habits_completed_month,
                'month_progress' => $month_progress,
                'most_consistent_habit' => $most_consistent_habit,
                'least_consistent_habit' => $least_consistent_habit,
            ],
            'habits' => $habitStats,
        ]);
    }
}
