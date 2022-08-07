<?php

namespace App\Http\Controllers;

use App\Accounts;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\currency;



class CurrencyController extends Controller
{
    public function allCurrency()
    {
        $currency = currency::all();
        if ($currency->isEmpty()) {
            $data = array(
                'status_message' => 'No data found'
            );
            $code = 200;
        } else {
            $data = $currency;
            $code = 200;
        }
        return response()->json($data, $code);
    }
    public function destroy($id)
    {
        $exp = currency::where('currency_id', $id)->first();
        if (!$exp) {
            return response()->json(['status_message' => 'no data found'], 404);
        } else {
            $tr = Accounts::where('currency', $exp->currency_id)->get();
            if ($tr->isEmpty()) {
                $exp->delete();
                return response()->json(['status_message' => 'record has been deleted', 'status_code' => 200], 200);
            }

            return response()->json(['status_message' => 'record has Account associated', 'status_code' => 202], 200);
        }
    }
    public function allCurrencyTable()
    {
        $currency = currency::all();
        if ($currency->isEmpty()) {
            $data = $currency;

            $code = 200;
        } else {
            $data = $currency;
            $code = 200;
        }
        return response()->json(['data' => $data], $code);
    }
    public function store(Request $request)
    {
        $rules = array(
            'name' => ['required', 'max:50'],
        );
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 406);
        } else {
            $comp = new currency();
            $comp->currency_name = $request->input('name');
            $boo = $comp->save();
            if ($boo) {
                return response()->json([
                    'currecy_id' => $comp->company_id,
                    'status_message' => "currency added"
                ], 200);
            } else {
                return response()->json([
                    'status_message' => 'unsuccessfull'
                ], 500);
            }
        }
    }
}
