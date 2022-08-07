<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contractor;
use App\specifications;
use Illuminate\Support\Facades\Validator;
use App\Accounts;
use App\Project;
use App\record;
use App\transfer;
use phpDocumentor\Reflection\Types\Null_;

class ContractorController extends Controller
{
    public function contDelete($id){
        try{
            $cont=Contractor::where('cont_id',$id)->delete();

        if(!$cont){
            return response()->json(['status_message'=>'there is no record'],200);
        }
        
        else{
            return response()->json(['status_message'=>'record has been deleted','status_code'=>200],200);
        }
    }
    catch(\Illuminate\Database\QueryException $ex){
        return response()->json(['status_message'=>'record cannot be deleted due to contractor associated with project','status_code'=>202],202);

    }
    }
    public function contUpdate($id,Request $request,Contractor $emp){
        $conr=Contractor::where('cont_id',$id)->first();
        if(!$conr){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=200;
        }
        else{
            $conr->update($request->all());
            $data=array(
                'status_message'=>'Record updated successfully'
            );   
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function singleContExpense($id){
        $tr_amount=transfer::where([['cust_id',$id],['cust_type','contractors']])->sum('tran_amount');
        return $tr_amount;
    } 
    public function singleContractorTransactionDetails($id){
        $tr=transfer::where([['cust_id',$id],['cust_type','contractors']])->get();
        // $rec=record::where('project_id',)
        // $spec=Accounts::where('acc_id',$c->tran_acc_id)->();
        if($tr->isEmpty())
        {
            return response()->json(['status_message'=>"No record found"],200);

        }
        else{
            foreach($tr as $c){
                // return $c;
                $spec=Accounts::where('acc_id',$c->tran_acc_id)->first();
                $rec=record::where('tran_id',$c->tran_id)->first();
                // return $spec;
                if(!$spec){
                    $c->setAttribute('acc_name',"Not available");
                    $c->setAttribute('bank_name',"Not available");
                }
                else{
                $c->setAttribute('acc_name',$spec->acc_title);
                $c->setAttribute('bank_name',$spec->bank_name);
            }
            if(!$rec){
                $c->setAttribute('project_name',"Not available");
            }
            else{
                $proj=Project::where('project_id',$rec->project_id)->first();
                $c->setAttribute('project_name',$proj->project_name);

            }
                // $c->setAttribute('bank_name',$spec->bank_name);
            }
            $data=$tr;
            $code=200;
        }
        return response()->json(['data'=>$data],$code);
    }
    public function allContractor(){
        $con=Contractor::all();
        if($con->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            // foreach($con as $c){
            //     // return $c;
            //     $spec=specifications::select('spec_name')->where('spec_id',$c->specification)->first();
            //     $c->setAttribute('spec_name',$spec->spec_name);
            // }
            $data=$con;
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function allContractorfortable(){
        $con=Contractor::all();
        if($con->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            foreach($con as $c){
                // return $c;
                $spec=specifications::select('spec_name')->where('spec_id',$c->specification)->first();
                $c->setAttribute('spec_name',$spec->spec_name);
            }
            $data=$con;
            $code=200;
        }
        return response()->json(['data'=>$data],$code);
    }
    public function store(Request $request){
        $rules=array(
            'name'=>['required','max:50'],
            'cont_phone'=>['required','max:11','min:11','unique:ht_contractors'],
            // 'email'=>['required','unique:ht_contractors'],
            'specification'=>['required'],
            // 'address'=>['required']           
         
        );
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        else{
            // $spec=specifications::where('spec_id',$request->specification)->get();
            // $cont=Contractor::where('cont_name',$request->contractor_name)->get();
            // if($spec->isEmpty()){
            //     return response()->json(['error'=>'No company with this name'],200);
            // }
            $comp = new Contractor();
            $comp->cont_name=$request->input('name');// taking input name field
            $comp->cont_phone=$request->input('cont_phone');
            $comp->email=$request->input('email');
            $comp->address=$request->input('address');
            $comp->specification=$request->input('specification');
            $boo=$comp->save();
            if($boo){
                return response()->json([
                    'contractor_id'=>$comp->con_id,
                    'status_message'=>"Contractor has been registered successfully"
                ],200);
            }
            
            else{
                return response()->json([
                    'status_message'=>'Contractor register unsuccessfull'
                ],500);
            }
        }
    }
    public function show($id)
    {
        $conr=Contractor::where('cont_id',$id)->first();
        if(!$conr){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=200;
        }
        else{
            $data=$conr;
            $code=200;
        }
        return response()->json($data,$code);
    }
}
