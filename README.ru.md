Ajax Response
=============

Laravel Ajax Response

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
Использование `bind` "связывает" класс `Vanchelo\AjaxResponse\Response` c `app.response` чтобы в дальнейшем нам было удобно к нему обращаться.
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
```json
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
```json
{
    'success': false,
    'error': true,
    'message': 'Что-то пошло не так'
}
```
Если всё хорошо:
```json
{
    'success': true,
    'error': false,
    'message': 'Получено 11 задач',
    'todos': [...] // здесь массив полученных задач
}
```
