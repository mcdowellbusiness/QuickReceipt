<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Org;
use App\Models\OrgMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OrganizationOnboardingController extends Controller
{
    /**
     * Create a new user, organization, and assign user as admin
     */
    public function createUserAndOrganization(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|string|email|max:255|unique:users,email',
            'user_password' => 'required|string|min:8',
            'organization_name' => 'required|string|max:255',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->user_name,
            'email' => $request->user_email,
            'password' => Hash::make($request->user_password),
            'email_verified_at' => now(),
        ]);

        // Create the organization
        $organization = Org::create([
            'name' => $request->organization_name,
        ]);

        // Assign user as admin of the organization
        $orgMember = OrgMember::create([
            'org_id' => $organization->id,
            'user_id' => $user->id,
            'global_role' => 'admin',
        ]);

        return response()->json([
            'message' => 'User and organization created successfully',
            'user' => $user,
            'organization' => $organization,
            'org_membership' => $orgMember,
        ], 201);
    }
}
