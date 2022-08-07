<?php

namespace App\Http\Controllers;
use App\Attendance_emp;
use App\Employee;
use App\department;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class AttendanceEmpController extends Controller
{
    public function allEmployeeAttendance(){
        $emp=Employee::all();
        $mytime = Carbon::now();
        $mytime= $mytime->toDateString();
        if($emp->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=200;
        }
        else{
            foreach($emp as $e){
                $dept=department::where('dept_id',$e->dept_id)->first();
                $att=Attendance_emp::where([['emp_id',$e->emp_id],['date',$mytime]])->get();
                // return 0;
                if($att->isEmpty()){
                    // $e->setAttribute('Date',$mytime);
                    // return 0;
                    $e->setAttribute('time_in',Null);
                    $e->setAttribute('time_out',Null);   
                    $e->setAttribute('status',Null);    
 
                }
                else{
                    // return 0;
                    foreach($att as $d){
                        if($d->time_out==null){

                            $e->setAttribute('time_out',Null);  
                        }
                        else{
                            
                            $e->setAttribute('time_out',$d->time_out);  
                        }
                        $e->setAttribute('status',$d->status);
                        $e->setAttribute('time_in',$d->time_in);
                        
                    }
                }
                $e->setAttribute('Date',$mytime);
                $e->setAttribute('dept_name',$dept->department_name);
            }
            $data=['data'=>$emp];
            $code=200;
        }
        return response()->json($data,$code);
    }

    public function store(Request $request){
        $rules=array(
            // 'project_name'=>['required','max:50'],
            // 'time_in'=>['required'],
            'emp_id'=>['required'],
            'date'=>['required'],
            // 'deadline'=>['required'],
            // 'total_amount'=>['required'],
            // 'extra_amount'=>['required'],
            'status'=>['required'],
            // 'company_name'=>['required'],
            // 'contractor_name'=>['required'],
            // 'contact_name'=>['required'],
            // 'contact_email'=>['required'],
            // 'contact_num_person'=>['required']
        );
        $boo=false;
        $boo1=false;
        $validate=Validator::make($request->all(),$rules);
        if($validate->fails()){
            return response()->json(['status_message'=>$validate->errors()],406);
        }
        else{
            $att=Attendance_emp::where([['emp_id',$request->input('emp_id')],['date',$request->input('date')]])->first();
            if($att==null){
            $att=new Attendance_emp();
            $att->emp_id=$request->input('emp_id');
            $att->date=$request->input('date');
            $att->status=$request->input('status');
            $att->time_in=$request->input('time_in');
            $att->time_out=$request->input('time_out');
            $boo=$att->save();
        }
        else{
            if($request->status=="absent" ||$request->status=="leave"){
                $boo1=Attendance_emp::where('id',$att->id)->update(['time_out'=>null,'time_in'=>null,'status'=>$request->input('status')]);
            }
            else{
            $boo1=Attendance_emp::where('id',$att->id)->update(['time_out'=>$request->input('time_out'),'time_in'=>$request->input('time_in'),'status'=>$request->input('status')]);
        }
        }
            if($boo||$boo1){
                return response()->json([
                    'Employee id'=>$att->emp_id,
                    'status_message'=>"Attendance successfully"
                ],200);
            }
            
            else{
                return response()->json([
                    'status_message'=>'Attendance unsuccessfull'
                ],500);
            }
        }
    
    }


}
