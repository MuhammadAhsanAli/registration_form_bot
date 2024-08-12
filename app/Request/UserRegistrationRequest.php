<?php

namespace App\Request;

use App\Services\HttpClientService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Handles requests for user registration.
 */
class UserRegistrationRequest
{
    /**
     * @var HttpClientService Service to handle HTTP requests.
     */
    private HttpClientService $httpClientService;

    /**
     * @var string The base URL for the user registration process.
     */
    private string $baseUrl;

    /**
     * @var string The URL reference used in request headers.
     */
    private string $referenceUrl;

    /**
     * UserRegistrationRequest constructor.
     */
    public function __construct()
    {
        $this->baseUrl = env('USER_REGISTRATION');
        $this->httpClientService = new HttpClientService($this->baseUrl);
        $this->referenceUrl = $this->baseUrl;
    }

    /**
     * Generate a request token.
     *
     * @return void
     * @throws Exception
     * @throws GuzzleException
     */
    public function generateRequestToken(): void
    {
        $this->httpClientService->request("GET", $this->baseUrl);
    }

    /**
     * Retrieve the registration page content.
     *
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    public function getRegisterPage(): string
    {
        $endpoint = 'register.php';
        $response = $this->httpClientService->request("GET", $endpoint);
        $this->referenceUrl = $endpoint;

        return (string) $response->getBody();
    }

    /**
     * Submit form data to a specified endpoint.
     *
     * @param string $endpoint
     * @param array $data
     * @return string
     * @throws GuzzleException
     * @throws Exception
     */
    public function postFormData(string $endpoint, array $data): string
    {
        $response = $this->httpClientService->request(
            "POST",
            $endpoint,
            [
                'form_params' => $data,
                'headers' => $this->getHeaders($this->referenceUrl),
            ]
        );
        $this->referenceUrl = $endpoint;

        return (string) $response->getBody();
    }

    /**
     * Generate the required headers for the HTTP request.
     *
     * @param string $referenceUrl
     * @return array
     */
    private function getHeaders(string $referenceUrl): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Referer' => $this->baseUrl . '/' . $referenceUrl,
        ];
    }
}
