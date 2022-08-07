<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Project;
use App\Company;
use App\Contractor;
use App\faq_project_contractor;
use App\transfer;
use App\record;
use App\Accounts;
use App\Expense;

class ProjectController extends Controller
{
    public function projDashboard(){
        $proj=Project::where('status','active')->get();
        $recieved=0;
        $spend=0;
        if(!$proj){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=404;
        }
        else{
            foreach($proj as $p){
                // return $p;
                $tr=transfer::where([['cust_type','project'],['cust_id',$p->project_id]])->get();
                $rec=record::where('project_id',$p->project_id)->get();
                if($rec->isempty()){
                    $p->setAttribute('payment_spend',0);
                }
                else{
                    foreach($rec as $r){
                    $tr_exp=transfer::where('tran_id',$r->tran_id)->first();
                    if(!$tr_exp){

                        $spend=0;
                    }
                    else{
                        $spend+=$tr_exp->tran_amount;
                    }
                    $p->setAttribute('payment_spend',$spend);

                    }
                }
                if(!$tr){
                    $p->setAttribute('payment_recieved',0);
                }
                else{
                    foreach($tr as $t){
                        $recieved+=$t->tran_amount;
                    }
                    $p->setAttribute('payment_recieved',$recieved);

                }
                $com=Company::where('company_id',$p->company_id)->first();
                $p->setAttribute('company_name',$com->company_name);
            }
            return response()->json(['data'=>$proj],200);
        }
        return response()->json($data,$code);

    }
    public function projUpdate($id,Request $request,Project $proj){
        $proj=Project::where('project_id',$id)->first();
        // $cont=Contractor::all();
        $con=faq_project_contractor::where('project_id',$id)->delete();

        // $c=faq_project_contractor::where('project_id',$id)->update('contractor_id',$request->input('contractor_id'));
        if(!$proj){
            $data=array(
                'status_message'=>'No record found'
            );
            $code=404;
        }
        else{
            // foreach($con->contractor_id as $co){
                // $c=faq_project_contractor::where('project_id',$id)->update('contractor_id',$co->contractor_id);
            // }
            foreach($request->contractor_id as $object){
                $faq=new faq_project_contractor();
                $faq->project_id=$proj->project_id;
                $faq->contractor_id=$object;
                $boo=$faq->save();
            }
            $proj->update($request->all());
            $data=array(
                'status_message'=>'Record updated Successfully'
            );   
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function projDelete($id){
        try{
        $proj=Project::where('project_id',$id)->delete();
        if(!$proj){
            return response()->json(['status_message'=>'there is no record'],200);
        }
        else{
            return response()->json(['status_message'=>'record has been deleted','status_code'=>200],200);
        }
    }
    catch(\Illuminate\Database\QueryException $e){
        return response()->json(['status_message'=>'cannot delete this Project it has transactions and company associated','status_code'=>202],202);
    }
    }

    public function singleprojectdetails($id){
        $cont=faq_project_contractor::select('ht_projects.project_name','ht_projects.budget','ht_projects.total_amount','ht_projects.extra_amount','ht_projects.status','ht_projects.start_date','ht_contractors.cont_name')->join('ht_contractors','faq_project_contractor.contractor_id','cont_id')->join('ht_projects','faq_project_contractor.project_id','ht_projects.project_id')->where('faq_project_contractor.project_id',$id)->get();
       if($cont->isEmpty()){
        return response()->json(['status_message'=>"No record found"],404);
    }
    else{
        return response()->json($cont,200);
    }
}
    public function singleproject($id){
        $cont=Project::find($id);
        $res=array();
        $c=faq_project_contractor::where('project_id',$cont->project_id)->get();
       if(!$cont){
        return response()->json(['status_message'=>"No record found"],200);
    }
    else{
        foreach($c as $con){
            array_push($res,$con->contractor_id);
        }
        $cont->setAttribute('contractor',$res);
        return response()->json($cont,200);
    }
    }
    public function project_details(){
        $proj=Project::all();
        // $total_projects=Object.keys($proj).length;
        $total=0;
        $on_project=array();
        $off_project=array();
        $quoted=array();
        $canceled=array();

        if($proj->isEmpty())
        {
            return response()->json(['status_message'=>'No data found'],200);
        }
        else{
            foreach($proj as $i){
                if($i->status=="active"){
                    array_push($on_project,$i);
                    $total+=1;
                }
                elseif($i->status=="completed"){
                    array_push($off_project,$i);
                    $total+=1;
                }
                elseif($i->status=="quoted"){
                    array_push($quoted,$i);
                    $total+=1;
                }
                elseif($i->status=="canceled"){
                    array_push($canceled,$i);
                    $total+=1;
                }
            }
            return response()->json(["total_project"=>$total,
                "completed_project"=>sizeof($off_project),
            "ongoing_project"=>sizeof($on_project),
        "quoted_project"=>sizeof($quoted),
        "canceled_project"=>sizeof($quoted)],200);
            // $data=$proj;
            // $code=200;
        }
        // return response()->json($data,$code);
    }
    // public function 
    public function store(Request $request){
        $rules=array(
            'project_name'=>['required','max:50'],
            'budget'=>['required'],
            // 'description'=>['required'],
            'start_date'=>['required'],
            'deadline'=>['required'],
            // 'total_amount'=>['required'],
            // 'extra_amount'=>['required'],
            'status'=>['required'],
            'company_name'=>['required'],
            // 'contractor_name'=>['required'],
            // 'contact_name'=>['required'],
            // 'contact_email'=>['required'],
            // 'contact_num_person'=>['required']
        );
        // return $request->all();
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['status_message'=>$validate->errors()],406);
        }
        else{
            $comp=Company::where('company_name',$request->company_name)->get();
            $c=array();
            // if(is_array($request->contractor_name)){
            $cont=Contractor::all();
            if($request->contractor_name != "null" ){
            foreach($cont as $object){
                foreach($request->contractor_name as $i){
                    if($object->cont_name===$i){
                        array_push($c,$object->cont_id);
                    }
                    else{
                        continue;
                    }
                }
            }
        }

            if($comp->isEmpty()){
                return response()->json(['error'=>'No company with this name'],200);
            }
            // elseif(sizeOf($c)==0){
            //     return response()->json(['error'=>'No contractor with this name'],200);
            // }
            else{
                $proj = new Project();
                $proj->project_name=$request->input('project_name');// taking input name field
                $proj->budget=$request->input('budget');
                $proj->description=$request->input('description');
                $proj->start_date=$request->input('start_date');
                $proj->deadline=$request->input('deadline');
                $proj->extra_amount=0;
                $proj->status=$request->input('status');
                $proj->total_amount=0;
                $proj->company_id=$comp[0]->company_id;
                $proj->contact_name=$request->input('contact_name');
                $proj->contact_email=$request->input('contact_email');
                $proj->contact_num_person=$request->input('contact_num_person');
                $boo=$proj->save();
                if($boo){
                    $p=Project::orderBy('project_id','desc')->first();
                    if(sizeOf($c)!=0){
                foreach($c as $object){
                    $faq=new faq_project_contractor();
                    $faq->project_id=$p->project_id;
                    $faq->contractor_id=$object;

                    $boo1=$faq->save();
                    }
                }
                    return response()->json([
                        'status_message'=>"project has been added"
                    ],200);

                }
                
                else{
                    return response()->json([
                        'status_message'=>'Project register unsuccessfull'
                    ],500);
                }
            }
        }
    }
    // to get all the projects
    public function projectData(){
        $exp=Project::all();
        if($exp->isEmpty()){
            return response()->json(['status_message'=>"No record found"],200);
        }
        else{
            return response()->json($exp,200);
        }
    }
    public function projectDataTable(){
        $exp=Project::all();
        $recieving=0;
        $paid=0;
        if($exp->isEmpty()){
            return response()->json(['status_message'=>"No record found"],200);
        }
        else{
            foreach($exp as $e){
                $com=Company::where('company_id',$e->company_id)->first();
                $rec=transfer::where([['cust_type','project'],['cust_id',$e->project_id]])->get();
                $pay=record::where('project_id',$e->project_id)->get();
                foreach($rec as $r){
                    $recieving+=$r->tran_amount;
                }
                foreach($pay as $p){
                    $t=transfer::where('tran_id',$p->tran_id)->first();
                    $paid+=$t->tran_amount;
                }
                // return [$pay,$e->project_id];
                $e->setAttribute('company_name',$com->company_name);
                $e->setAttribute('Recieved',$recieving);
                $e->setAttribute('Payed',$paid);
                if($e->budget-$recieving<0){
                    $e->setAttribute('extra',($e->budget-$recieving)*-1);

                }
                else{
                    $e->setAttribute('extra',0);
                }

            }
            return response()->json(['data'=>$exp],200);
        }
    }
    public function singleprojectTransactionDetails($id){
        $debit=[];
        $result=[];
        $rec=record::select("tran_id")->where("project_id",$id)->get();
        $credit=transfer::where([["cust_type","project"],['tran_type','credit'],['cust_id',$id]])->get();
        foreach($rec as $r){
            $transfer=transfer::where('tran_id',$r->tran_id)->first();
            $account=Accounts::where('acc_id',$transfer->tran_acc_id)->first();
            if($transfer->cust_type=="contractors"){
            $con=Contractor::where('cont_id',$transfer->cust_id)->first();
            $transfer->setAttribute('contractor',$con->cont_name);    
            $transfer->setAttribute('expense',"null");

        }
        else{
            $exp=Expense::where('Id',$transfer->cust_id)->first();
            // return $exp;
            $transfer->setAttribute('expense',$exp->expense_name);
            $transfer->setAttribute('contractor',"Null");

        }          
            $transfer->setAttribute('acc_name',$account->acc_title);
            $transfer->setAttribute('bank_name',$account->bank_name);


            // return $account;
            array_push($debit,$transfer);
        }
        foreach($credit as $c){
            $acc=Accounts::where('acc_id',$c->tran_acc_id)->first();
            $c->setAttribute('acc_name',$acc->acc_title);
            $c->setAttribute('bank_name',$acc->bank_name);
            $c->setAttribute('contractor',"Null");
            $c->setAttribute('expense',"Null");


        array_push($debit,$c);
    }
        return response()->json(['data'=>$debit],200);
        // $credit=transfer::where([["cust_type","project"],['tran_type','credit'],['cust_id',$id]])->get();

    }
    public function comp_project($id){
        $result=[]; 
        // $records=[];
        $amount=0;
        // $a=[];
       $proj=Project::where("company_id",$id)->get();
       
       foreach($proj as $t){
        $tr=transfer::where([["cust_type","project"],['tran_type','credit'],['cust_id',$t->project_id]])->sum('tran_amount');
        $rec=record::select("tran_id")->where("project_id",$t->project_id)->get();
        foreach($rec as $r){
            // return $r->tran_id;
            $transfer=transfer::select('tran_amount')->where('tran_id',$r->tran_id)->first();
            $amount=$amount+$transfer->tran_amount;
        }
      
        array_push($result,['extra_bill'=>$t->extra_amount,'budget'=>$t->budget,'deadline'=>$t->deadline,'project_id'=>$t->project_id,'payment_recieved'=>$tr,'project_name'=>$t->project_name,'status'=>$t->status,'budget'=>$t->budget,'payment_spend'=>$amount,'balance'=>$t->budget-$tr]);   
        $amount=0;

    }
       if($proj->isEmpty()){
        return response()->json(['status_message'=>"No record found"],200);
    }
    else{
        return response()->json(['data'=>$result],200);
        // return response()->json(['data'=>$records],200);
        // return $records;
    }
       
    }
    // public function update(Request $request){

    // }
}
