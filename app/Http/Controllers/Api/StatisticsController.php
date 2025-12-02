<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Camp;
use App\Models\User;
use App\Models\Family;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CampStatisticsExport;
use App\Http\Resources\StatisticsResource;

class StatisticsController extends Controller
{
   
    public function statistics(Request $request)
    {
        $user = Auth::user();
        $camp = $user->camp;

        if (!$camp) {
            return response()->json([
                'success' => false,
                'message' => __('messages.camp_not_found'),
                'data' => null,
            ], 404);
        }

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
            'data' => new StatisticsResource($camp, $startDate, $endDate, $monthType),
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


    public function getCampStatistics(): JsonResponse
    {
        $camps = Camp::with([
            'projects' => function($query) {
                $query->where('is_approved', true)
                    ->with(['contributions' => function($q) {
                        $q->where('status', 'approved');
                    }]);
            },
            'families'
        ])->get();

        $data = $camps->map(function ($camp) {
            $registeredFamilies = $camp->families->count();
            $currentProjects = $camp->projects->count();

            $totalReceived = $camp->projects->sum(function ($project) {
                return $project->contributions->sum('total_quantity');
            });

            $totalCollege = $camp->projects->sum('college');

            $contributionsPercentage = $totalCollege > 0
                ? round(($totalReceived / $totalCollege) * 100)
                : 0;

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


    public function DelegateStatistics(Request $request)
    {
        $user = Auth::user();
        $camp = $user->camp;

        $oneWeekAgo = now()->subWeek();
        $oneMonthAgo = now()->subMonth(); 

        $projects = $camp->projects()->where('is_approved', true)->get();

        $currentProjects = $projects->where('status', '!=', 'delivered');
        $deliveredProjects = $projects->where('status', 'delivered');

        $currentProjectsLastWeek = $currentProjects->filter(fn($p) => $p->created_at >= $oneWeekAgo);
        $deliveredProjectsLastWeek = $deliveredProjects->filter(fn($p) => $p->created_at >= $oneWeekAgo);

        $familiesCount = $camp->families()->count();
        $familiesLastMonthCount = $camp->families()->where('created_at', '>=', $oneMonthAgo)->count();
        $familiesGrowthPercentage = $familiesCount > 0 
            ? round(($familiesLastMonthCount / $familiesCount) * 100, 1)
            : 0;

        $contributionsCount = $projects->sum(fn($p) => $p->contributions()->where('status', 'approved')->count());

        return response()->json([
            'success' => true,
            'message' => __('messages.camp_statistics_fetched'),
            'data' => [
                'currentProjects' => $currentProjects->count(),
                'currentProjectsLastWeek' => $currentProjectsLastWeek->count(),
                'currentProjectsLastWeekPercentage' => $currentProjects->count() > 0 
                    ? round(($currentProjectsLastWeek->count() / $currentProjects->count()) * 100) . '%'
                    : '0%',
                'deliveredProjects' => $deliveredProjects->count(),
                'deliveredProjectsLastWeek' => $deliveredProjectsLastWeek->count(),
                'deliveredProjectsLastWeekPercentage' => $deliveredProjects->count() > 0 
                    ? round(($deliveredProjectsLastWeek->count() / $deliveredProjects->count()) * 100) . '%'
                    : '0%',
                'familiesCount' => $familiesCount,
                'familiesGrowthPercentage' => $familiesGrowthPercentage . '%',
                'contributionsCount' => $contributionsCount,
            ],
        ]);
    }


    public function exportCampStatistics(Request $request)
    {
        $user = Auth::user();
        $camp = $user->camp;

        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date'))->startOfDay() 
            : Carbon::now()->subMonths(2)->startOfMonth();

        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date'))->endOfDay() 
            : Carbon::now()->endOfMonth();

        $monthType = $request->input('month_type', 'F');

        return Excel::download(
            new CampStatisticsExport($camp, $startDate, $endDate, $monthType),
            'camp_statistics.xlsx'
        );
    }

    public function ContributorStatistics(Request $request)
    {
        $user = Auth::user();
        $oneWeekAgo = now()->subWeek();
        $oneMonthAgo = now()->subMonth();

    
        $projects = $user->contributions()
            ->where('status', 'approved')
            ->with('project.camp')
            ->get()
            ->pluck('project')
            ->filter(fn($p) => $p && $p->is_approved);

        $data = $projects->groupBy('camp_id')->map(function($campProjects) use ($oneWeekAgo, $oneMonthAgo) {
            $camp = $campProjects->first()->camp;

            $currentProjects = $campProjects->where('status', '!=', 'delivered');
            $deliveredProjects = $campProjects->where('status', 'delivered');

            $currentProjectsLastWeek = $currentProjects->filter(fn($p) => $p->created_at >= $oneWeekAgo);
            $deliveredProjectsLastWeek = $deliveredProjects->filter(fn($p) => $p->created_at >= $oneWeekAgo);

            $familiesCount = $camp->families()->count();
            $familiesLastMonthCount = $camp->families()->where('created_at', '>=', $oneMonthAgo)->count();
            $familiesGrowthPercentage = $familiesCount > 0 
                ? round(($familiesLastMonthCount / $familiesCount) * 100, 1) 
                : 0;

            $contributionsCount = $campProjects->sum(fn($p) => $p->contributions()->where('status', 'approved')->where('user_id', auth()->id())->count());

            return [
                'campId' => $camp->id,
                'campName' => $camp->name,
                'currentProjects' => $currentProjects->count(),
                'currentProjectsLastWeek' => $currentProjectsLastWeek->count(),
                'currentProjectsLastWeekPercentage' => $currentProjects->count() > 0 
                    ? round(($currentProjectsLastWeek->count() / $currentProjects->count()) * 100) . '%'
                    : '0%',
                'deliveredProjects' => $deliveredProjects->count(),
                'deliveredProjectsLastWeek' => $deliveredProjectsLastWeek->count(),
                'deliveredProjectsLastWeekPercentage' => $deliveredProjects->count() > 0 
                    ? round(($deliveredProjectsLastWeek->count() / $deliveredProjects->count()) * 100) . '%'
                    : '0%',
                'familiesCount' => $familiesCount,
                'familiesGrowthPercentage' => $familiesGrowthPercentage . '%',
                'contributionsCount' => $contributionsCount,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => __('messages.contributor_statistics_fetched'),
            'data' => $data
        ]);
    }





}
