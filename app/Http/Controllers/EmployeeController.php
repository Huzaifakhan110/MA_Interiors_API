<?php

namespace App\Http\Controllers;

use App\Attendance_emp;
use Illuminate\Http\Request;
use App\Employee;
use App\department;
use App\Expense;
use App\Project;
use App\transfer;
use App\Accounts;




use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Empty_;

class EmployeeController extends Controller
{
    public function empAccount($id)
    {
        $emp = transfer::where([['cust_id', $id], ['cust_type', 'employee_salary']])->get();
        $e = Employee::where("emp_id", $id)->first();
        if ($emp->isEmpty()) {
            return response()->json(['data' => $emp], 200);
        } else {
            foreach ($emp as $emp1) {
                if ($emp1->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($emp1->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $to = Accounts::where('acc_id', $emp1->tran_acc_id)->first();
                $s = "Paid From Account: " . (string)$to->acc_title . " -> ";
                $emp1->description = $s . " To Employee " . $e->emp_name . "<br>" . $emp1->description;
                $emp1->setAttribute('payment_mode', $payment_mode);
                $emp1->setAttribute('emp_name', $e->emp_name);
                $emp1->setAttribute('acc_title', $to->acc_title);
                $emp1->setAttribute('bank_name', $to->bank_name);
            }
            return response()->json(['data' => $emp], 200);
        }
    }
    public function empAttendance($id)
    {
        $emp = Attendance_emp::where('emp_id', $id)->get();
        $e = Employee::where('emp_id', $id)->first();
        if ($emp->isEmpty()) {
            return response()->json(['data' => $emp], 200);
        } else {
            foreach ($emp as $e1) {
                $e1->setAttribute('emp_name', $e->emp_name);
            }
            return response()->json(['data' => $emp], 200);
        }
    }
    public function empUpdate($id, Request $request, Employee $emp)
    {
        $emp = Employee::where('emp_id', $id)->first();
        if (!$emp) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 200;
        } else {
            $emp->update($request->all());
            $data = array(
                'status_message' => 'Record updated successfully'
            );
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function empDelete($id)
    {
        try {
            $emp = Employee::where('emp_id', $id)->first();
            if (!$emp) {
                return response()->json(['status_message' => 'there is no record'], 200);
            } else {
                Attendance_emp::where('emp_id', $id)->delete();
                $emp->delete();
                return response()->json(['status_message' => 'record has been deleted', 'status_code' => 200], 200);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status_message' => 'cannot delete this Employee'], 200);
        }
    }
    public function allEmployeeSelect()
    {
        $emp = Employee::all();
        if ($emp->isEmpty()) {
            $data = array(
                'status_message' => 'No data found'
            );
            $code = 200;
        } else {
            $data = $emp;
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function allEmployee()
    {
        $emp = Employee::all();
        if ($emp->isEmpty()) {
            $data = array(
                'data' => $emp
            );
            $code = 200;
        } else {
            foreach ($emp as $e) {
                $dept = department::where('dept_id', $e->dept_id)->first();
                $e->setAttribute('dept_name', $dept->department_name);
            }
            $data = ['data' => $emp];
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function singleEmployee($id)
    {
        $emp = Employee::where('emp_id', $id)->first();
        if (!$emp) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 200;
        } else {
            $dept = department::where('dept_id', $emp->dept_id)->first();
            $emp->setAttribute('dept_name', $dept->department_name);
            $data = $emp;
            $code = 200;
        }


        return response()->json($data, $code);
    }
    public function store(Request $request)
    {
        $rules = array(
            'emp_name' => ['required', 'max:50'],
            'emp_phone' => ['required', 'unique:ht_employee'],
            'emp_joiningDate' => ['required'],
            'emp_salary' => ['required'],
            // 'description'=>['required'],
            'job_type' => ['required'],
            'dept_id' => ['required']
        );
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            $emp = new Employee();
            $emp->emp_name = $request->input('emp_name'); // taking input name field
            $emp->emp_phone = $request->input('emp_phone');
            $emp->emp_salary = $request->input('emp_salary');
            $emp->emp_joiningDate = $request->input('emp_joiningDate');
            $emp->description = $request->input('description');
            $emp->job_type = $request->input('job_type');
            $emp->dept_id = $request->input('dept_id');
            // $comp->status=$request->input('status');
            $boo = $emp->save();
            if ($boo) {
                return response()->json([
                    'company_id' => $emp->emp_id,
                    'status_message' => "Employee has been registered successfully"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Employee register unsuccessfull'
                ], 500);
            }
        }
    }
}
