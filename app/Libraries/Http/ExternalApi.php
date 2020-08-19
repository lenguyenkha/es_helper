<?php

namespace App\Libraries\Http;

use App\Exceptions\ExternalAPIException;

interface ExternalApi
{
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
    public function callAPI(string $method, string $uriAPI, array $options = []);
}
