<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassHall;
use Throwable;

class InstituteHallController extends Controller
{
    public function fetchInstituteHall()
    {
        try {

            $halls = ClassHall::where('is_active', true)
                ->orderBy('hall_name')
                ->get(['id', 'hall_name']);

            return response()->json([
                'success' => true,
                'data' => $halls
            ]);
        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
