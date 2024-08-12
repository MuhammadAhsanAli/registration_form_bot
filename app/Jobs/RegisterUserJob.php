<?php

namespace App\Jobs;

use App\Services\UserRegistrationService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to register a user asynchronously.
 */
class RegisterUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array The user's credentials for registration.
     */
    private array $credentials;

    /**
     * Create a new job instance.
     *
     * @param array $credentials The user's credentials for registration.
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @param UserRegistrationService $registrationService The service to handle user registration.
     * @return void
     * @throws GuzzleException
     */
    public function handle(UserRegistrationService $registrationService): void
    {
        try {
            foreach ($this->credentials as $credential) {
                $registrationService->registerUser($credential);
            }
        } catch (\Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage());
        }
    }
}
