<?php

namespace App\Http\Controllers;
use App\department;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function getDepartments(){
        $dept=department::all();
        if($dept->isEmpty()){
            return response()->json(['status_massage'=>'no data found'],200);
        }
        else{
            return response()->json($dept,200);
        }
    }

    public function store(Request $request){
        $rules=array(
            'department_name'=>['required','max:200','unique:ht_department']
        );
        $name=strtoupper($request->input('department_name'));
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        else{
            $dept = new department();
            $dept->department_name=$name;// taking input name field
            $boo=$dept->save();
            if($boo){
                return response()->json([
                    'dept_id'=>$dept->dept_id,
                    'status_message'=>"Department has been added"
                ],200);
            }
            else{
                return response()->json([
                    'status_message'=>'Department adding not possible'
                ],500);
            }
        }
    }
}
