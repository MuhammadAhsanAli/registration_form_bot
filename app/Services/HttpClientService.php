<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Service for handling HTTP requests using GuzzleHttp.
 */
class HttpClientService
{
    /**
     * @var Client The Guzzle HTTP client instance.
     */
    private Client $client;

    /**
     * @var CookieJar The cookie jar used to maintain cookies between requests.
     */
    private CookieJar $cookieJar;

    /**
     * Create a new HttpClientService instance.
     *
     * @param string $baseUrl The base URL for all HTTP requests made by this client.
     */
    public function __construct(string $baseUrl)
    {
        $this->cookieJar = new CookieJar();
        $this->client = new Client([
            'cookies' => $this->cookieJar,
            'base_uri' => $baseUrl
        ]);
    }

    /**
     * Perform an HTTP request.
     *
     * @param string $method The HTTP method to use (e.g., 'GET', 'POST').
     * @param string $url The URL to send the request to.
     * @param array $options Optional array of request options (e.g., headers, form_params).
     * @return ResponseInterface The response received from the HTTP request.
     * @throws GuzzleException If an error occurs during the HTTP request.
     * @throws Exception If the request fails or encounters an exception.
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $url, $options);
        } catch (RequestException $e) {
            throw new Exception('HTTP request failed: ' . $e->getMessage());
        }
    }
}
