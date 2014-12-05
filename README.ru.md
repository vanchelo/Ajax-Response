Ajax Response
=============

Laravel Ajax Response

#Для чего это нужно?
А нужно это для того, чтобы гарантированно возвращать один формат ответов на AJAX запросы во всём приложении.

Обычно мы пишем что-то в таком духе (вы может быть так не пишете, а я раньше писал именно так):
```
TodosController
{
    public function index()
    {
        $todos = Todo::all():
         
        return [
            'success' => true,
            'error' => false,
            'message' => sprintf('Получено %s задач', $todos->count()),
            'todos' => $todos->toArray()
        ];
    }
    
    public function show($id)
    {
        $todo = Todo::find($id):
        
        if ( ! $todo)
        {
            return [
                'success' => false,
                'error' => true,
                'message' => sprintf('Задача с ID "%s" не найдена', $id),
            ]; 
        }
         
        return [
            'success' => true,
            'error' => false,
            'message' => 'Задача успешно получена',
            'todo' => $todo->toArray()
        ];
    }
}
```
Какой основной минус в таком подходе?
1-й. Приходится формировать массив для ответа копируя код из метода в метод.
2-й. И самый большой! Помнить формат массива и следить за его целостностью во всем приложении!

Другое дело заранее подготовить ответ, и возвращать его всегда в одном формате, не думаю каждый раз какие ключи должны в нем присутствовать.

```
TodosController
{
    public function index()
    {
        $todos = Todo::all():
         
        return app('app.response')
            ->message(sprintf('Получено %s задач', $todos->count()))
            ->data(['todos' => $todos->toArray()]);
    }
    
    public function show($id)
    {
        $todo = Todo::find($id):
        
        if ( ! $todo)
        {
            return app('app.response')
                ->error('Задача с ID "%s" не найдена', $id);
        }
         
        return app('app.response')
            ->message('Задача успешно получена')
            ->data(['todo' => $todo->toArray()]);
    }
}
```
Здесь мы имеем один заранее подготовленный ответ на всё приложение.

Ниже я приведу более развернутые примеры как этим пользоваться.

##Установка
Подключаем пакет через composer:
```
composer require "vanchelo/ajax-response dev-master"
```

##Использование
В сервис провайдере вашего приложения в метод `register` добавьте следующий код:
```
$this->app->singleton('Vanchelo\AjaxResponse\Response');
$this->app->bind('app.response', 'Vanchelo\AjaxResponse\Response', true);
```

В качестве примера, привожу вот такой сервис провайдер.

```php
<?php namespace App;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerBindings();
    }

    protected function registerBindings()
    {
        $this->app->singleton('Vanchelo\AjaxResponse\Response');

        $this->app->bind('app.response', 'Vanchelo\AjaxResponse\Response', true);
    }
}
```
Использование `singleton` гарантирует один экзепляр класса на всё приложение, используйте данный подход на своё усмотрение.
Использование `bind` "связывает" класс `Vanchelo\AjaxResponse\Response` c `app.response` (абстрактный сервис, реализацию которого всегда можно заменить на свою, ) в **IoC-контейнере** чтобы в дальнейшем нам было удобно к нему обращаться.
Ниже примеду пример контроллера с разным подходом.

##Краткий How To. Или как это всё готовить.

###Подход #1:

Базовый контроллер
```php
<?php namespace App\Controllers;

use App;
use Request;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    /**
     * @var \Vanchelo\AjaxResponse\Response
     */
    protected $response;

    function __construct()
    {
        if (Request::ajax())
        {
            // Вот для чего мы использовали метод `bind`
            $this->response = App::make('app.response');
        }
    }
}
```

Какой-то другой контроллер
```php
<?php namespace App\Controllers;

use View;
use App\Models\Todo;

class TodosController extends BaseController
{
    public function index()
    {
        $todos = Todo::all();
        
        if ($this->response)
        {
            return $this->response
                ->message('Получено ' . $todos->count() . ' задач')
                ->data([
                    'todos' => $todos->toArray()
                ]);
        }
        
        return View::make('todos', compact('todos'));
    }
}
```
Если всё хорошо:
```js
{
    'success': true,
    'error': false,
    'message': 'Получено 11 задач',
    'todos': [...] // здесь массив полученных задач
}
```


###Подход #2:

Используем магию **DI**, **Laravel** автоматические инжектит наш **Response** в конструктор контроллера.

Базовый аяксовый контроллер
```php
<?php namespace App\Controllers;

use Illuminate\Routing\Controller;
use Vanchelo\AjaxResponse\Response as AjaxResponse;

class BaseAjaxController extends Controller
{
    /**
     * @var \Vanchelo\AjaxResponse\Response
     */
    protected $response;

    function __construct(AjaxResponse $response)
    {
        $this->response = $response;
    }
}
```

Наследник базового контроллера. Можно работать и без базового, определив конструктор в текущем контроллере.
```php
<?php namespace App\Controllers;

use View;
use Request;
use App\Models\Todo;

class TodosController extends BaseAjaxController
{
    public function index()
    {
        $todos = Todo::all();
        
        if ( ! $todos->count())
        {
            return $this->response->error('Что-то пошло не так.');
        }
        
        return $this->response
            ->message('Получено ' . $todos->count() . ' задач')
            ->data([
                'todos' => $todos->toArray()
            ]);
    }
}
```

В данном случае, если не было получено ни одной задачи возвращаем сообщение об ошибке и статусы:
```js
{
    'success': false,
    'error': true,
    'message': 'Что-то пошло не так'
}
```
Если всё хорошо:
```js
{
    'success': true,
    'error': false,
    'message': 'Получено 11 задач',
    'todos': [...] // здесь массив полученных задач
}
```

Вы конечно же можете по аналогии создать свой класс, или даже реализовать магические методы для еще большего удобства:
```php
<?php

use Vanchelo\AjaxResponse\Response as AjaxResponse;

class MyAjaxResponse extends AjaxResponse
{
    function __call($name, $arguments)
    {
        if (count($arguments) == 1)
        {
            $this->data[$name] = $arguments[0];
        }
        else if (count($arguments) > 1)
        {
            $this->data[$name] = $arguments;
        }

        return $this;
    }
}
```
И тогда вместо `response->data(['todo' => $todo])` можно смело писать
```php
$response->todo($todos);
```
На выходе получим всё тот же готовый ответ
```js
{
    'success': true,
    'error': false,
    'message': '',
    'todo': { ... }
}
```
