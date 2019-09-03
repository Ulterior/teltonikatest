<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(
    ['prefix' => 'auth'],
    function () use ($router) {
        $router->post('login', ['uses' => 'AuthController@authenticate']);
        $router->post('forgot', ['uses' => 'AuthController@forgot']);
        $router->post('reset', ['uses' => 'AuthController@reset']);
    }
);

$router->group(
    ['middleware' => ['jwt.auth']],
    function () use ($router) {
        $router->put('todos/{todo_id}', [
          'middleware' => ['role:user|admin'], 'uses' => 'TodosController@editTodo'
        ]);
        $router->delete('todos/{todo_id}', [
          'middleware' => ['role:user|admin'], 'uses' => 'TodosController@deleteTodo'
        ]);
        $router->get('todos/{todo_id}', [
          'middleware' => ['role:user|admin'], 'uses' => 'TodosController@getTodo'
        ]);
        $router->post('todos', [
          'middleware' => ['role:user'], 'uses' => 'TodosController@addTodo'
        ]);
        $router->get('todos', [
          'middleware' => ['role:user'], 'uses' => 'TodosController@userTodos'
        ]);
    }
);

$router->group(
    ['prefix' => 'admin', 'middleware' => ['jwt.auth', 'role:admin']],
    function () use ($router) {
        $router->get('users', ['uses' => 'AuthController@users']);
        $router->get('todos', ['uses' => 'TodosController@todos']);
        $router->get('todos/{user_id}', ['uses' => 'TodosController@userTodos']);
        $router->get('syslogs', ['uses' => 'SyslogsController@syslogs']);
    }
);
