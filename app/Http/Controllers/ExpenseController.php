<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Expense;
use App\transfer;

class ExpenseController extends Controller
{
    public function allExpense()
    {
        $exp = Expense::all();
        if ($exp->isEmpty()) {
            return response()->json(['status_message' => "No record found"], 200);
        } else {
            return response()->json($exp, 200);
        }
    }
    public function allExpenseTable()
    {
        $exp = Expense::all();
        if ($exp->isEmpty()) {
            return response()->json(['data' => $exp], 200);
        } else {
            return response()->json(['data' => $exp], 200);
        }
    }
    public function destroy($id)
    {
        $exp = Expense::where('Id', $id)->first();
        if (!$exp) {
            return response()->json(['status_message' => 'no data found'], 404);
        } else {
            $tr = transfer::where([['cust_type', 'other_expense'], ['cust_id', $exp->Id]])->get();
            if ($tr->isEmpty()) {
                $exp->delete();
                return response()->json(['status_message' => 'record has been deleted', 'status_code' => 200], 200);
            }

            return response()->json(['status_message' => 'record has transaction associated', 'status_code' => 202], 200);
        }
    }
    public function store(Request $request)
    {
        $rules = array(
            'name' => ['required', 'max:50'],
            // 'address'=>['required'],
            // 'contact_name'=>['required']
        );
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            $exp = new Expense();
            $exp->expense_name = $request->name;
            $bool = $exp->save();
            if ($bool) {
                return response()->json([
                    'expense_id' => $exp->Id,
                    'status_message' => 'Expense Added'
                ], 200);
            } else {
                return response()->json(['status_message' => 'Error Occured'], 500);
            }
        }
    }
}
