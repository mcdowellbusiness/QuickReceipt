<?php

namespace App\Http\Controllers\OrgManagement;

use App\Exceptions\TeamException;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }
    /**
     * Create a new team for the authenticated user's organization
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Validate the request data
            $validatedData = $this->teamService->validateTeamData($request->all());
            
            // Create the team using the service
            $team = $this->teamService->createTeam($user, $validatedData);

            return response()->json([
                'message' => 'Team created successfully',
                'team' => $team,
            ], 201);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * List all teams for the authenticated user's organization
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $teams = $this->teamService->getAllTeams($user);

            return response()->json($teams);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Show a specific team
     */
    public function show(Request $request, Team $team)
    {
        try {
            $user = $request->user();
            
            $teamDetails = $this->teamService->getTeamDetails($user, $team);

            return response()->json($teamDetails);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Update a team (only team admins)
     */
    public function update(Request $request, Team $team)
    {
        try {
            $user = $request->user();
            
            // Validate the request data
            $validatedData = $this->teamService->validateTeamData($request->all(), true);
            
            // Update the team using the service
            $updatedTeam = $this->teamService->updateTeam($user, $team, $validatedData);

            return response()->json([
                'message' => 'Team updated successfully',
                'team' => $updatedTeam,
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }

    /**
     * Delete a team (only team admins)
     */
    public function destroy(Request $request, Team $team)
    {
        try {
            $user = $request->user();
            
            // Delete the team using the service
            $this->teamService->deleteTeam($user, $team);

            return response()->json([
                'message' => 'Team deleted successfully'
            ]);
        } catch (TeamException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getStatusCode());
        }
    }
}
