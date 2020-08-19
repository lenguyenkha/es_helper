<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
Use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use \Symfony\Component\HttpKernel\Exception\HttpException;
use Elasticsearch\Common\Exceptions\ElasticsearchException;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @param Throwable $exception
     * @throws \Exception
     *
     */
    public function report(Throwable $exception)
    {

        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        $this->setData(null);

        switch (true) {
            case ($exception instanceof HttpException):
                $this->setStatus($exception->getStatusCode());
                $message = $exception->getMessage();
                $message = empty($message) ? __('messages.api.' . $exception->getStatusCode()) : $message;
                $this->setErrorCode(__('error_code.api.' . $exception->getStatusCode()));
                $this->setMeta($message);
                break;
            case ($exception instanceof ValidationException):
                $this->setStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
                $this->setMeta(__('messages.request_unprocessable_entity'));
                $this->setErrorCode(__('error_code.request_unprocessable_entity'));
                $this->setData(["error" => $exception->errors()]);
                break;
            case ($exception instanceof AppBaseException):
                $this->setStatus($exception->getCode());
                $this->setErrorCode($exception->getErrorCode());
                $this->setMeta($exception->getMessage());
                break;
            case ($exception instanceof ElasticsearchException):
                $this->setStatus($exception->getCode());
                $this->setMeta(__('messages.es_server.execute_fail'));
                $this->setErrorCode(__('error_code.es_server.execute_fail'));
                $error = json_decode($exception->getMessage(),true);
                $this->setData($error);
                break;
            default:
                $this->setStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                $this->setMeta(__('messages.request_server_error'));
                $this->setErrorCode(__('error_code.request_server_error'));
                break;
        }

        return $this->jsonOut();
        // Enable it to handle web errors
        // return parent::render($request, $exception);
    }
}
