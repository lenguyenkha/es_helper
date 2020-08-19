<?php

namespace App\Traits;

use App\Http\Resources\BaseResource;
use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Define meta to response
     *
     * @var null
     */
    protected $meta = [];
    /**
     * Define data to response
     *
     * @var array
     */
    protected $data = [];
    /**
     * Response only data
     *
     * @var bool
     */
    protected $dataOnly = false;
    /**
     * Status to response
     *
     * @var int
     */
    protected $status = JsonResponse::HTTP_OK;
    /**
     * Error validation
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Error code when exception
     *
     * @var int
     */
    protected $errorCode = null;

    /**
     * Flag to use resource convert
     *
     * @var bool
     */
    protected $useResource = false;

    /**
     * Resource class
     *
     * @var string
     */
    protected $resourceClass = BaseResource::class;

    /**
     * Return json format
     */
    public function jsonOut()
    {
        if ($this->dataOnly) {
            return response($this->data, $this->status);
        }

        if (!isset($this->meta['message'])) {
            $this->meta['message'] = __('messages.request_success');
        }

        $this->meta['status'] = $this->status;

        if (!empty($this->errorCode)) {
            $this->meta['error_code'] = $this->errorCode;
        }

        if ($this->useResource && !empty($this->data)) {
            $this->data = call_user_func($this->resourceClass . '::collection', $this->data);
        }

        # Set data response
        $response = [
            'meta' => $this->meta,
            'data' => $this->data
        ];

        # Check on local and enable debug
//        if (config('app.debug') && config('app.env') == 'local') {
//            $response['_debugbar'] = app('debugbar')->getData();
//        }

        return response($response, $this->status);
    }

    /**
     * Set messages to response
     *
     * @param mixed $messages messages
     * @param array $optional can be input optional params
     *
     * @return $this
     */
    public function setMeta($messages = "", $optional = [])
    {
        // set message into tag meta
        if (!empty($messages)) {
            $this->meta["message"] = $messages;
        }
        // set options data
        if (!empty($optional)) {
            $this->meta = array_merge($this->meta, $optional);
        }

        return $this;
    }

    /**
     * Set data to response
     *
     * @param array $data Data of response
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set data only, want to response only data
     *
     * @param bool $dataOnly response without meta
     * @return $this
     */
    public function setDataOnly(bool $dataOnly)
    {
        $this->dataOnly = $dataOnly;
        return $this;
    }

    /**
     * Set status to response
     *
     * @param int $status Status of response
     * @return $this
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set data for paginated
     *
     * @param array $resource Resource data
     * @return $this;
     */
    public function setDataPaginated(array $resource)
    {
        # Set data response
        $this->setData($resource['data']);
        # delete data in $resource.
        unset($resource['data']);
        # Set pagination to meta data.
        $this->setMeta(__('messages.request_success'), ['pagination' => $resource]);

        return $this;
    }

    /**
     * Set error code function
     *
     * @param string $errorCode Error code at meta
     * @return $this
     */
    public function setErrorCode(string $errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }
}
