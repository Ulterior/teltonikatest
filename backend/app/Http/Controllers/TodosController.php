<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class TodosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * All todos
     *
     * @return json
     */
     public function todos(Request $request) {
        $todos = \App\Todo::all();
        return response()->json($todos);
    }

    /**
     * Add todo
     *
     * @return json
     */
     public function addTodo(Request $request) {

        $input = $request->all();

        $validator = Validator::make($input, [
           'details' => 'string|min:1|required'
        ]);

        if ($validator->fails()) {
         return response()->json([
             'error' => $validator->errors()->all()
         ], 400);
        }

        $todo = new \App\Todo();
        $todo->details = $input['details'];
        $todo->user()->associate($request->user());
        $todo->save();

        (new \App\Syslog)->register('user:'.$request->user()->id.' created todo id:'.$todo->id);

        return response()->json($todo);
    }

    /**
     * Delete todo
     *
     * @return json
     */
     public function deleteTodo(Request $request, $todo_id) {

        $todo = \App\Todo::findOrFail($todo_id);
        $todo->delete();

        (new \App\Syslog)->register('user:'.$request->user()->id.' deleted todo id:'.$todo->id);

        return response()->json([]);
    }

    /**
     * Get todo
     *
     * @return json
     */
     public function getTodo(Request $request, $todo_id) {
        $todo = \App\Todo::findOrFail($todo_id);
        return response()->json($todo);
    }

    /**
     * Update todo
     *
     * @return json
     */
     public function editTodo(Request $request, $todo_id) {

       $input = $request->all();

       $validator = Validator::make($input, [
          'details' => 'string|min:1|required'
       ]);

       if ($validator->fails()) {
        return response()->json([
            'error' => $validator->errors()->all()
        ], 400);
       }

       $todo = \App\Todo::findOrFail($todo_id);
       $todo->details = $input['details'];
       $todo->save();

       (new \App\Syslog)->register('user:'.$request->user()->id.' modified todo id:'.$todo->id);

       return response()->json($todo);
    }


    /**
     * User todo list
     *
     * @return json
     */
     public function userTodos(Request $request, $user_id=null) {
       if(isset($user_id)) {
         $user = \App\User::findOrFail($user_id);
         $todos = $user->todos()->get();
         return response()->json(compact('user', 'todos'));
       } else {
        $user = $request->user();
        $todos = $user->todos()->get();
        return response()->json($todos);
        }
    }

    //
}
