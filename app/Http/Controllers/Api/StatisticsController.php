<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticsResource;

class StatisticsController extends Controller
{
    public function statistics(Request $request)
    {
        $user = $request->user();

        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date'))->startOfDay() 
            : Carbon::now()->subMonths(2)->startOfMonth();

        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date'))->endOfDay() 
            : Carbon::now()->endOfMonth();

        $monthType = $request->input('month_type', 'F'); 
        return response()->json([
            'success' => true,
            'message' => __('messages.statistics_fetched_successfully'),
            'data' => new StatisticsResource($user, $startDate, $endDate, $monthType),
        ], 200);
    }

}
