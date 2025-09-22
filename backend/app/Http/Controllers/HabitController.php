<?php

namespace App\Http\Controllers;

use App\Http\Requests\Habit\StoreHabitRequest;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitController extends Controller
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
    public function store(StoreHabitRequest $request)
    {
        $body = $request->only('title', 'description', 'frequency');
        
        $habit = Habit::create([
            'user_id' => Auth::id(),
            ...$body,
        ]);

        return response()->json($habit, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Habit $habit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Habit $habit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habit $habit)
    {
        //
    }
}
