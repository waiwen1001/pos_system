<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\product;
use App\transaction;
use App\transaction_detail;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      $user = Auth::user();

      $subtotal = 0;
      $discount = 0;
      $total = 0;
      $payment = 0;
      $balance = 0;

      $pending_transaction = transaction::where('completed', null)->where('user_id', $user->id)->first();
      if($pending_transaction)
      {
        $transaction_summary = $this->transaction_summary($pending_transaction);

        $pending_transaction->items_list = $transaction_summary->items_list;
        $subtotal = $transaction_summary->subtotal;
        $discount = $transaction_summary->discount;
        $total = $transaction_summary->total;

        $payment = $pending_transaction->payment;
        $balance = $pending_transaction->balance;
      }

      return view('front.index', compact('user', 'pending_transaction', 'subtotal', 'discount', 'total', 'payment', 'balance'));
    }

    public function searchAndAddItem(Request $request)
    {
      $barcode = $request->barcode;

      if(!$barcode)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->title = "Barcode is empty";
        $response->message = "Please key in barcode before proceed";

        return response()->json($response);
      }
      else
      {
        $product = product::where('barcode', $barcode)->first();

        if(!$product)
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->title = "Product not found";
          $response->message = "Product {$barcode} is not found";

          return response()->json($response);
        }

        $user = Auth::user();
        $transaction = transaction::where('completed', null)->where('user_id', $user->id)->first();

        if(!$transaction)
        {
          $transaction = transaction::create([
            'transaction_no' => uniqid(),
            'user_id' => $user->id
          ]);

          transaction_detail::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'price' => $product->price,
            'quantity' => 1,
            'discount' => 0,
            'subtotal' => $product->price,
            'total' => $product->price
          ]);
        }
        else
        {
          $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->where('product_id', $product->id)->where('void', null)->first();

          if($transaction_detail)
          {
            transaction_detail::where('id', $transaction_detail->id)->update([
              'quantity' => $transaction_detail->quantity + 1,
              'subtotal' => $transaction_detail->subtotal + $product->price,
              'total' => $transaction_detail->total + $product->price
            ]);
          }
          else
          {
            transaction_detail::create([
              'transaction_id' => $transaction->id,
              'product_id' => $product->id,
              'product_name' => $product->product_name,
              'price' => $product->price,
              'quantity' => 1,
              'discount' => 0,
              'subtotal' => $product->price,
              'total' => $product->price
            ]);
          }
        }

        $transaction_summary = $this->transaction_summary($transaction);

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->transaction_summary = $transaction_summary;
        $response->product = $product;

        return response()->json($response);
      }
    }

    public function transaction_summary($transaction)
    {
      $subtotal = 0;
      $discount = 0;
      $total = 0;

      $items_list = transaction_detail::where('transaction_id', $transaction->id)->where('void', null)->get();
      if(count($items_list) > 0)
      {
        foreach($items_list as $item)
        {
          $item->subtotal_text = number_format($item->subtotal, 2);

          $subtotal = $subtotal + $item->subtotal;
          $discount = $discount + $item->discount;
          $total = $total + $item->total;
        }
      }

      $transaction_summary = new \stdClass();
      $transaction_summary->items_list = $items_list;
      $transaction_summary->subtotal = number_format($subtotal, 2);
      $transaction_summary->discount = number_format($discount, 2);
      $transaction_summary->total = number_format($total, 2);
      $transaction_summary->payment = number_format($transaction->payment, 2);
      $transaction_summary->balance = number_format($transaction->balance, 2);

      return $transaction_summary;
    }
}
