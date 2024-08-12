<?php

namespace App\Services;

use App\Request\UserRegistrationRequest;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for handling user registration operations.
 */
class UserRegistrationService
{
    /**
     * @var CaptchaService Service to handle captcha-related functionality.
     */
    private CaptchaService $captchaService;

    /**
     * @var EmailOtpService Service to handle email OTP-related functionality.
     */
    private EmailOtpService $emailOtpService;

    /**
     * @var UserRegistrationRequest Service to manage user registration requests.
     */
    private UserRegistrationRequest $userRegistrationRequest;

    /**
     * UserRegistrationRequest constructor.
     */
    public function __construct(
        CaptchaService $captchaService,
        EmailOtpService $emailOtpService,
        UserRegistrationRequest $userRegistrationRequest
    ) {
        $this->captchaService = $captchaService;
        $this->emailOtpService = $emailOtpService;
        $this->userRegistrationRequest = $userRegistrationRequest;
    }

    /**
     * Register a new user.
     *
     * @param array $credentials
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function registerUser(array $credentials): void
    {
        $this->userRegistrationRequest->generateRequestToken();

        $registerPage = $this->userRegistrationRequest->getRegisterPage();
        Log::info("Registered Page Fetched: " . $registerPage);

        $verifyPage = $this->submitRegistration(
            $credentials,
            $this->getStoken($registerPage),
            $this->getActionUrl($registerPage)
        );
        Log::info("Register Page submission response: " . $verifyPage);

        $captchaPageUrl = $this->getActionUrl($verifyPage);
        $captchaPage = $this->submitVerification(
            $this->fetchOtp($credentials),
            $captchaPageUrl
        );
        Log::info("Verify Page submission response: " . $captchaPage);

        $completePage = $this->submitCaptcha(
            $this->captchaService->solveCaptcha(
                $this->getSiteKey($captchaPage),
                $captchaPageUrl
            ),
            $this->getActionUrl($captchaPage)
        );

        Log::info("Task has been completed: " . $completePage);
    }

    /**
     * Submit the registration form.
     *
     * @param array $credentials
     * @param string $sToken
     * @param string $actionUrl
     * @return string
     * @throws GuzzleException
     */
    private function submitRegistration(array $credentials, string $sToken, string $actionUrl): string
    {
        $response = $this->userRegistrationRequest->postFormData(
            $actionUrl,
            [
                'stoken' => $sToken,
                'fullname' => Str::random(5),
                'email' => $credentials['email'],
                'password' => Str::random(8),
                'email_signature' => base64_encode($credentials['email']),
            ]
        );
        Log::info("User registered successfully.");

        return $response;
    }

    /**
     * Submit the verification form.
     *
     * @param string $otp
     * @param string $actionUrl
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    private function submitVerification(string $otp, string $actionUrl): string
    {
        $response = $this->userRegistrationRequest->postFormData(
            $actionUrl,
            [
                'code' => $otp,
            ]
        );
        Log::info("Email verification code submitted successfully. OTP: " . $otp);

        return $response;
    }

    /**
     * Submit the captcha form.
     *
     * @param string $code
     * @param string $actionUrl
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    private function submitCaptcha(string $code, string $actionUrl): string
    {
        $response = $this->userRegistrationRequest->postFormData(
            $actionUrl,
            [
                'g-recaptcha-response' => $code,
            ]
        );
        Log::info("Captcha submitted successfully.");

        return $response;
    }

    /**
     * Extract the sToken from the page content.
     *
     * @param string $page
     * @return string
     * @throws Exception
     */
    private function getStoken(string $page): string
    {
        preg_match('/name="stoken" value="(.+?)"/', $page, $matches);
        return $matches[1] ?? throw new Exception("Failed to extract sToken.");
    }

    /**
     * Extract the action URL from the page content.
     *
     * @param string $page
     * @return string
     * @throws Exception
     */
    private function getActionUrl(string $page): string
    {
        preg_match('/<form[^>]*action="([^"]+)"/', $page, $matches);
        Log::info("Action URL: " . $matches[1] ?? null);
        return $matches[1] ?? throw new Exception("Failed to extract action URL.");
    }

    /**
     * Fetch OTP from the user's email.
     *
     * @param array $credentials
     * @return string
     * @throws Exception
     */
    private function fetchOtp(array $credentials): string
    {
        $emailOtp = $this->emailOtpService->fetchOtpFromEmail($credentials['email'], $credentials['password']);
        return $emailOtp ?: throw new Exception("Failed to fetch OTP.");
    }

    /**
     * Extract the site key from the page content.
     *
     * @param string $page
     * @return string
     * @throws Exception
     */
    private function getSiteKey(string $page): string
    {
        preg_match('/data-sitekey="([^"]+)"/', $page, $matches);
        return $matches[1] ?? throw new Exception("Failed to extract site key.");
    }
}
