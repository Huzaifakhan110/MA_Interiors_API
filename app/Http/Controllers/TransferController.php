<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\transfer;
use App\Accounts;
use App\Company;
use App\Contractor;
use App\Employee;
use App\Expense;
use App\expense_account;
use App\invoice;
use App\Project;
use App\record;
use App\personalExpenseRecord;

use stdClass;

// use App\Project;



class TransferController extends Controller
{
    public function transactionDelete($id)
    {
        $tr = transfer::where('tran_id', $id)->first();
        if ($tr->cust_type == "transfer") {
            $acc1 = Accounts::where('acc_id', $tr->tran_acc_id)->first();
            $acc2 = Accounts::where('acc_id', $tr->cust_id)->first();
            if ($tr->tran_type == "credit") {
                $first = $acc1->current_balance + $tr->tran_amount;
                $second = $acc2->current_balance - $tr->tran_amount;
                $acc1->update(['current_balance' => $first]);
                $acc2->update(['current_balance' => $second]);
                transfer::where('tran_id', $id + 1)->delete();
                $tr->delete();
                return response()->json(['status_message' => "Transaction Deleted", 'status_code' => 200], 200);
            } else {
                $first = $acc1->current_balance - $tr->tran_amount;
                $second = $acc2->current_balance + $tr->tran_amount;
                $acc1->update(['current_balance' => $first]);
                $acc2->update(['current_balance' => $second]);
                transfer::where('tran_id', $id - 1)->delete();

                $tr->delete();
                return response()->json(['status_message' => "Transaction Deleted", 'status_code' => 200], 200);
            }
        } else {
            $acc1 = Accounts::where('acc_id', $tr->tran_acc_id)->first();
            if ($tr->cust_type == "expenses") {
                $e = personalExpenseRecord::where('tran_id', $tr->tran_id)->first();
                $exp = expense_account::where('id', $e->exp_id)->first();
                $exp->update(['amount' => $exp->amount - $tr->tran_amount]);
            }
            if ($tr->tran_type == "credit") {
                $first = $acc1->current_balance - $tr->tran_amount;
                $acc1->update(['current_balance' => $first]);
                if ($tr->cust_type == "project") {
                    record::where('tran_id', $id)->delete();
                    invoice::where('Trans_id', $id)->delete();
                }
                $tr->delete();
                return response()->json(['status_message' => "Transaction Deleted", 'status_code' => 200], 200);
            } else {
                $first = $acc1->current_balance + $tr->tran_amount;
                $acc1->update(['current_balance' => $first]);
                if ($tr->cust_type == "other_expense") {
                    record::where('tran_id', $id)->delete();
                    invoice::where('Trans_id', $id)->delete();
                } elseif ($tr->cust_type == "expenses") {
                    personalExpenseRecord::where('tran_id', $id)->delete();
                    invoice::where('Trans_id', $id)->delete();
                } elseif ($tr->cust_type == "contractors") {
                    record::where('tran_id', $id)->delete();
                    invoice::where('Trans_id', $id)->delete();
                } else {
                    invoice::where('Trans_id', $id)->delete();
                }
                $tr->delete();
                return response()->json(['status_message' => "Transaction Deleted", 'status_code' => 200], 200);
            }
        }
        // $a=Accounts::where('acc_id')
    }
    public function report(Request $request)
    {
        $rules = array(
            'to_date' => ['required'],
            'from_date' => ['required'],
        );
        // return $request->all();
        date('Y-m-d', strtotime($request->from_date));
        date('Y-m-d', strtotime($request->to_date));
        $validate = Validator::make($request->all(), $rules);
        $tr_cont = new stdClass();
        $tr_proj = new stdClass();
        $tr_emp = new stdClass();
        $tr = new stdClass();
        $pr_rec = 0;
        if ($validate->fails()) {
            return response()->json(['status_message' => $validate->errors()], 406);
        } elseif ($request->project == "null" && $request->employee == "null" && $request->contractor == "null") {
            $tr = transfer::whereBetween('tran_date', [$request->from_date, $request->to_date])->get();
            foreach ($tr as $t) {
                $to = Accounts::where('acc_id', $t->tran_acc_id)->first();

                if ($t->cust_type == "project") {
                    $project = Project::where('project_id', $t->cust_id)->first();
                    $t->setAttribute("name", $project->project_name);

                    $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                    $t->description = $s . " From Project " . $project->project_name . "<br>" . $t->description;
                } elseif ($t->cust_type == "contractors") {
                    $cont = Contractor::where('cont_id', $t->cust_id)->first();
                    $rec = record::where('tran_id', $t->tran_id)->first();
                    $project = Project::where('project_id', $rec->project_id)->first();
                    $t->setAttribute("name", $cont->cont_name);

                    $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                    $t->description = $s . " to contractor " . $cont->cont_name . " for this project " . $project->project_name . "<br>" . $t->description;
                } elseif ($t->cust_type == "transfer") {
                    $from = Accounts::where('acc_id', $t->cust_id)->first();
                    $t->setAttribute("name", $from->acc_title);

                    $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                    $t->description = $s . " From Account " . $from->acc_title . "<br>" . $t->description;
                } elseif ($t->cust_type == "other_expense") {
                    $exp = Expense::where('Id', $t->cust_id)->first();
                    $rec = record::where('tran_id', $t->tran_id)->first();
                    $project = Project::where('project_id', $rec->project_id)->first();
                    $t->setAttribute("name", $exp->expense_name);

                    $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                    $t->description = $s . " for expense " . $exp->expense_name . " for this project " . $project->project_name . "<br>" . $t->description;
                } elseif ($t->cust_type == "expenses") {
                    $exp_rec = personalExpenseRecord::where('tran_id', $t->tran_id)->first();
                    $exp = expense_account::where('id', $exp_rec->exp_id)->first();
                    // dd($exp)
                    // return response()->json($exp);
                    // return $exp_rec->exp_id;
                    $t->setAttribute("name", $exp->expense_acc_name);

                    $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                    $t->description = $s . " for expense " . $exp->expense_acc_name . "<br>" . $t->description;
                } elseif ($t->cust_type == "employee_salary") {
                    $exp = Employee::where('emp_id', $t->cust_id)->first();
                    $t->setAttribute("name", $exp->emp_name);

                    $s = " Payment given from Account: " . (string)$to->acc_title . " -> ";
                    $t->description = $s . " for salary " . $exp->emp_name . "<br>" . $t->description;
                }
                if ($t->cheque_num != null) {
                    $t->setAttribute("payment_mode", "cheque");
                } elseif ($t->transfer_id != null) {
                    $t->setAttribute("payment_mode", "transfer");
                } else {
                    $t->setAttribute("payment_mode", "cash");
                }
                if ($t->tran_type == "credit") {
                    $t->setAttribute('credit_amount', $t->tran_amount);
                    $t->setAttribute('debit_amount', 0);
                } else {

                    $t->setAttribute('credit_amount', 0);
                    $t->setAttribute('debit_amount', $t->tran_amount);
                }
            }
            return response()->json(['data' => $tr], 200);
        } else {
            if ($request->project != "null") {
                if (count($request->project) > 0) {
                    $tr_proj = transfer::where('cust_type', 'project')->whereBetween('tran_date', [$request->from_date, $request->to_date])->whereIn('cust_id', $request->project)->get();
                    // $tr_proj=transfer::where('cust_type','contractor')->whereIn('cust_id',$request->project)->get();
                    // return $tr_proj;
                    // }
                    // }
                    // }

                    if (!$tr_proj) {
                    } else {
                        foreach ($tr_proj as $t2) {
                            $to = Accounts::where('acc_id', $t2->tran_acc_id)->first();
                            $project = Project::where('project_id', $t2->cust_id)->first();
                            if ($t2->cheque_num != null) {
                                $payment_mode = 'cheque';
                            } elseif ($t2->transfer_id != null) {
                                $payment_mode = 'transfer';
                            } else {
                                $payment_mode = 'cash';
                            }
                            $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                            $t2->description = $s . " From Project " . $project->project_name . "<br>" . $t2->description;
                            $t2->setAttribute('payment_mode', $payment_mode);
                            $t2->setAttribute('debit_amount', 0);
                            $t2->setAttribute('credit_amount', $t2->tran_amount);
                            $t2->setAttribute('name', $project->project_name);
                        }
                    }
                }
            }
            if ($request->contractor != "null") {
                if (count($request->contractor) > 0) {
                    $tr_cont = transfer::where('cust_type', 'contractors')->whereBetween('tran_date', [$request->from_date, $request->to_date])->whereIn('cust_id', $request->contractor)->get();
                    if (!$tr_cont) {
                    } else {
                        foreach ($tr_cont as $t2) {
                            $to = Accounts::where('acc_id', $t2->tran_acc_id)->first();
                            $cont = Contractor::where('cont_id', $t2->cust_id)->first();
                            // return $cont;
                            if ($t2->cheque_num != null) {
                                $payment_mode = 'cheque';
                            } elseif ($t2->transfer_id != null) {
                                $payment_mode = 'transfer';
                            } else {
                                $payment_mode = 'cash';
                            }
                            $s = "Paid From Account: " . (string)$to->acc_title . " -> ";
                            $t2->description = $s . " To Contractor " . $cont->cont_name . "<br>" . $t2->description;
                            $t2->setAttribute('payment_mode', $payment_mode);
                            $t2->setAttribute('debit_amount', $t2->tran_amount);
                            $t2->setAttribute('credit_amount', 0);
                            $t2->setAttribute('name', $cont->cont_name);
                        }
                    }
                }
            }
            if ($request->employee != "null") {
                if (count($request->employee) > 0) {
                    $tr_emp = transfer::where('cust_type', 'employee_salary')->whereBetween('tran_date', [$request->from_date, $request->to_date])->whereIn('cust_id', $request->employee)->get();
                    if (!$tr_emp) {
                    } else {
                        foreach ($tr_emp as $t2) {
                            $to = Accounts::where('acc_id', $t2->tran_acc_id)->first();
                            $emp = Employee::where('emp_id', $t2->cust_id)->first();
                            if ($t2->cheque_num != null) {
                                $payment_mode = 'cheque';
                            } elseif ($t2->transfer_id != null) {
                                $payment_mode = 'transfer';
                            } else {
                                $payment_mode = 'cash';
                            }
                            $s = "Paid From Account: " . (string)$to->acc_title . " to ";
                            $t2->description = $s . $emp->emp_name . "Salary " . "<br>" . $t2->description;
                            $t2->setAttribute('payment_mode', $payment_mode);
                            $t2->setAttribute('debit_amount', $t2->tran_amount);
                            $t2->setAttribute('credit_amount', 0);
                            $t2->setAttribute('name', $emp->emp_name);
                        }
                    }
                }
            }
            if ($request->project != "null") {
                $tr = $tr_proj;
                $tr = $tr->merge($tr_cont);
                $tr = $tr->merge($tr_emp);
            } elseif ($request->contractor != "null") {
                $tr = $tr_cont;
                $tr = $tr->merge($tr_emp);
            } elseif ($request->employee != "null") {
                $tr = $tr_emp;
            }

            // $tr=$tr->merge($tr_cont);
            // $tr=$tr->merge($tr_emp);
            // $tr=transfer::where([['cust_type','project'],['cust_type','contractors'],['cust_type','employee_salary']])->whereBetween('tran_date',[$request->from_date,$request->to_date])->get();
            return response()->json(['data' => $tr], 200);
        }
    }
    public function totalDashboard()
    {
        $tr = transfer::all();
        $expense = 0;
        $recieving = 0;
        $profit = 0;
        if ($tr->isEmpty()) {
            if (!$tr) {
                return response()->json(['status_message' => 'there is no record'], 200);
            }
        } else {
            foreach ($tr as $t) {
                if ($t->cust_type == "project") {
                    $recieving += $t->tran_amount;
                } elseif ($t->cust_type == "contractors" || $t->cust_type == "employee_salary" || $t->cust_type == "other_expense" || $t->cust_type == "expenses") {
                    $expense += $t->tran_amount;
                }
            }
            $capital = $recieving + Accounts::sum('opening_balance');
            return response()->json(['total_recieved' => $recieving, 'total_expense' => $expense, 'total_profit' => $capital - $expense, 'total_capital' => $capital], 200);
        }
    }
    public function internalTransactionDelete($id)
    {
        try {
            $tr = transfer::where('tran_id', $id)->delete();
            if (!$tr) {
                return response()->json(['status_message' => 'there is no record'], 200);
            } else {
                return response()->json(['status_message' => 'record has been deleted'], 200);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['status_message' => 'cannot delete this Employee'], 200);
        }
    }
    public function internalTransactionUpdate(Request $request, $id)
    {
        $tr = transfer::where([['cust_type', 'transfer'], ['tran_id', $id]])->first();
        if (!$tr) {
            $data = array(
                'status_message' => 'No record found'
            );
            $code = 200;
        } else {
            $tr->update($request->all());
            $data = array(
                'status_message' => 'Record updated successfully'
            );
            $code = 200;
        }
        return response()->json($data, $code);
    }

