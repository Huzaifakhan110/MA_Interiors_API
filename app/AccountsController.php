<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Accounts;
use App\Currency;

use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function singleAcc(Request $request){
        $acc=Accounts::where('acc_id',$request->id)->first();
        if(!$acc){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=200;
        }
        else{
            $data=$acc;
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function accUpdate($id,Request $request,Accounts $acc){
        $acc=Accounts::where('acc_id',$id)->first();
        if(!$acc){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=404;
        }
        else{
            $acc->update($request->all());
            $data=array(
                'status_message'=>'Record updated Successfully'
            );   
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function accDelete($id){
        try{
        $acc=Accounts::where('acc_id',$id)->delete();
        if(!$acc){
            return response()->json(['status_message'=>'record not found'],404);
        }
        return response()->json(['status_message'=>'record has been deleted','status_code'=>200],200);
    }
    catch(\Illuminate\Database\QueryException $e){
        return response()->json(['status_message'=>'cannot delete this account as transactions are associated with this account','status_code'=>202],202);
    }
}
    public function allAccounts(){
        $acc=Accounts::where('status','active')->get();
        if($acc->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            return response()->json($acc,200);
        }
        return response($data,$code);
    }
    public function allAccountsTable(){
        $acc=Accounts::all();
        if($acc->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            foreach($acc as $a){
                $c=Currency::where('currency_id',$a->currency)->first();
                $a->setAttribute('curr_name',$c->currency_name);
            }
            return response()->json(['data'=>$acc],200);
        }
        return response($data,$code);
    }
    public function store(Request $request){
        $rules=array(
            'bank_name'=>['required','max:50'],
            'acc_number'=>['required','unique:ht_accounts'],
            'acc_title'=>['required'],
            // 'bank_phone'=>['required','max:11','min:11'],
            // 'bank_address'=>['required'],
            'currency'=>['required'],
            // 'status'=>['required'],
            // 'open_balance'=>['required'],
            // 'curr_balance'=>['required']
        );
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
            else{
            $acc = new Accounts();
            $acc->bank_name=$request->input('bank_name');// taking input name field
            $acc->acc_number=$request->input('acc_number');
            $acc->acc_title=$request->input('acc_title');
            $acc->bank_phone=$request->input('bank_phone');
            $acc->status=$request->input('status');
            $acc->bank_address=$request->input('bank_address');
            $acc->currency=$request->input('currency');
            $acc->opening_balance=$request->input('open_balance');
            $acc->current_balance=$request->input('curr_balance');
            $boo=$acc->save();
            if($boo){
                return response()->json([
                    // 'company_id'=>$comp->company_id,
                    'status_message'=>"Account has been registered successfully"
                ],200);
            }
            
            else{
                return response()->json([
                    'status_message'=>'Account register unsuccessfull'
                ],500);
            }
        }
    }
}
