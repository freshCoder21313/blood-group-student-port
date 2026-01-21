<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAspToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asp:create-token {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an API token for the ASP system with sync abilities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'ASP System',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        // Revoke existing tokens to prevent accumulation
        $user->tokens()->delete();

        $token = $user->createToken('ASP System Token', ['asp:sync']);

        $this->info("Token created for user: {$email}");
        $this->info("Token: " . $token->plainTextToken);
        
        return 0;
    }
}
