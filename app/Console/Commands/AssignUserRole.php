<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    protected $signature = 'user:assign-role {email} {role}';
    protected $description = 'Assign a role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("Role {$roleName} not found");
            return;
        }

        if (!$user->hasRole($roleName)) {
            $user->assignRole($roleName);
            $this->info("Role {$roleName} assigned to {$email}");
        } else {
            $this->info("User already has role {$roleName}");
        }
    }
}

/*
class AssignUserRole extends Command
{
    protected $signature = 'user:assign-role {email} {role}';
    protected $description = 'Assign a role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        if (!$user->hasRole($roleName)) {
            $user->assignRole($roleName);
            $this->info("Role {$roleName} assigned to {$email}");
        } else {
            $this->info("User already has role {$roleName}");
        }
    }
}
*/
