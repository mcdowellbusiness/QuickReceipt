<?php

namespace App\Services;

use App\Exceptions\TeamException;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\AuthorizationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TeamService
{
    protected $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Create a new team for the authenticated user's organization
     */
    public function createTeam(User $user, array $data): Team
    {
        // Get user's organization (assuming they're admin of one org)
        $orgMembership = $this->authService->getUserOrgMembership($user);

        if (!$orgMembership) {
            throw new TeamException('You must be an organization admin to create teams', 403);
        }

        return DB::transaction(function () use ($user, $orgMembership, $data) {
            $team = Team::create([
                'org_id' => $orgMembership->org_id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // Add the creator as admin of the team
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'team_role' => 'admin',
            ]);

            return $team->load('members.user');
        });
    }

    /**
     * Get all teams for the authenticated user's organization
     */
    public function getAllTeams(User $user): Collection
    {
        $orgMembership = $this->authService->getUserOrgMembership($user);

        if (!$orgMembership) {
            throw new TeamException('You must be an organization admin to view teams', 403);
        }

        return Team::where('org_id', $orgMembership->org_id)
            ->with('members.user')
            ->get();
    }

    /**
     * Get a specific team with full details
     */
    public function getTeamDetails(User $user, Team $team): Team
    {
        if (!$this->authService->hasTeamAccess($user, $team)) {
            throw new TeamException('You do not have access to this team', 403);
        }

        return $team->load('members.user', 'budgets', 'transactions');
    }

    /**
     * Update a team (only team admins)
     */
    public function updateTeam(User $user, Team $team, array $data): Team
    {
        if (!$this->authService->isTeamAdmin($user, $team)) {
            throw new TeamException('You must be a team admin to update this team', 403);
        }

        $team->update([
            'name' => $data['name'] ?? $team->name,
            'description' => $data['description'] ?? $team->description,
        ]);

        return $team->load('members.user');
    }

    /**
     * Delete a team (only team admins)
     */
    public function deleteTeam(User $user, Team $team): bool
    {
        if (!$this->authService->isTeamAdmin($user, $team)) {
            throw new TeamException('You must be a team admin to delete this team', 403);
        }

        return DB::transaction(function () use ($team) {
            // Delete all team members first
            $team->members()->delete();
            
            // Then delete the team
            return $team->delete();
        });
    }


    /**
     * Validate team data
     */
    public function validateTeamData(array $data, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        $validator = validator($data, $rules);
        
        if ($validator->fails()) {
            throw new TeamException('Validation failed: ' . implode(', ', $validator->errors()->all()), 422);
        }

        return $validator->validated();
    }
}
