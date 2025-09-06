<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invitation;
use App\Models\TeamMember;
use App\Models\OrgMember;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // If invitation token is provided, make email optional in validation
        if ($request->has('invitation_token')) {
            $validationRules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255'];
        }

        $request->validate($validationRules);

        // Handle invitation-based registration
        if ($request->has('invitation_token')) {
            return $this->handleInvitationRegistration($request);
        }

        // Handle regular registration
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return response()->noContent();
    }

    /**
     * Handle registration with invitation token
     */
    private function handleInvitationRegistration(Request $request): Response
    {
        $invitation = Invitation::where('token', $request->invitation_token)
                               ->whereNull('accepted_at')
                               ->where('expires_at', '>', now())
                               ->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Invalid or expired invitation token'
            ], 400);
        }

        // Verify email matches invitation
        if ($invitation->email !== $request->email) {
            return response()->json([
                'message' => 'Email does not match invitation'
            ], 400);
        }

        return DB::transaction(function () use ($request, $invitation) {
            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->string('password')),
            ]);

            // Add user to organization
            OrgMember::create([
                'org_id' => $invitation->team->org_id,
                'user_id' => $user->id,
                'global_role' => 'member'
            ]);

            // Add user to team with the role from invitation
            TeamMember::create([
                'team_id' => $invitation->team_id,
                'user_id' => $user->id,
                'team_role' => $invitation->role
            ]);

            // Mark invitation as accepted
            $invitation->update(['accepted_at' => now()]);

            event(new Registered($user));
            Auth::login($user);

            return response()->json([
                'message' => 'Account created successfully',
                'user' => $user,
                'team' => $invitation->team
            ], 201);
        });
    }
}
