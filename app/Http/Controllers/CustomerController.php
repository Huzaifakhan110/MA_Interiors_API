<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(){
        return "ahsan";
    }
    public function index(Request $request)
    {
        $rules=array(
            'name'=>['required','max:50']
        );
        // validating the data 
        $validate=Validator::make($request->all(),$rules);
         
         if($validate->fails()){
            return response()->json($validate->errors(),406);
        }
        else{
            $Cust = new Customer();
            $Cust->name=$request->input('name');// taking input name field
            $boo=$Cust->save();
            if($boo){
                return response()->json([
                    'company_id'=>$Cust->id,
                    'status_message'=>"Company has been registered successfully"
                ],200);
            }
            
            else{
                return response()->json([
                    'status_message'=>'Company register unsuccessfull'
                ],500);
            }
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        $client=Customer::all();
        if($client->isEmpty())
        {
            $data=array(
                'status_message'=>'No data found'
            );
            $code=404;
        }
        else{
            $data=array(
                $client
            );
            $code=200;
        }
        return response()->json($data,$code);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
