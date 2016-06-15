<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Todo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;


class TodoController extends Controller
{
    private $token_key;
    private $user;
    
    public function __construct()
    {
        $this->token_key = Session::get('token_key');
        if(!isset($this->token_key)){
            return response('Unauthoraized',403);
        }
        $redis = Redis::connection();
        $this->user = $redis->get('token_key:'.$this->token_key);
        $redis = Redis::connection();
        $this->user = json_decode($redis->get('token:'.$this->token_key));
    }

    public function index()
    {
        $todos = DB::select('select * from todos where owner_id = ? ', array($this->user->id));

        return $todos;
    }

    public function store(Request $request)
    {
        $newTodo = $request->all();
        $newTodo['owner_id'] = $this->user->id;
        return Todo::create($newTodo);
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::where('owner_id', $this->user->id)->where('id',$id)->first();

        if($todo){
            $todo->is_done=$request->input('is_done');
            $todo->save();
            return $todo;
        }else{
            return response('Unauthoraized',403);
        }
    }

    public function destroy($id)
    {
        $todo = Todo::where('owner_id', $this->user->id)->where('id',$id)->first();

        if($todo){
            Todo::destroy($todo->id);
            return  response('Success',200);;
        }else{
            return response('Unauthoraized',403);
        }
    }
}