<?php

use Illuminate\Database\Seeder;
use App\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;


class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
       
        $name = $this->command->ask('What is the super admin name?');
        $mobile = $this->command->ask('What is the super admin mobile number?');
        $email = $this->command->ask('What is the super admin email address?');
        $password = $this->command->secret('What is the super admin password?');


        AdminUser::updateOrCreate([
            'email'   => $email,
        ],[
            'name' => $name,
            'mobile' => $mobile,
            'password' => Hash::make($password)
        ]);
        

        $this->command->info("Super Admin $name  was created");
    }
}
