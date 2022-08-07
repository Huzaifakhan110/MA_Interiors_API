<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\invoice;

class InvoiceController extends Controller
{
    public function getExpenseInvoice(){
        $inv=invoice::where("inv_type","Paying")->orderBy('inv_no','desc')->first();
        if($inv==null){
            return response()->json(['inv_no'=>1],200);
        }
        else{
            return response()->json($inv,200);
        }
    }
    public function getRecievingInvoice(){
        $inv=invoice::where("inv_type","Recieving")->orderBy('inv_no','desc')->first();
        if($inv==null){
            return response()->json(['inv_no'=>1],200);
        }
        else{
            return response()->json($inv,200);
        }
    }
}
