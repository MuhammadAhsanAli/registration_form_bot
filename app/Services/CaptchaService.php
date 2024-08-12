<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service for CAPTCHA operations.
 */
class CaptchaService
{
    /**
     * @var HttpClientService Service to handle HTTP requests.
     */
    private HttpClientService $httpClientService;

    /**
     * @var string The API key for accessing the CAPTCHA service.
     */
    private string $apiKey;

    /**
     * Create a new instance of CaptchaService.
     */
    public function __construct()
    {
        $this->apiKey = env('CAPTCHA_API_KEY');
        $this->httpClientService = new HttpClientService(env('CAPTCHA_API_URL'));
    }

    /**
     * Solve CAPTCHA using the 2Captcha API.
     *
     * @param string $siteKey The site key of the CAPTCHA to be solved.
     * @param string $pageUrl The URL of the page where the CAPTCHA is located.
     * @return string The solution to the CAPTCHA.
     */
    public function solveCaptcha(string $siteKey, string $pageUrl): string
    {
        Log::info("Request begin to fetch Captcha. Site Key is " . $siteKey);
        $captchaId = $this->requestCaptchaId($siteKey, $pageUrl);
        Log::info("Captcha ID: " . $captchaId);
        $response = $this->pollForCaptchaResult($captchaId);
        Log::info("Captcha Solution Code: ");
        Log::info($response);
        return $response;
    }

    /**
     * Request CAPTCHA ID from the 2Captcha API.
     *
     * @param string $siteKey The site key of the CAPTCHA.
     * @param string $pageUrl The URL of the page where the CAPTCHA is located.
     * @return string The ID of the CAPTCHA request.
     */
    private function requestCaptchaId(string $siteKey, string $pageUrl): string
    {
        $response = $this->httpClientService->request('POST', 'in.php', [
            'form_params' => [
                'key' => $this->apiKey,
                'method' => 'userrecaptcha',
                'googlekey' => $siteKey,
                'pageurl' => $pageUrl,
                'json' => 1,
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);
        return $result['request'];
    }

    /**
     * Poll for CAPTCHA result until solved.
     *
     * @param string $captchaId The ID of the CAPTCHA request.
     * @return string The solution to the CAPTCHA.
     */
    private function pollForCaptchaResult(string $captchaId): string
    {
        do {
            sleep(50);
            $response = $this->httpClientService->request('GET', 'res.php', [
                'query' => [
                    'key' => $this->apiKey,
                    'action' => 'get',
                    'id' => $captchaId,
                    'json' => 1,
                ],
            ]);

            $result = json_decode((string) $response->getBody(), true);
        } while ($result['status'] != 1);

        return $result['request'];
    }
}
