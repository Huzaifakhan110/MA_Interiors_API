<?php

namespace App\Http\Controllers;
use App\specifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\CssSelector\Node\Specificity;

class SpecificationsController extends Controller
{
    public function allSpecifications(){
        $spec=Specifications::all();
        if($spec->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            $data=$spec;
            $code=200;
        }
        return response()->json($data,$code);
    }
    public function store(Request $request){
        $rules=array(
            'name'=>['required','max:50']
        );
        $name=strtoupper($request->input('name'));
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        else{
            $spec = new Specifications();
            $spec->spec_name=$name;// taking input name field
            $boo=$spec->save();
            if($boo){
                return response()->json([
                    'spec_id'=>$spec->spec_id,
                    'status_message'=>"Specification has been added"
                ],200);
            }
            else{
                return response()->json([
                    'status_message'=>'specification adding not possible'
                ],500);
            }
        }
    }
}
