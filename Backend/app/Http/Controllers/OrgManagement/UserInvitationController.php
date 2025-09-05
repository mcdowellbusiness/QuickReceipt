<?php

namespace App\Http\Controllers\OrgManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\OrgMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserInvitationController extends Controller
{
    /**
     * Invite an existing user to a team
     */
    public function inviteExistingUser(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is admin of the team
        $team = Team::findOrFail($request->team_id);
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to invite users'
            ], 403);
        }

        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_email' => 'required|email|exists:users,email',
            'team_role' => 'required|in:admin,member',
        ]);

        $invitedUser = User::where('email', $request->user_email)->first();
        
        // Check if user is already in the team
        $existingMembership = TeamMember::where('team_id', $team->id)
            ->where('user_id', $invitedUser->id)
            ->exists();

        if ($existingMembership) {
            return response()->json([
                'message' => 'User is already a member of this team'
            ], 400);
        }

        // Check if user is in the same organization
        $userOrgMembership = $user->orgMemberships()->first();
        $invitedUserOrgMembership = $invitedUser->orgMemberships()
            ->where('org_id', $userOrgMembership->org_id)
            ->exists();

        if (!$invitedUserOrgMembership) {
            return response()->json([
                'message' => 'User must be in the same organization to join this team'
            ], 400);
        }

        $teamMember = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $invitedUser->id,
            'team_role' => $request->team_role,
        ]);

        return response()->json([
            'message' => 'User invited to team successfully',
            'team_member' => $teamMember->load('user'),
        ], 201);
    }

    /**
     * Create a new user and add them to a team
     */
    public function createAndInviteUser(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is admin of the team
        $team = Team::findOrFail($request->team_id);
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to invite users'
            ], 403);
        }

        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:8',
            'team_role' => 'required|in:admin,member',
        ]);

        // Create the new user
        $newUser = User::create([
            'name' => $request->user_name,
            'email' => $request->user_email,
            'password' => Hash::make($request->user_password),
            'email_verified_at' => now(),
        ]);

        // Add user to the same organization
        $userOrgMembership = $user->orgMemberships()->first();
        OrgMember::create([
            'org_id' => $userOrgMembership->org_id,
            'user_id' => $newUser->id,
            'global_role' => 'member', // New users start as members
        ]);

        // Add user to the team
        $teamMember = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $newUser->id,
            'team_role' => $request->team_role,
        ]);

        return response()->json([
            'message' => 'User created and added to team successfully',
            'user' => $newUser,
            'team_member' => $teamMember->load('user'),
        ], 201);
    }

    /**
     * Remove a user from a team
     */
    public function removeFromTeam(Request $request, Team $team)
    {
        $user = Auth::user();
        
        // Check if user is admin of the team
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to remove users'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $teamMember = TeamMember::where('team_id', $team->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'message' => 'User is not a member of this team'
            ], 400);
        }

        $teamMember->delete();

        return response()->json([
            'message' => 'User removed from team successfully'
        ]);
    }

    /**
     * Update a user's role in a team
     */
    public function updateTeamRole(Request $request, Team $team)
    {
        $user = Auth::user();
        
        // Check if user is admin of the team
        $isTeamAdmin = $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();

        if (!$isTeamAdmin) {
            return response()->json([
                'message' => 'You must be a team admin to update user roles'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'team_role' => 'required|in:admin,member',
        ]);

        $teamMember = TeamMember::where('team_id', $team->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'message' => 'User is not a member of this team'
            ], 400);
        }

        $teamMember->update(['team_role' => $request->team_role]);

        return response()->json([
            'message' => 'User role updated successfully',
            'team_member' => $teamMember->load('user'),
        ]);
    }
}
