<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Family;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticsResource;
use App\Models\Camp;

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

    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'projectsCount' => Project::count(),
                'familiesCount' => Family::count(),
                'contributorsCount' => User::where('role', 'contributor')->count(),
                'CampsCount' => Camp::count(),
            ];

            return response()->json([
                'success' => true,
                'message' => __('messages.statistics_retrieved_successfully'),
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.failed_to_retrieve_statistics'),
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getCampStatistics(Request $request): JsonResponse
    {
        $camps = Camp::with(['projects' => function($query) {
            $query->where('is_approved', true);
        }, 'families'])->get();

        $data = $camps->map(function ($camp) {
            $registeredFamilies = $camp->families->count();
            $currentProjects = $camp->projects->count();
            $totalReceived = $camp->projects->sum('total_received');
            $totalQuantity = $camp->projects->sum('total_quantity');
            $contributionsPercentage = $totalQuantity > 0 ? round(($totalReceived / $totalQuantity) * 100) : 0;

            return [
                'id' => $camp->id,
                'name' => $camp->getTranslation('name', app()->getLocale()),
                'registeredFamilies' => $registeredFamilies,
                'currentProjects' => $currentProjects,
                'contributionsPercentage' => $contributionsPercentage . '%',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => __('messages.camp_statistics_fetched'),
            'data' => $data,
        ]);
    }



}
