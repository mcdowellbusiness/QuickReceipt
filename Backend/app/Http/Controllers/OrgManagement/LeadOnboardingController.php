<?php

namespace App\Http\Controllers\OrgManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\OrgMember;
use App\Models\Invitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadOnboardingController extends Controller
{
    /**
     * Invite a team lead to the platform
     */
    public function inviteTeamLead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the current user and their organization
        $currentUser = $request->user();
        $orgMember = $currentUser->getOrganization();
        
        if (!$orgMember) {
            return response()->json([
                'message' => 'User is not a member of any organization'
            ], 403);
        }

        // Verify the team belongs to the user's organization
        $team = Team::where('id', $request->team_id)
                   ->where('org_id', $orgMember->org_id)
                   ->first();

        if (!$team) {
            return response()->json([
                'message' => 'Team not found or does not belong to your organization'
            ], 404);
        }

        // Check if user already exists
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // Check if user is already a member of this team
            $existingTeamMember = TeamMember::where('team_id', $team->id)
                                          ->where('user_id', $existingUser->id)
                                          ->first();
            
            if ($existingTeamMember) {
                return response()->json([
                    'message' => 'User is already a member of this team'
                ], 409);
            }

            // Add existing user to team as admin (lead)
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $existingUser->id,
                'team_role' => 'admin'
            ]);

            return response()->json([
                'message' => 'Existing user added to team as lead',
                'user' => $existingUser,
                'team' => $team
            ]);
        }

        // Check if there's already a pending invitation for this email and team
        $existingInvitation = Invitation::where('email', $request->email)
                                      ->where('team_id', $team->id)
                                      ->whereNull('accepted_at')
                                      ->where('expires_at', '>', now())
                                      ->first();

        if ($existingInvitation) {
            return response()->json([
                'message' => 'An invitation has already been sent to this email for this team'
            ], 409);
        }

        // Create invitation
        $invitation = Invitation::createInvitation(
            $request->email,
            $request->name,
            $team->id,
            $currentUser->id,
            'admin'
        );

        // Create a temporary user object for sending the notification
        $tempUser = new User();
        $tempUser->email = $request->email;
        $tempUser->name = $request->name;

        // Send invitation notification
        $tempUser->notify(new UserInvitationNotification($invitation));

        return response()->json([
            'message' => 'Team lead invitation sent successfully',
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'name' => $invitation->name,
                'team' => $team,
                'expires_at' => $invitation->expires_at
            ]
        ], 201);
    }

}
