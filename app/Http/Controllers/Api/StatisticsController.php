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
        $camps = Camp::withCount(['families', 'projects'])->get();

        $data = $camps->map(function ($camp) {
            $contributions = 0;
            $totalProjects = $camp->projects_count;
            if ($totalProjects > 0) {
                $totalContributions = $camp->projects()->sum('contribution_percentage');
                $contributions = round($totalContributions / $totalProjects);
            }

            return [
                'id' => $camp->id,
                'name' => $camp->getTranslation('name', app()->getLocale()),
                'registered_families' => $camp->families_count,
                'current_projects' => $camp->projects_count,
                'contributions_percentage' => $contributions . '%',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => __('messages.camp_statistics_fetched'),
            'data' => $data,
        ]);
    }


}
