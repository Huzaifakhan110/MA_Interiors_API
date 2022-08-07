<?php

namespace App\Http\Controllers;
// use App\Http\Controllers\Expebs;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SpecificationsController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\expense_account_controller;

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceEmpController;
use App\personalExpenseRecord;
use App\expense_account;

use App\Project;
use Illuminate\Http\Request;

class daim extends Controller
{
    public function getData(Request $request)
    {
        if ($request->data == "project") {
            $result = Project::all();
        } else {
            $result = expense_account::all();
        }

        return response()->json($result, 200);
    }
    public function delete($table, $id)
    {
        $result = null;
        if ($table == 'contractor') {
            $result = (new ContractorController)->contDelete($id);
        } elseif ($table == 'company') {
            $result = (new CompanyController)->compDelete($id);
        } elseif ($table == 'employee') {
            $result = (new EmployeeController)->empDelete($id);
        } elseif ($table == 'account') {
            $result = (new AccountsController)->accDelete($id);
        } elseif ($table == 'project' || $table == 'project_dash') {
            $result = (new ProjectController)->projDelete($id);
        } elseif ($table == 'user') {
            $result = (new AuthController)->deleteUser($id);
        } elseif ($table == 'transaction') {
            $result = (new TransferController)->transactionDelete($id);
        } elseif ($table == 'expense_account') {
            $result = (new expense_account_controller)->deleteExpense($id);
        } elseif ($table == 'other_expense') {
            $result = (new ExpenseController)->destroy($id);
        } elseif ($table == 'currency') {
            $result = (new CurrencyController)->destroy($id);
        }
        return response()->json($result, 200);
    }
}
