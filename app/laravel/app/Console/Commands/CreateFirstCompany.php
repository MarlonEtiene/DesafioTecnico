<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateFirstCompany extends Command
{
    protected $signature = 'company:create-first';
    protected $description = 'Create the first company and user';

    public function handle()
    {
        if (Company::count() > 0) {
            $this->error('Uma empresa já existe. Este comando só pode ser executado uma vez.');
            return;
        }

        $companyName = $this->ask('Enter company name:');
        $userName = $this->ask('Enter user name:');
        $userEmail = $this->ask('Enter user email:');
        $userPassword = $this->secret('Enter user password:');

        $company = Company::create([
            'name' => $companyName,
        ]);

        $user = User::create([
            'name' => $userName,
            'email' => $userEmail,
            'password' => Hash::make($userPassword),
            'company_id' => $company->id,
        ]);

        $this->info('Company and user created successfully!');
        $this->info('Company ID: ' . $company->id);
        $this->info('User ID: ' . $user->id);
    }
}
