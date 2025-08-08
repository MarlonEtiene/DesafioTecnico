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

        $companyName = $this->ask('Nome da empresa:');
        $userName = $this->ask('Nome do usuário:');
        $userEmail = $this->ask('Email do usuário:');
        $userPassword = $this->secret('Senha do usuário:');

        $company = Company::create([
            'name' => $companyName,
        ]);

        $user = User::create([
            'name' => $userName,
            'email' => $userEmail,
            'password' => Hash::make($userPassword),
            'company_id' => $company->id,
        ]);

        $this->info('Empresa e usuário criados com sucesso!');
        $this->info('ID da Empresa: ' . $company->id);
        $this->info('ID do usuário: ' . $user->id);
    }
}
