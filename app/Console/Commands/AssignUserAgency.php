<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignUserAgency extends Command
{
    protected $signature = 'user:assign-agency {email} {agency}';
    protected $description = 'Assign an agency to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $agency = $this->argument('agency');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        $user->agencia = $agency;
        $user->save();

        $this->info("Agency {$agency} assigned to {$email}");
    }
}
