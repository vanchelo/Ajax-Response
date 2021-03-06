<?php

namespace Vanchelo\AjaxResponse;

/**
 * Почему я использую use и as?
 * Потому что если вы воспользуетесь Response фасадом Laravel, названия будут конфликтовать
 */
use Vanchelo\AjaxResponse\Response as AjaxResponse;

class MyAjaxResponse extends AjaxResponse
{
    /**
     * Для еще большего удобства
     * $response->todo(['id' => 10, 'title' => 'Какая-то важная задача']);
     * $response->todos(
     *      ['id' => 10, 'title' => 'Какая-то важная задача'],
     *      ['id' => 11, 'title' => 'Еще одна важная задача']
     * );
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (count($arguments) == 1) {
            $this->data[$name] = $arguments[0];
        } else {
            if (count($arguments) > 1) {
                $this->data[$name] = $arguments;
            }
        }

        return $this;
    }
}
