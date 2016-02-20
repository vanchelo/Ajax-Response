<?php

namespace Vanchelo\AjaxResponse;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\MessageBag;
use JsonSerializable;

class Response implements JsonSerializable, ArrayableInterface, JsonableInterface
{
    /**
     * @var bool
     */
    protected $success;
    /**
     * @var string
     */
    protected $message;
    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $message
     * @param bool   $success
     * @param array  $data
     */
    public function __construct($message = '', $success = true, array $data = [])
    {
        $this->data = $data;
        $this->success = $success;
        $this->message = $message;
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
     * @param bool  $merge
     *
     * @return self
     */
    public function data(array $data, $merge = false)
    {
        if ($merge) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data += $data;
        }

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
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
            'error' => !$this->success,
            'message' => $this->message,
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
