<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Accounts;
use App\Currency;
use App\transfer;
use App\Project;
use App\Contractor;
use App\Employee;
use App\expense_account;
use App\personalExpenseRecord;
use App\Expense;
use App\record;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function singleAcc(Request $request)
    {
        $acc = Accounts::where('acc_id', $request->id)->first();
        if (!$acc) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 200;
        } else {
            $data = $acc;
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function accUpdate($id, Request $request, Accounts $acc)
    {
        $acc = Accounts::where('acc_id', $id)->first();
        if (!$acc) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 404;
        } else {
            $acc->update($request->all());
            $data = array(
                'status_message' => 'Record updated Successfully'
            );
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function accDelete($id)
    {
        try {
            $acc = Accounts::where('acc_id', $id)->delete();
            if (!$acc) {
                return response()->json(['status_message' => 'record not found'], 404);
            }
            return response()->json(['status_message' => 'record has been deleted', 'status_code' => 200], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status_message' => 'cannot delete this account as transactions are associated with this account', 'status_code' => 202], 202);
        }
    }
    public function allAccounts()
    {
        $acc = Accounts::where('status', 'active')->get();
        if ($acc->isEmpty()) {
            $data = array(
                'status_message' => 'No data found'
            );
            $code = 200;
        } else {
            return response()->json($acc, 200);
        }
        return response($data, $code);
    }
    public function allAccountsTable()
    {
        $acc = Accounts::all();
        if ($acc->isEmpty()) {
            $data = array(
                'data' => $acc
            );
            $code = 200;
        } else {
            foreach ($acc as $a) {
                $c = Currency::where('currency_id', $a->currency)->first();
                $a->setAttribute('curr_name', $c->currency_name);
            }
            return response()->json(['data' => $acc], 200);
        }
        return response($data, $code);
    }
    // public function fordescription($cust_type,$table){

    // }
    public function accountTransaction($id)
    {
        $acc = Accounts::where('acc_id', $id)->first();
        $temp = $acc->opening_balance;

        $tr1 = transfer::where('tran_acc_id', $id)->get();
        $tr2 = transfer::where([['cust_type', 'transfer'], ['cust_id', $id]])->get();
        if ($tr1->isEmpty() && $tr2->isEmpty()) {
            return response()->json(['data' => $tr1], 200);
        } else {
            if ($tr1->isEmpty()) {
                foreach ($tr2 as $t) {
                    $to = Accounts::where([['acc_id', $t->tran_acc_id], ['status', 'active']])->first();
                    $from = Accounts::where([['acc_id', $t->cust_id], ['status', 'active']])->first();
                    // $temp_2=$to->opening_balance;
                    // $temp_1=$from->opening_balance;
                    if ($t->cheque_num != null) {
                        $t->setAttribute("payment_mode", "cheque");
                    } elseif ($t->transfer_id != null) {
                        $t->setAttribute("payment_mode", "transfer");
                    } else {
                        $t->setAttribute("payment_mode", "cash");
                    }
                    if ($t->tran_type == "credit") {
                        $t->setAttribut('credit_amount', number_format($t->tran_amount));
                        $t->setAttribut('debit_amount', 0);
                        $temp = $temp + $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } else {
                        $t->setAttribut('credit_amount', 0);
                        $t->setAttribut('debit_amount', number_format($t->tran_amount));
                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    }
                    $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                    $t->description = $s . " From Account " . $from->acc_title . "<br>" . $t->description;
                    $t->tran_date = date('d-m-Y', strtotime($t->tran_date));
                }
                return response()->json(['data' => $tr2], 200);
            } else {
                // dd($tr1);

                // dd($temp);
                foreach ($tr1 as $t) {
                    $to = Accounts::where([['acc_id', $t->tran_acc_id], ['status', 'active']])->first();
                    // $temp=$to->opening_balance;
                    if ($t->cust_type == "project") {
                        $project = Project::where('project_id', $t->cust_id)->first();
                        $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                        $t->description = $s . " From Project " . $project->project_name . "<br>" . $t->description;
                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $temp = $temp + $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } elseif ($t->cust_type == "employee_salary") {
                        $emp = Employee::where('emp_id', $t->cust_id)->first();
                        $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                        $t->description = $s . " to Employee " . $emp->emp_name . "<br>" . $t->description;
                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } elseif ($t->cust_type == "other_expense") {
                        $exp = Expense::where('Id', $t->cust_id)->first();
                        $rec = record::where('tran_id', $t->tran_id)->first();
                        $project = Project::where('project_id', $rec->project_id)->first();
                        $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                        $t->description = $s . " for expense " . $exp->expense_name . " for this project " . $project->project_name . "<br>" . $t->description;
                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } elseif ($t->cust_type == "contractors") {
                        $cont = Contractor::where('cont_id', $t->cust_id)->first();
                        $rec = record::where('tran_id', $t->tran_id)->first();
                        $project = Project::where('project_id', $rec->project_id)->first();
                        $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                        $t->description = $s . " to contractor " . $cont->cont_name . " for this project " . $project->project_name . "<br>" . $t->description;
                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } elseif ($t->cust_type == "transfer") {
                        $from = Accounts::where('acc_id', $t->cust_id)->first();
                        if ($t->tran_type == "credit") {
                            $s = " Payment Given from Account: " . (string)$to->acc_title . " -> ";
                            $t->description = $s . " to Account " . $from->acc_title . "<br>" . $t->description;
                            $t->setAttribute('credit_amount', 0);
                            $t->setAttribute('debit_amount', number_format($t->tran_amount));
                            $temp = $temp - $t->tran_amount;
                        } else {
                            $s = " Payment recieved in Account: " . (string)$to->acc_title . " <- ";
                            $t->description = $s . " from Account " . $from->acc_title . "<br>" . $t->description;
                            $t->setAttribute('credit_amount', number_format($t->tran_amount));
                            $t->setAttribute('debit_amount', 0);
                            $temp = $temp + $t->tran_amount;
                        }

                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $t->setAttribute('current_amount', number_format($temp));
                    } elseif ($t->cust_type == "expenses") {
                        // dd($temp);
                        $e = personalExpenseRecord::where('tran_id', $t->tran_id)->first();
                        $exp = expense_account::where('id', $e->exp_id)->first();

                        // dd($exp);
                        // $t->
                        $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                        $t->description = $s . " for personal expense " . $exp->expense_acc_name . "<br>" . $t->description;
                        $t->tran_date = date('d-m-Y', strtotime($t->tran_date));

                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    }
                    if ($t->cheque_num != null) {
                        $t->setAttribute("payment_mode", "cheque");
                    } elseif ($t->transfer_id != null) {
                        $t->setAttribute("payment_mode", "transfer");
                    } else {
                        $t->setAttribute("payment_mode", "cash");
                    }
                    if ($t->tran_type == "credit" && $t->cust_type != "transfer") {
                        $t->setAttribute('credit_amount', number_format($t->tran_amount));
                        $t->setAttribute('debit_amount', 0);
                    } elseif ($t->cust_type != "transfer") {

                        $t->setAttribute('credit_amount', 0);
                        $t->setAttribute('debit_amount', number_format($t->tran_amount));
                    }
                }
                foreach ($tr2 as $t) {
                    $to = Accounts::where('acc_id', $t->tran_acc_id)->first();
                    $from = Accounts::where('acc_id', $t->cust_id)->first();
                    $a = $to->opening_balance;
                    $h = $from->opening_balance;
                    if ($t->cheque_num != null) {
                        $t->setAttribute("payment_mode", "cheque");
                    } elseif ($t->transfer_id != null) {
                        $t->setAttribute("payment_mode", "transfer");
                    } else {
                        $t->setAttribute("payment_mode", "cash");
                    }
                    if ($t->tran_type == "credit") {
                        $t->setAttribute('credit_amount', number_format($t->tran_amount));
                        $t->setAttribute('debit_amount', 0);
                        // $t->setAttribute('current_amount', $a + $t->tran_amount);
                        $temp = $temp + $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    } else {
                        $t->setAttribute('credit_amount', 0);
                        $t->setAttribute('current_amount', $h - $t->tran_amount);
                        $t->setAttribute('debit_amount', number_format($t->tran_amount));
                        $temp = $temp - $t->tran_amount;
                        $t->setAttribute('current_amount', number_format($temp));
                    }
                    $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                    $t->description = $s . " From Account " . $from->acc_title . "<br>" . $t->description;
                    $t->tran_date = date('d-m-Y', strtotime($t->tran_date));
                }
                $result = $tr1->merge($tr2);
                return response()->json(['data' => $tr1], 200);
            }
        }
    }
}
