<?php

namespace App\Http\Controllers;

use App\Accounts;
use Illuminate\Http\Request;
use App\expense_account;
use App\transfer;
use App\personalExpenseRecord;
use Exception;
use Illuminate\Support\Facades\Validator;

class expense_account_controller extends Controller
{
    public function getTabledata($id)
    {
        $exp = expense_account::where('id', $id)->first();
        $p = personalExpenseRecord::where('exp_id', $exp->id)->get();
        // $tr=transfer::where
        foreach ($p as $i) {
            $tr = transfer::where('tran_id', $i->tran_id)->first();
            $acc = Accounts::where('acc_id', $tr->tran_acc_id)->first();
            $i->setAttribute('tran_date', $tr->tran_date);
            $a = (string)$acc->acc_title . "(" . (string)$acc->bank_name . ")";
            $i->setAttribute('from_account', $a);
            $i->setAttribute('tran_amount', number_format($tr->tran_amount));
            $i->setAttribute('exp_name', $exp->expense_acc_name);
            $i->setAttribute('description', $exp->description . "<br>" . $tr->description);

            if ($tr->cheque_num != null || $tr->cheque_date != null) {
                $i->setAttribute('payment_mode', "cheque");
            } elseif ($tr->transfer_id != null) {
                $i->setAttribute('payment_mode', "transfer");
            } else {
                $i->setAttribute('payment_mode', "cash");
            }
        }
        return response()->json(['data' => $p], 200);

        // $cont = personalExpenseRecord::select('ht_expense_account.expense_acc_name', 'ht_expense_account.description', 'ht_transactions.tran_date','')->join('ht_contractors', 'faq_project_contractor.contractor_id', 'cont_id')->join('ht_projects', 'faq_project_contractor.project_id', 'ht_projects.project_id')->where('faq_project_contractor.project_id', $id)->get();
    }
    public function deleteExpense($id)
    {
        try {
            $pr = personalExpenseRecord::where('exp_id', $id)->get();
            if ($pr) {
                personalExpenseRecord::where('exp_id', $id)->delete();
                foreach ($pr as $p) {
                    transfer::where('tran_id', $p->tran_id)->delete();
                }
            }
            expense_account::where('id', $id)->delete();
        } catch (Exception $e) {
            return response()->json(['status_message' => "cannot delete for following reason " + $e, 'status_code' => 202], 202);
        }
    }
    public function getExpense()
    {
        $exp = expense_account::all();
        if (!$exp) {
            return response()->json(['status_message' => "no data"], 200);
        } else {
            return $exp;
        }
    }
    public function getSingledata($id)
    {
        $exp = expense_account::where('id', $id)->first();
        if (!$exp) {
            return response()->json(['status_message' => "no data"], 200);
        } else {
            return $exp;
        }
    }
    public function store(Request $request)
    {
        $rules = array(
            'expense_acc_name' => ['required', 'unique:ht_expense_account'],
        );
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json(['status_message' => $validate->errors()], 406);
        } else {
            $exp_acc = new expense_account();
            $exp_acc->expense_acc_name = $request->input("expense_acc_name");
            $exp_acc->description = $request->input("description");
            $exp_acc->amount = 0;
            $boo = $exp_acc->save();
            if ($boo) {
                return response()->json([
                    // 'company_id'=>$exp_acc->id,
                    'status_message' => "Expense Account has been Added"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Employee register unsuccessfull'
                ], 500);
            }
        }
    }
    public function getExpenseAccount()
    {
        $exp = expense_account::all();
        // $exp_acc=expense_account::all();
        if ($exp->isEmpty()) {
            return response()->json(['data' => $exp], 200);
        } else {
            // number_format($exp->amount);
            // $exp->setAttribute('amount',number_format($exp->amount));
            return response()->json(['data' => $exp], 200);
        }
    }
}
