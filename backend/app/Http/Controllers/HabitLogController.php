<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitLog\StoreHabitLogRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\Request;

class HabitLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    /**
     * Display the specified resource.
     */
    public function show(HabitLog $habitLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HabitLog $habitLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HabitLog $habitLog)
    {
        //
    }
}
