<?php

namespace App\Console\Commands;

use App\Jobs\RegisterUserJob;
use Illuminate\Console\Command;

/**
 * Command for registering users asynchronously.
 */
class RegisterUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:register-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registers users by dispatching a job to process user registration asynchronously.';

    /**
     * Execute the console command.
     *
     * This method handles the command execution logic.
     * It dispatches a job to register users with predefined credentials.
     * Multiple email IDs and their associated passwords can be added to the $userCredentials array
     * for IMAP fetching and retrieving codes dynamically from scripts.
     *
     * @return void
     */
    public function handle(): void
    {
        $userCredentials = [
            ['email' => 'user2@yahoo.com', 'password' => 'password2'],
            ['email' => 'user3@hotmail.com', 'password' => 'password3']
        ];

        RegisterUserJob::dispatch($userCredentials);
    }
}
