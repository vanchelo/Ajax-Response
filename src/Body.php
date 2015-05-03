<?php

namespace Vanchelo\AjaxResponse;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\MessageBag;

class Body implements Arrayable, Jsonable
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var string
     */
    protected $message;
    /**
     * @var bool
     */
    protected $success;

    /**
     * ReponseData constructor.
     *
     * @param string $message
     * @param bool $success
     * @param array $data
     */
    public function __construct($message = '', $success = true, array $data = [])
    {
        $this->message = $message;
        $this->success = $success;
        $this->data = $data;
    }

    /**
     * Set response error status and message
     *
     * @param string $message
     *
     * @return self
     */
    public function error($message = '')
    {
        $this->success = false;
        $this->message($message);

        return $this;
    }

    /**
     * Set response errors
     *
     * @param mixed $errors
     *
     * @return self
     */
    public function errors($errors)
    {
        $this->success = false;
        $this->data['errors'] = $errors instanceof MessageBag
            ? $errors->toArray()
            : $errors;

        return $this;
    }

    /**
     * Set response message
     *
     * @param string $message
     *
     * @return self
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set response data
     *
     * @param array $data
     * @param bool $merge
     *
     * @return self
     */
    public function data($data, $merge = false)
    {
        if ($merge)
        {
            $this->data = array_merge($this->data, $data);
        }
        else
        {
            $this->data += $data;
        }

        return $this;
    }

    /**
     * Get the instance as an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'success' => $this->success,
            'error'   => ! $this->success,
            'message' => $this->message
        ] + $this->data;
    }

    /**
     * Convert the object to its JSON representation
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->toArray(), $options);
    }
}
