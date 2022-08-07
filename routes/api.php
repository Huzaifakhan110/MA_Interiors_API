<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceEmpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\expense_account_controller;

use App\Http\Controllers\daim;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// if (env('APP_ENV') === 'production') {
//     URL::forceSchema('https');
// }

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::POST('expense/account/add', [expense_account_controller::class, 'store']);
Route::GET('expense/account/personal', [expense_account_controller::class, 'getExpenseAccount']);
Route::GET('expense/personal/account', [expense_account_controller::class, 'getExpense']);
Route::GET('expense/personal/account/transactions/{id}', [expense_account_controller::class, 'getTabledata']);

Route::GET('expense/account/personal/{id}', [expense_account_controller::class, 'getSingledata']);


// user routes
Route::POST('user/register', [AuthController::class, 'register']);
Route::POST('login', [AuthController::class, 'login']);
Route::get('users/single/{id}', [AuthController::class, 'getSingleUser']);
Route::post('users/single/update/{id}', [AuthController::class, 'updateUser']);




// Route::POST('user/register',[UserController::class,'store']);

Route::get('users', [AuthController::class, 'getUsers']);
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::POST('logout', [AuthController::class, 'logout']);
    Route::get('user/roles', [AuthController::class, 'getRole']);
});
// Route::group(['middleware'=>['auth:sanctum']],function(){

// });
// company routes
Route::POST('company/register', [CompanyController::class, 'store']);
Route::POST('company/update', [CompanyController::class, 'update']);
Route::GET('company', [CompanyController::class, 'allCompany']);

// Route::GET('company',[CompanyController::class,'allCompany']);
Route::POST('company/single', [CompanyController::class, 'getCompany']);
Route::GET('company/single/{id}', [CompanyController::class, 'compID']);

Route::DELETE('company/delete/{id}', [CompanyController::class, 'compDelete']);
Route::POST('company/update/{id}', [CompanyController::class, 'compUpdate']);



//contractor routes
Route::POST('contractor/register', [ContractorController::class, 'store']);
Route::GET('contractor', [ContractorController::class, 'allContractor']);
Route::GET('contractor/all', [ContractorController::class, 'allContractorfortable']);
Route::GET('contractor/search/{id}', [ContractorController::class, 'show']);

Route::POST('contractor/update/{id}', [ContractorController::class, 'contUpdate']);
Route::DELETE('contractor/delete/{id}', [ContractorController::class, 'contDelete']);
Route::DELETE('delete/{table}/{id}', [daim::class, 'delete']);
Route::post('getdata', [daim::class, 'getData']);




Route::GET('contractor/account/{id}', [ContractorController::class, 'singleContractorTransactionDetails']);
Route::GET('contractor/expense/{id}', [ContractorController::class, 'singleContExpense']);


//attendance
Route::GET('attendance', [AttendanceEmpController::class, 'allEmployeeAttendance']);
Route::POST('attendance/add', [AttendanceEmpController::class, 'store']);



//specs routes
Route::POST('specification/register', [SpecificationsController::class, 'store']);
Route::GET('specification', [SpecificationsController::class, 'allSpecifications']);

//currency routes
Route::POST('currency/register', [CurrencyController::class, 'store']);
Route::GET('currency', [CurrencyController::class, 'allCurrency']);
Route::GET('currencyTable', [CurrencyController::class, 'allCurrencyTable']);



// employee routes
Route::POST('employee/register', [EmployeeController::class, 'store']);
Route::GET('employee', [EmployeeController::class, 'allEmployee']);
Route::GET('employee/all', [EmployeeController::class, 'allEmployeeSelect']);
Route::DELETE('employee/delete/{id}', [EmployeeController::class, 'empDelete']);
Route::POST('employee/update/{id}', [EmployeeController::class, 'empUpdate']);
Route::GET('attendance/emp/{id}', [EmployeeController::class, 'empAttendance']);
Route::GET('employee/single/{id}', [EmployeeController::class, 'singleEmployee']);
Route::GET('employee/account/{id}', [EmployeeController::class, 'empAccount']);




// account routes
Route::POST('account/register', [AccountsController::class, 'store']);
Route::GET('account', [AccountsController::class, 'allAccounts']);
Route::GET('allaccount', [AccountsController::class, 'allAccountsTable']);
Route::GET('account/transaction/{id}', [AccountsController::class, 'accountTransaction']);
Route::DELETE('account/delete/{id}', [AccountsController::class, 'accDelete']);
Route::POST('account/update/{id}', [AccountsController::class, 'accUpdate']);
Route::POST('getaccountbyid', [AccountsController::class, 'singleAcc']);



// Route::GET('daim',[daim::class,'hello']);

// project routes  
Route::POST('project/register', [ProjectController::class, 'store']);
Route::GET('project/company/{id}', [ProjectController::class, 'comp_project']);
Route::GET('project/single/{id}', [ProjectController::class, 'singleprojectdetails']);
Route::GET('project/cont/{id}', [ProjectController::class, 'projectDetailsCont']);

Route::GET('project/account/{id}', [ProjectController::class, 'singleprojectTransactionDetails']);
Route::GET('project/edit/{id}', [ProjectController::class, 'singleproject']);


Route::GET('project', [ProjectController::class, 'project_details']);
Route::GET('project/all', [ProjectController::class, 'projectData']);
Route::GET('dasboard/project', [ProjectController::class, 'projDashboard']);

Route::GET('project/all/detail', [ProjectController::class, 'projectDataTable']);

Route::POST('project/update', [ProjectController::class, 'update']);
Route::DELETE('project/delete/{id}', [ProjectController::class, 'projDelete']);
Route::POST('project/update/{id}', [ProjectController::class, 'projUpdate']);

// Route::GET('projet',[AccountsController::class,'allAccounts']);

Route::post('expense/add', [ExpenseController::class, 'store']);

Route::get('expense', [ExpenseController::class, 'allExpense']);
Route::get('expenseTable', [ExpenseController::class, 'allExpenseTable']);


Route::post('transfer/add', [TransferController::class, 'store']);
Route::post('recieving/add', [TransferController::class, 'recieving']);
Route::post('invoice/add', [TransferController::class, 'expense']);
Route::get('dasboard/transactions', [TransferController::class, 'totalDashboard']);
Route::post('report', [TransferController::class, 'report']);


Route::get('single/transaction/{id}', [TransferController::class, 'singleTransaction']);
Route::get('single/transaction/iternal/{id}', [TransferController::class, 'singleInternalTransaction']);
Route::post('transaction/iternal/update/{id}', [TransferController::class, 'internalTransactionUpdate']);


Route::get('allTransaction', [TransferController::class, 'allInternalTransactions']);
Route::get('allTransaction/table', [TransferController::class, 'getAllTransactions']);


//Invoice

Route::get('expense/receipt', [InvoiceController::class, 'getExpenseInvoice']);
Route::get('recieve/receipt', [InvoiceController::class, 'getRecievingInvoice']);

//Departmen

Route::get('department', [DepartmentController::class, 'getDepartments']);
Route::post('department/add', [DepartmentController::class, 'store']);

// URL::forceScheme('https');
