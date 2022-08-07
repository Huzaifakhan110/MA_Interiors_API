<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsers(){
        $company=User::all();
        if($company->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=404;
        }
        else{
            $data=array('data'=>$company);
            $code=200;
        }
        return $data;
    }
    public function store(Request $request){
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
            if($boo){
                return response()->json([
                    // 'company_id'=>$comp->company_id,
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
    public function login(Request $request){
        $user= User::where('user_email',$request->user_email)->first();
        if(!$user || !hash::check($request->user_pass,$user->user_pass)){
            return response()->json(["Error"=>"Email or Password is incorrect"],404); 
        }
        else{
            return response()->json(['username'=>$user->user_name,'role'=>$user->user_role],200);
        }
        }
   }
