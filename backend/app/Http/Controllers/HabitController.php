<?php

namespace App\Http\Controllers;

use App\Http\Requests\Habit\StoreHabitRequest;
use App\Models\Habit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $habits = Auth::user()->habits();

        $allowedSorts = ['id', 'title', 'description', 'frequency', 'created_at', 'updated_at'];

        if ($request->has('search')) {
            $habits->where('title', 'LIKE', '%' . $request->get('search') . '%');
        }

        if ($request->has('order')) {
            $orderStr = $request->get('order');
            $orderField = 'id'; // default
            $orderDirection = 'asc'; // default

            if (strpos($orderStr, ',')) {
                [$field, $direction] = explode(',', $orderStr);
                $orderField = in_array($field, $allowedSorts) ? $field : 'id';
                $orderDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            } else {
                $orderField = in_array($orderStr, $allowedSorts) ? $orderStr : 'id';
            }

            $habits->orderBy($orderField, $orderDirection);
        }

        return response()->json($habits->get());
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
        return response()->json($habit);
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
