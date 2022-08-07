<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Company;
use App\Project;

class CompanyController extends Controller
{
    public function compID($id)
    {
        $com = Company::where('company_id', $id)->first();
        if (!$com) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 404;
        } else {
            $data = $com;
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function compDelete($id)
    {
        try {
            $com = Company::where('company_id', $id)->delete();
            if (!$com) {
                return response()->json(['status_message' => 'record not found'], 404);
            }
            return response()->json(['status_message' => 'record has been deleted', 'status_code' => 200], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status_message' => 'cannot delete this company because it has projects', 'status_code' => 202], 202);
        }
    }
    public function compUpdate($id, Request $request, Company $com)
    {
        $com = Company::where('company_id', $id)->first();
        if (!$com) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 404;
        } else {
            $com->update($request->all());
            $data = array(
                'status_message' => 'Record updated '
            );
            $code = 200;
        }
        return response()->json($data, $code);
    }
    // if(!$com){
    //     return response()->json(['status_message'=>'there is no record'],404);
    // }
    // else{
    //     return response()->json(['status_message'=>'record has been deleted'],200);
    // }

    public function allCompany()
    {
        $company = Company::all();
        // if($company->isEmpty())
        // {
        //     $data=array(
        //         'status_message'=>'No data found'
        //     );
        //     $code=200;
        // }
        // else{
        $data = array('data' => $company);
        $code = 200;
        // }
        return response()->json($data, $code);
    }
    public function store(Request $request)
    {
        $rules = array(
            'name' => ['required', 'max:50'],
            // 'contact_no'=>['required','max:11','min:11','unique:ht_company'],
            // 'email'=>['required','unique:ht_company'],
            'status' => ['required'],
            // 'address'=>['required'],
            // 'contact_name'=>['required']
        );
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            $comp = new Company();
            $comp->company_name = $request->input('name'); // taking input name field
            $comp->contact_no = $request->input('contact_no');
            $comp->email = $request->input('email');
            $comp->contact_name = $request->input('contact_name');
            // $comp->address=$request->input('address');
            $comp->status = $request->input('status');
            $boo = $comp->save();
            if ($boo) {
                return response()->json([
                    'company_id' => $comp->company_id,
                    'status_message' => "Company has been registered successfully"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Company register unsuccessfull'
                ], 500);
            }
        }
    }
    public function getCompany(Request $request)
    {
        $comp = Company::where('company_name', $request->name)->get();
        // return $comp;
        $on_project = array();
        $off_project = array();
        if ($comp->isEmpty()) {
            return response()->json(["status_message" => "no company found"], 404);
        } else {
            $proj = Project::where('company_id', $comp[0]->company_id)->get();
            if ($proj->isEmpty()) {
                return response()->json(["status_message" => "no project for company"], 404);
            } else {
                foreach ($proj as $i) {
                    if ($i->status == "active") {
                        array_push($on_project, $i);
                    } elseif ($i->status == "completed" || $i->status == "canceled") {
                        array_push($off_project, $i);
                    }
                }
                return response()->json([
                    "company_name" => $comp[0]->company_name,
                    "company_contact" => $comp[0]->contact_no,
                    "closed_project" => sizeof($off_project),
                    "ongoing_project" => sizeof($on_project)
                ], 200);
            }
        }
    }
    public function update(Request $request)
    {
        $comp = Company::where('company_name', $request->company_name)->update(['company_name' => $request->company_name, 'contact_no' => $request->contact_no]);
        // if($comp==true){
        return response()->json([
            // 'company_id'=>$comp->company_id,
            'status_message' => "Company has been updated"
        ], 200);
        // }
        // else{
        //     return response()->json([
        //         'status_message'=>'Company update unsuccessfull'
        //     ],500);
        // }

    }
}
