<?php

namespace Vanchelo\AjaxResponse;

use Illuminate\Http\JsonResponse;

/**
 * Class Response
 *
 * @package Vanchelo\AjaxResponse
 *
 * @method $this data($data, $merge = false)
 * @method $this message($message)
 * @method $this error($message = '')
 * @method $this errors($errors)
 * @method $this toJson($options)
 * @method $this toArray()
 */
class Response extends JsonResponse
{
    protected $body;

    public function __construct()
    {
        $this->body = new Body();

        parent::__construct();
    }

    public function send()
    {
        $this->setData($this->body);

        return parent::send();
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->body, $name)) {
            call_user_func_array([$this->body, $name], $arguments);

            return $this;
        }
    }
}
