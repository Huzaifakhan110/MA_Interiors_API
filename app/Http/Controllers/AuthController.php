<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Ramsey\Uuid\Guid\Fields;

class AuthController extends Controller
{
    public function getUsers(){
        $user=User::all();
        if($user->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=404;
        }
        else{
            $data=array('data'=>$user);
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function getSingleUser($id){
        $user=User::where('user_id',$id)->first();
        if(!$user){
            return response()->json(['status_message'=>'no data available'],200);
        }
        else{
            return response()->json(['user_id'=>$user->user_id,'user_name'=>$user->user_name,'user_role'=>$user->user_role,'user_status'=>$user->user_status],200);

        }
    }
    public function deleteUser($id){
        try{
            $user=User::where('user_id',$id)->delete();
            if(!$user){
                return response()->json(['status_message'=>'there is no record'],200);
            }
            else{
                return response()->json(['status_message'=>'record has been deleted','status_code'=>200],200);
            }
        }
        catch(\Illuminate\Database\QueryException $e){
            return response()->json(['status_message'=>'cannot delete this User ','status_code'=>202],202);
        }

    }
    public function updateUser(Request $request,$id){
        $user=User::where('user_id',$id)->first();
        if(!$user){
            return response()->json(['status_message'=>'no data available'],200);
        }
        else{
            $user->update($request->all());
            return response()->json(['status_message'=>"User Updated"],200);
        }
    }
    public function getRole(Request $request){
        // Auth::guard('api')->user();
        $user =  Auth::guard()->user();
        // $user=PersonalAccessToken::where('token',Hash::make($request->token))->first();

        if($user==null)
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=404;
        }
        else{
            return response()->json($user->user_role,200);
        }
        return response()->json($data,$code);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status_message'=>'loggedout'],200);
;        }
    public function login(Request $request){
        $rules=array(
            'user_pass'=>['required','min:8'],
            'user_email'=>['required'],
            // 'address'=>['required'],
            // 'role'=>['required']
        );
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        $user= User::where('user_email',$request->user_email)->first();
        if(!$user || !hash::check($request->user_pass,$user->user_pass)){
            return response()->json(["status_message"=>"Email or Password is incorrect"],404); 
        }
        else{
            $token=$user->createToken('myApptoken')->plainTextToken;
            $user->setAttribute("token",$token);
            return response()->json($token,200);
        }
        }
    
    public function register(Request $request){
        $rules=array(
            'user_name'=>['required','max:50','unique:ht_users'],
            'user_pass'=>['required','min:8'],
            'user_email'=>['required','unique:ht_users'],
            'status'=>['required'],
            // 'address'=>['required'],
            'role'=>['required']
        );
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        else{
            $user=new User();
            $user->user_name=$request->user_name;
            $user->user_email=$request->user_email;
            $user->user_status=$request->status;
            $user->user_pass=Hash::make($request->user_pass);
            $user->user_role=$request->role;
            $boo=$user->save();
             
            $boo=$user->save();
            $token=$user->createToken('myApptoken')->plainTextToken;
            if($boo){
                return response()->json([
                    'token'=>$token,
                    'status_message'=>"User has been registered successfully"
                ],200);
            }
            else{
                return response()->json([
                    'status_message'=>'User register unsuccessfull'
                ],500);
            }
        }
    }
}