    public function getAllTransactions()
    {
        $tr = transfer::orderBy('tran_id', 'desc')->get();
        // if($tr->isEmpty()){
        //     return response()->json(['status_message'=>'No record found'],200);
        // }
        // else{
        foreach ($tr as $t2) {
            $to = Accounts::where('acc_id', $t2->tran_acc_id)->first();
            if ($t2->cust_type == "employee_salary") {
                // $to=Accounts::where([['acc_id',$t2->tran_acc_id],['status','active']])->first();
                $emp = Employee::where('emp_id', $t2->cust_id)->first();
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $s = "Paid From Account: " . (string)$to->acc_title . " to ";
                $t2->description = $s . "Salary " . $emp->emp_name . "<br>" . $t2->description;
                $t2->setAttribute('payment_mode', $payment_mode);
                $t2->setAttribute('debit_amount', number_format($t2->tran_amount));
                $t2->setAttribute('credit_amount', 0);
                $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
            } elseif ($t2->cust_type == "other_expense") {
                $exp = Expense::where('Id', $t2->cust_id)->first();
                $rec = record::where('tran_id', $t2->tran_id)->first();
                $project = Project::where('project_id', $rec->project_id)->first();
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $s = "Paid From Account: " . (string)$to->acc_title . " to ";
                $t2->description = $s . " Office expense " . $exp->expense_name . " for this project " . $project->project_name . "<br>" . $t2->description;
                $t2->setAttribute('payment_mode', $payment_mode);
                $t2->setAttribute('debit_amount', number_format($t2->tran_amount));
                $t2->setAttribute('credit_amount', 0);
                $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
            } elseif ($t2->cust_type == "contractors") {
                // $to=Accounts::where('acc_id',$t2->tran_acc_id)->first();
                // return $t2;
                $cont = Contractor::where('cont_id', $t2->cust_id)->first();
                $rec = record::where('tran_id', $t2->tran_id)->first();
                if (!isset($rec->project_id)) {
                    // dd($t2);
                    // $tr->forget(18);
                    continue;
                }
                $project = Project::where('project_id', $rec->project_id)->first();
                // dd($rec->project_id);
                // return $cont;
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $s = "Paid From Account: " . (string)$to->acc_title . " -> ";
                $t2->description = $s . " To Contractor " . $cont->cont_name . " for this project " . $project->project_name . "<br>" . $t2->description;
                $t2->setAttribute('payment_mode', $payment_mode);
                $t2->setAttribute('debit_amount', number_format($t2->tran_amount));
                $t2->setAttribute('credit_amount', 0);
                $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
            } elseif ($t2->cust_type == "project") {
                // $to=Accounts::where('acc_id',$t2->tran_acc_id)->first();
                $project = Project::where('project_id', $t2->cust_id)->first();
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $s = " Payment Recieved in Account: " . (string)$to->acc_title . " <- ";
                $t2->description = $s . " From Project " . $project->project_name . "<br>" . $t2->description;
                $t2->setAttribute('payment_mode', $payment_mode);
                $t2->setAttribute('debit_amount', 0);
                $t2->setAttribute('credit_amount', number_format($t2->tran_amount));
                $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
            } elseif ($t2->cust_type == "transfer") {
                $from = Accounts::where('acc_id', $t2->tran_acc_id)->first();
                $to = Accounts::where('acc_id', $t2->cust_id)->first();
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                // return $from->acc_title;
                if ($t2->tran_type == 'debit') {
                    $s = "To Account: " . (string)$from->acc_title . " <- ";
                    $t2->description = $s . "From Account: " . (string)$to->acc_title . "<br>" . $t2->description;
                    $t2->setAttribute('debit_amount', number_format($t2->tran_amount));
                    $t2->setAttribute('credit_amount', 0);
                    $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
                } else {
                    $s = " From Account: " . (string)$from->acc_title . " -> ";
                    $t2->description = $s . "To Account: " . (string)$to->acc_title . "<br>" . $t2->description;
                    $t2->setAttribute('debit_amount', 0);
                    $t2->setAttribute('credit_amount', number_format($t2->tran_amount));
                    $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
                }
                $t2->setAttribute('payment_mode', $payment_mode);
            } elseif ($t2->cust_type == "expenses") {
                $exp = Expense::where('id', $t2->cust_id)->first();
                if ($t2->cheque_num != null) {
                    $payment_mode = 'cheque';
                } elseif ($t2->transfer_id != null) {
                    $payment_mode = 'transfer';
                } else {
                    $payment_mode = 'cash';
                }
                $s = "Paid From Account: " . (string)$to->acc_title . " to ";
                $t2->description = $s . " Personal expense " . $exp->expense_name . "<br>" . $t2->description;
                $t2->setAttribute('payment_mode', $payment_mode);
                $t2->setAttribute('debit_amount', number_format($t2->tran_amount));
                $t2->setAttribute('credit_amount', 0);
                $t2->tran_date = date('d-m-Y', strtotime($t2->tran_date));
            }
        }
        // }
        return response()->json(['data' => $tr], 200);
    }
    public function allInternalTransactions()
    {
        $tr = transfer::Where('cust_type', 'transfer')->get();
        // return $tr;
        // return $tr;
        if ($tr->isEmpty()) {
            return response()->json(["data" => $tr], 200);
        } else {
            foreach ($tr as $t) {

                $acc = Accounts::where('acc_id', $t->tran_acc_id)->first();
                $to = Accounts::where('acc_id', $t->cust_id)->first();
                if ($t->tran_type == "credit") {
                    $t->setAttribute('credit_amount', number_format($t->tran_amount));
                    $t->setAttribute('debit_amount', 0);
                } else {
                    $t->setAttribute('debit_amount', number_format($t->tran_amount));
                    $t->setAttribute('credit_amount', 0);
                }
                $t->setAttribute('from_account', $acc->acc_title);
                $t->setAttribute('from_bank', $acc->bank_name);
                $t->setAttribute('to_accout', $to->acc_title);
                $t->setAttribute('to_bank', $to->bank_name);
                $t->tran_date = date('d-m-Y', strtotime($t->tran_date));
            }
            return response()->json(['data' => $tr], 200);
        }
    }
    public function singleInternalTransaction($id)
    {
        $tr = transfer::where('tran_id', $id)->first();
        $acc1 = Accounts::where('acc_id', $tr->tran_acc_id)->first();
        $acc2 = Accounts::where('acc_id', $tr->cust_id)->first();
        if (!$tr) {
            return response()->json(["status_message" => "no Record Found"], 200);
        } else {
            $tr->setAttribute("from", $acc1->acc_id);
            $tr->setAttribute("to", $acc2->acc_id);
            return response()->json($tr, 200);
        }
    }
    public function singleTransaction($id)
    {
        $tr = transfer::where('tran_id', $id)->first();
        $rec = record::where('tran_id', $id)->first();
        $acc = Accounts::where('acc_id', $tr->tran_acc_id)->first();
        $tr->setAttribute('bank_name', $acc->bank_name);
        $tr->setAttribute('acc_id', $acc->acc_id);



        // return $rec;
        if (!$tr) {
            return response()->json(["status_message" => "no Record Found"], 200);
        } else {
            if ($tr->cust_type == "employee_salary") {
                $emp = Employee::where('emp_id', $tr->cust_id)->first();
                if ($emp) {
                    $tr->setAttribute('to_name', $emp->emp_name);
                }
            } elseif ($tr->cust_type == "transfer") {
                $acc = Accounts::where('acc_id', $tr->cust_id)->first();
                if ($acc) {

                    $tr->setAttribute('to_name', $acc->acc_title);
                }
            } elseif ($tr->cust_type == "project") {
                $proj = Project::where('project_id', $tr->cust_id)->first();
                $com = Company::where('company_id', $proj->company_id)->first();
                if ($com and $proj) {
                    $tr->setAttribute('company_id', $com->company_id);
                    $tr->setAttribute('to_name', $com->company_name);
                    $tr->setAttribute('project_name', $proj->project_name);
                }
            } elseif ($tr->cust_type == "other_expense") {
                $exp = Expense::where('Id', $tr->cust_id)->first();
                $proj = Project::where('project_id', $rec->project_id)->first();
                if ($exp and $proj) {
                    $tr->setAttribute('expense_id', $exp->Id);
                    $tr->setAttribute('to_name', $exp->expense_name);
                    $tr->setAttribute('project_name', $proj->project_name);
                }
            } elseif ($tr->cust_type == "expenses") {
                $exp = Expense::where('Id', $tr->cust_id)->first();
                $r = personalExpenseRecord::where('tran_id', $id)->first();
                $proj = expense_account::where('id', $r->exp_id)->first();
                if ($exp and $proj) {
                    $tr->setAttribute('expense_id', $exp->Id);
                    $tr->setAttribute('to_name', $exp->expense_name);
                    $tr->setAttribute('project_name', $proj->expense_acc_name);
                }
            } else {
                $con = Contractor::where('cont_id', $tr->cust_id)->first();
                $proj = Project::where('project_id', $rec->project_id)->first();
                if ($con and $proj) {
                    $tr->setAttribute('cont_id', $con->cont_id);

                    $tr->setAttribute('to_name', $con->cont_name);
                    $tr->setAttribute('project_name', $proj->project_name);
                }
            }

            // return $proj;



            // return $proj;



            return response()->json($tr, 200);
        }
    }
    public function expense(Request $request)
    {
        if ($request->expense_type == "employee_salary") {
            $rules = array(
                'receipt' => ['required'],
                // 'project'=>['required'],
                'payment_mode' => ['required'],
                'date' => ['required'],
                'amount' => ['required'],
                'account' => ['required'],
                'expense_type' => ['required'],
                // 'cont_name'=>['required'],
                // 'description'=>['required'],
            );
        } else {
            $rules = array(
                'receipt' => ['required'],
                'project' => ['required'],
                'payment_mode' => ['required'],
                'date' => ['required'],
                'amount' => ['required'],
                'account' => ['required'],
                'expense_type' => ['required'],
                // 'cont_name'=>['required'],
                // 'description'=>['required'],
            );
        }
        $boo = false;
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        }
        $check = (new TransferController)->amount($request->account, $request->amount);
        if ($check) {
            return response()->json(['status_message' => "This account does not have enough balance for this transaction"], 406);
        } else {
            if ($request->payment_mode === "cash") {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = $request->input('expense_type');
                $tr->tran_type = "debit";
                $tr->tran_acc_id = $request->input('account');
                $tr->cust_id = $request->input('cont_name');
                $tr->description = $request->input('description');
                $boo = $tr->save();
            } elseif ($request->payment_mode === "checque") {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = $request->input('expense_type');
                $tr->tran_type = "debit";
                $tr->tran_acc_id = $request->input('account');
                $tr->cust_id = $request->input('cont_name');
                $tr->cheque_num = $request->input('cheque_no');
                $tr->cheque_date = $request->input('cheque_date');
                $tr->description = $request->input('description');
                $boo = $tr->save();
            } else {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = $request->input('expense_type');
                $tr->tran_type = "debit";
                $tr->tran_acc_id = $request->input('account');
                $tr->cust_id = $request->input('cont_name');
                $tr->transfer_id = $request->input('reference_id');
                $tr->description = $request->input('description');
                $boo = $tr->save();
            }
            if ($boo) {
                $acc = Accounts::where('acc_id', $request->account)->first();
                $amount = $acc->current_balance - $request->amount;
                $acc->update(['current_balance' => $amount]);
                if ($request->expense_type == "expenses") {
                    $rec = new personalExpenseRecord();
                    $rec->tran_id = $tr->tran_id;
                    $rec->exp_id = $request->input('project');
                    $ch = $rec->save();
                    if ($ch) {
                        $e = expense_account::where('id', $request->project)->first();
                        // if($e){
                        $a = $e->amount + $request->amount;
                        $e->update(['amount' => $a]);

                        // }
                    }
                } elseif ($request->expense_type != "employee_salary") {
                    $rec = new record();
                    $rec->tran_id = $tr->tran_id;
                    $rec->project_id = $request->input('project');
                    $rec->save();
                }
                $inv = new invoice();
                $inv->inv_type = "Paying";
                $inv->Trans_id = $tr->tran_id;
                $inv->inv_no = $request->input('receipt');
                $inv->save();
                return response()->json([
                    'descriptions' => $tr->description,
                    'status_message' => "Expense successfully"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Expense unsuccessfull'
                ], 500);
            }
        }
    }
    public function recieving(Request $request)
    {
        $rules = array(
            'receipt' => ['required'],
            'project' => ['required'],
            'payment_mode' => ['required'],
            'date' => ['required'],
            'amount' => ['required'],
            'account' => ['required'],
            // 'description'=>['required']
        );
        $boo = false;
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            if ($request->payment_mode === "cash") {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = "project";
                $tr->tran_type = "credit";
                $tr->tran_acc_id = $request->input('account');
                $tr->description = $request->input('description');
                $tr->cust_id = $request->input('project');
                $boo = $tr->save();
            } elseif ($request->payment_mode === "checque") {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = "project";
                $tr->tran_type = "credit";
                $tr->tran_acc_id = $request->input('account');
                $tr->cust_id = $request->input('project');
                $tr->cheque_num = $request->input('cheque_no');
                $tr->cheque_date = $request->input('cheuqe_date');
                $tr->description = $request->input('description');

                $boo = $tr->save();
            } else {
                $tr = new transfer();
                $tr->tran_date = $request->input('date');
                $tr->tran_amount = $request->input('amount');
                $tr->cust_type = "project";
                $tr->tran_type = "credit";
                $tr->tran_acc_id = $request->input('account');
                $tr->cust_id = $request->input('project');
                $tr->transfer_id = $request->input('reference_id');
                $tr->description = $request->input('description');

                $boo = $tr->save();
                $proj = Project::where('project_id', $request->project)->first();
                $a = $request->amount + $proj->total_amount;
                // return $a;
                $proj->update(['total_amount' => $a]);
            }
            if ($boo) {
                $acc = Accounts::where('acc_id', $request->account)->first();
                $amount = $acc->current_balance + $request->amount;
                $acc->update(['current_balance' => $amount]);
                $inv = new invoice();
                $inv->inv_type = "Recieving";
                $inv->Trans_id = $tr->tran_id;
                $inv->inv_no = $request->input('receipt');
                $inv->save();
                return response()->json([
                    'company_id' => $acc->current_balance,
                    'status_message' => "Transfer successfully"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Transfer unsuccessfull'
                ], 500);
            }
        }
    }
    public function amount($user_account, $user_amount)
    {
        $c = Accounts::where("acc_id", $user_account)->first();
        if ($c->current_balance < $user_amount) {
            return true;
        }
        return false;
    }
    public function store(Request $request)
    {
        $rules = array(
            'from_account' => ['required'],
            'to_account' => ['required'],
            'ref_id' => ['required'],
            // 'date'=>['required'],
            'amount' => ['required'],
            // 'total_amount'=>['required'],
            // 'extra_amount'=>['required'],
            // 'payment_method'=>['required'],
            // 'contractor_name'=>['required'],
        );
        // return $request->all();
        $validate = Validator::make($request->all(), $rules);
        if ($request->input('from_account') == $request->input('to_account')) {
            return response()->json(["status_message" => "Accounts can't be same"], 406);
        }
        $check = (new TransferController)->amount($request->from_account, $request->amount);
        if ($check) {
            return response()->json(['status_message' => "From account does not have enough balance for transaction"], 406);
        }
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            // $to=Accounts::select('acc_id')->first();
            // $from=Accounts::select('acc_id')->first();
            // return $to;
            $tr = new transfer();
            // $tr->tran_type=$request->input('payment_method');
            $tr->tran_date = $request->input('transaction_date');
            $tr->tran_amount = $request->input('amount');
            $tr->cust_type = "transfer";
            $tr->tran_type = "credit";
            $tr->transfer_id = $request->input('ref_id');
            $tr->tran_acc_id = $request->input('from_account');
            $tr->cust_id = $request->input('to_account');
            $boo = $tr->save();
            $tr = new transfer();
            // $tr->tran_type=$request->input('payment_method');
            $tr->tran_date = $request->input('transaction_date');
            $tr->tran_amount = $request->input('amount');
            $tr->cust_type = "transfer";
            $tr->tran_type = "debit";
            $tr->transfer_id = $request->input('ref_id');
            $tr->tran_acc_id = $request->input('to_account');
            $tr->cust_id = $request->input('from_account');
            $boo = $tr->save();
            if ($boo) {
                $acc = Accounts::where('acc_id', $request->to_account)->first();
                $from = Accounts::where('acc_id', $request->from_account)->first();
                $from_amount = $from->current_balance - $request->amount;
                $amount = $acc->current_balance + $request->amount;
                $acc->update(['current_balance' => $amount]);
                $from->update(['current_balance' => $from_amount]);

                return response()->json([
                    // 'company_id'=>$comp->company_id,
                    'status_message' => "Transfer successfully"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'Transfer unsuccessfull'
                ], 500);
            }
        }
    }
}
