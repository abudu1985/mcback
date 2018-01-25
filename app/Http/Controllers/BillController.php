<?php
/**
 * Created by PhpStorm.
 * User: Anna
 * Date: 10.12.2017
 * Time: 22:01
 */

namespace App\Http\Controllers;
use App\Bill;
//use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
//    public function postQuote(Request $request)
//    {
//        $user = JWTAuth::parseToken()->toUser();
//        $quote = new Quote();
//        $quote->content = $request->input('content');
//        $quote->save();
//        return response()->json(['quote' => $quote], 201);
//    }
    public function getBill()
    {
        $bill = Bill::all();
        foreach ($bill as $b) {
            return response()->json(['value' => $b->value, 'currency' => $b->currency], 200);
        }
    }

    public function getRateFromPrivate()
    {
      $rates = [];
      $rate = [];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.privatbank.ua/p24api/pubinfo?json&exchange&cioursd=5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $res = json_decode($response);
            foreach ($res as $r){
                $rates[$r->ccy] = number_format(1/$r->buy, 6, '.', ',');
            }
            $rate['date'] = date("Y-m-d");
            $rate['rates'] = $rates;
            return $rate;
        }
    }

    public function getRateFromFinance()
    {
        $rates = [];
        $rate = [];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://resources.finance.ua/ru/public/currency-cash.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return $rates;
        } else {
            $res = json_decode($response);
            $rate['date'] = $res->date;
            $org = $res->organizations;
            foreach ($org as $o){
                foreach ($o->currencies as $k=>$v){
                    $rates[$k] = number_format(1/$v->bid, 6, '.', ',');
                }
            }
            $rate['rates'] = $rates;
            return $rate;
        }
    }
    public function putBill(Request $request)
    {
        $bill = Bill::all()->first();
//        var_dump( $request->input('currency'));
//        exit();
        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], 404);
        }
        $bill->value = $request->input('value');
        $bill->save();
        return response()->json(['bill' => $bill], 200);
    }
//    public function deleteQuote($id)
//    {
//        $quote = Quote::find($id);
//        $quote->delete();
//        return response()->json(['message' => 'Quote deleted'], 200);
//    }
}