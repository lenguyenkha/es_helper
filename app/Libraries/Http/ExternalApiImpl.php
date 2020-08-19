<?php

namespace App\Libraries\Http;

use App\Exceptions\ExternalAPIException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class ExternalApiImpl
 *
 * @package App\Libraries\Http
 *
 * @author khaln <khaln@tech.est-rouge.com>
 */
class ExternalApiImpl implements ExternalApi
{
    /**
     * Client
     *
     * @var Client $client
     */
    public $client;

    /**
     * ExternalApiImpl constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Call external API
     *
     * @param string $method
     * @param string $uriAPI
     * @param array $options
     *
     * @return array|string
     *
     * @throws ExternalAPIException
     *
     * @author khaln <khaln@tech.est-rouge.com>
     */
    public function callAPI(string $method, string $uriAPI, array $options = [])
    {
        Log::debug("Call external api: Method {$method}, URI: {$uriAPI}, Options: " . json_encode($options));

        try {
            $response = $this->client->request($method, $uriAPI, $options);

            $this->handleException($response);
            if (preg_grep('/^application\/json/', $response->getHeader('Content-Type'))) {
                return $response = json_decode($response->getBody()->getContents(), true);
            }

            return $response->getBody()->getContents();
        } catch (GuzzleException $exception) {
            Log::debug("Have Exp " . get_class($exception) . ": Code {$exception->getCode()} , Message {$exception->getMessage()}");
            throw new ExternalAPIException(null, $exception->getMessage(), [], $exception);
        }
    }

    /**
     * Handle Exception
     *
     * @param Response $response
     *
     * @throws ExternalAPIException
     *
     * @author khaln <khaln@tech.est-rouge.com>
     */
    private function handleException(Response $response)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode != JsonResponse::HTTP_OK) {
            throw (new ExternalAPIException(null, "External api status code {$statusCode}"));
        }
    }
}
