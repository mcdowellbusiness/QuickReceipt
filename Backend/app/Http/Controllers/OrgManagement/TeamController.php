<?php

namespace App\Http\Controllers\OrgManagement;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Create a new team for the authenticated user's organization
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Get user's organization (assuming they're admin of one org)
        $orgMembership = $user->orgMemberships()
            ->where('global_role', 'admin')
            ->with('org')
            ->first();

        if (!$orgMembership) {
            return response()->json([
                'message' => 'You must be an organization admin to create teams'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = Team::create([
            'org_id' => $orgMembership->org_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Add the creator as admin of the team
        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'team_role' => 'admin',
        ]);

        return response()->json([
            'message' => 'Team created successfully',
            'team' => $team->load('members.user'),
        ], 201);
    }

    /**
     * List all teams for the authenticated user's organization
     */
    public function index()
    {
        $user = Auth::user();
        
        $orgMembership = $user->orgMemberships()
            ->where('global_role', 'admin')
            ->with('org')
            ->first();

        if (!$orgMembership) {
            return response()->json([
                'message' => 'You must be an organization admin to view teams'
            ], 403);
        }

        $teams = Team::where('org_id', $orgMembership->org_id)
            ->with('members.user')
            ->get();

        return response()->json($teams);
    }

    /**
     * Show a specific team
     */
    public function show(Team $team)
    {
        $user = Auth::user();
        
        // Check if user has access to this team
        $hasAccess = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'message' => 'You do not have access to this team'
            ], 403);
        }

        return response()->json($team->load('members.user', 'budgets', 'transactions'));
    }

    /**
     * Update a team (only team admins)
     */
    public function update(Request $request, Team $team)
    {
        $user = Auth::user();
        
        // Check if user is admin of this team
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to update this team'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update($request->only(['name', 'description']));

        return response()->json([
            'message' => 'Team updated successfully',
            'team' => $team->load('members.user'),
        ]);
    }

    /**
     * Delete a team (only team admins)
     */
    public function destroy(Team $team)
    {
        $user = Auth::user();
        
        // Check if user is admin of this team
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to delete this team'
            ], 403);
        }

        $team->delete();

        return response()->json([
            'message' => 'Team deleted successfully'
        ]);
    }
}
