<?php
namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class AppBaseException
 *
 * @package App\Exceptions
 */
class AppBaseException extends \Exception
{
    /**
     * Http code
     *
     * @var int
     */
    protected $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * Error code
     *
     * @var int
     */
    protected $errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * Message arguments
     *
     * @var array
     */
    protected $messageArgs = [];

    /**
     * AppBaseException constructor.
     *
     * @param int $errorCode
     * @param string $message
     * @param array $messageArgs
     * @param Throwable|null $previous
     */
    public function __construct(
        int $errorCode = null,
        string $message = "",
        array $messageArgs = [],
        Throwable $previous = null
    ) {
        $this->messageArgs = !empty($messageArgs) ? $messageArgs : [];

        if (!empty($errorCode)) {
            $this->errorCode = $errorCode;
        }

        if (empty($this->message)) {
            $this->message = __('messages.api_messages.' . $this->errorCode, $this->messageArgs);
        } else {
            $this->message = $message;
        }

        parent::__construct($this->message, $this->code, $previous);
    }
}
