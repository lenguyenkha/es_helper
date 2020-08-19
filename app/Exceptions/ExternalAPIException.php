<?php


namespace App\Exceptions;

/**
 * Class ExternalAPIException
 *
 * @package App\Exceptions
 */
class ExternalAPIException extends AppBaseException
{
    /**
     * Error code
     *
     * @var string
     */
    protected $errorCode = "500001";
}
