<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     //
    // }
    public function index()
    {
        
        $users=app('db')->table('users')->get();
        return response()->json($users);
    }
    public function create(Request $req){
        //return $req->all();
        //echo 'OK';
       try{
        $this->validate($req,[
            'full_name' => 'required',
            'username'=>'required|min:6',
            'email'=>'required|email',
            'password'=>'required|min:6'
        ]);
       }
       catch(ValidationException $e){
           return response()->json([
               'success'=>false,
               'message'=>$e->getMessage(),
           ],422);

       }
     //  echo 'Validation Passed';
       try{
          $id = app('db')->table('users')->insertGetId([
               'full_name'=>trim($req->input('full_name')),
               'username'=>strtolower(trim($req->input('username'))),
               'email'=>strtolower(trim($req->input('email'))),
               'password'=>app('hash')->make($req->input('password')),
               'created_at'=>Carbon::now(),
               'updated_at'=>Carbon::now(),
           ]);
           $user = app('db')->table('users')->select('username')->
           where('id',$id)->first();
           return response()->json(
               [
                   'id'=>$id,
                   'username'=>$user->username
               ],201);
       } catch(\PDOException $e){
           return response()->json([
               'success' =>false,
               'message' =>$e->getMessage(),
           ],400);
       }
    }
    
    public function authenticate(Request $req){
        //validation
        try{
            $this->validate($req,[
                'email'=>'required|email',
                'password'=>'required|min:6'
            ]);
           }
           catch(ValidationException $e){
               return response()->json([
                   'success'=>false,
                   'message'=>$e->getMessage(),
               ],422);
    
           }
        //
        //return $req->all();
       $token= app('auth')->attempt($req->only('email', 'password'));
       if($token){
        return response()->json([
            'success'=>true,
            'message'=>'User Authenticated',
            'token' =>$token,
        ]);
       }else{
        return response()->json([
            'success'=>false,
            'message'=>"Invalid Credentials",
        ],400);
       }
    }
    public function me(){
        $user = app('auth')->user();
        if($user){
            return response()->json([
                'success'=>true,
                'message'=>'User Profile Found',
                'user' =>$user,
            ]);
           }else{
            return response()->json([
                'success'=>false,
                'message'=>"User Not Found",
            ],404);
           }
    }
}
