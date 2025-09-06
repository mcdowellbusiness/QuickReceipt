<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;

class AuthorizationService
{
    /**
     * Check if user has access to team
     */
    public function hasTeamAccess(User $user, Team $team): bool
    {
        return $user->teamMemberships()
            ->where('team_id', $team->id)
            ->exists();
    }

    /**
     * Check if user is team admin
     */
    public function isTeamAdmin(User $user, Team $team): bool
    {
        return $user->teamMemberships()
            ->where('team_id', $team->id)
            ->where('team_role', 'admin')
            ->exists();
    }

    /**
     * Check if user is organization admin
     */
    public function isOrgAdmin(User $user, int $orgId): bool
    {
        return $user->orgMemberships()
            ->where('org_id', $orgId)
            ->where('global_role', 'admin')
            ->exists();
    }

    /**
     * Check if user can manage teams (team admin or org admin)
     */
    public function canManageTeams(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team) || $this->isOrgAdmin($user, $team->org_id);
    }

    /**
     * Check if user can manage budgets (team admin or org admin)
     */
    public function canManageBudgets(User $user, Team $team): bool
    {
        return $this->isTeamAdmin($user, $team) || $this->isOrgAdmin($user, $team->org_id);
    }

    /**
     * Get user's organization membership for team operations
     */
    public function getUserOrgMembership(User $user)
    {
        return $user->orgMemberships()
            ->where('global_role', 'admin')
            ->with('org')
            ->first();
    }
}
