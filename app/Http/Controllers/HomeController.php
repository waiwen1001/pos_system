<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
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
      $user_list = User::get();
      $user = Auth::user();

      $subtotal = 0;
      $discount = 0;
      $total = 0;
      $real_total = 0;
      $payment = 0;
      $balance = 0;
      $transaction_id = null;

      $pending_transaction = transaction::where('completed', null)->first();
      if($pending_transaction)
      {
        $transaction_summary = $this->transaction_summary($pending_transaction);

        $pending_transaction->items_list = $transaction_summary->items_list;
        $subtotal = $transaction_summary->subtotal;
        $discount = $transaction_summary->discount;
        $total = $transaction_summary->total;
        $real_total = $transaction_summary->real_total;

        $payment = $pending_transaction->payment;
        $balance = $pending_transaction->balance;

        $transaction_id = $pending_transaction->id;
      }

      $completed_transaction = transaction::where('completed', 1)->get();

      foreach($completed_transaction as $completed)
      {
        $completed->void_by_name = "";
        if($completed->void == 1 && $completed->void_by)
        {
          foreach($user_list as $user_detail)
          {
            if($user_detail->id == $completed->void_by)
            {
              $completed->void_by_name = $user_detail->name;
              break;
            }
          }
        }
      }

      return view('front.index', compact('user', 'pending_transaction', 'subtotal', 'discount', 'total', 'real_total', 'payment', 'balance', 'transaction_id', 'completed_transaction'));
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
      $transaction_summary->real_total = $total;
      $transaction_summary->payment = number_format($transaction->payment, 2);
      $transaction_summary->balance = number_format($transaction->balance, 2);
      $transaction_summary->transaction_id = $transaction->id;

      return $transaction_summary;
    }

    public function submitDeleteItem(Request $request)
    {
      transaction_detail::where('id', $request->item_id)->delete();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function submitTransaction(Request $request)
    {
      $user = Auth::user();

      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();
      $received_cash = $request->received_cash;

      $total = 0;
      $subtotal = 0;
      $total_discount = 0;
      $balance = 0;

      foreach($transaction_detail as $detail)
      {
        $total = $total + $detail->total;
        $subtotal = $subtotal + $detail->subtotal;
        $total_discount = $total_discount + $detail->total_discount;
      }

      if($received_cash > $total)
      {
        $balance = $received_cash - $total;

        transaction::where('id', $request->transaction_id)->update([
          'subtotal' => $subtotal,
          'total_discount' => $total_discount,
          'payment' => $received_cash,
          'payment_type' => "cash",
          'balance' => $balance,
          'total' => $total,
          'completed' => 1,
          'completed_by' => $user->id,
          'transaction_date' => date('Y-m-d H:i:s', strtotime(now()))
        ]);

        $completed_transaction = transaction::where('id', $request->transaction_id)->first();

        $completed_transaction->total_text = number_format($completed_transaction->total, 2);
        $completed_transaction->payment_text = number_format($completed_transaction->payment, 2);
        $completed_transaction->balance_text = number_format($completed_transaction->balance, 2);
        $completed_transaction->transaction_date_text = date('d M Y g:i:s A', strtotime($completed_transaction->transaction_date));

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->balance = number_format($balance, 2);
        $response->completed_transaction = $completed_transaction;

        return response()->json($response);
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Received cash is lesser than transaction price.";

        return response()->json($response);
      }
    }

    public function submitVoidTransaction(Request $request)
    {
      $user = Auth::user();

      transaction::where('id', $request->transaction_id)->update([
        'void' => 1,
        'void_by' => $user->id,
        'void_date' => date('Y-m-d H:i:s', strtotime(now()))
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->void_by_name = $user->name;

      return response()->json($response);
    }

    public function submitUnvoidTransaction(Request $request)
    {
      $user = Auth::user();

      transaction::where('id', $request->transaction_id)->update([
        'void' => null,
        'void_by' => null,
        'void_date' => null
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function clearTransaction(Request $request)
    {
      transaction_detail::where('transaction_id', $request->transaction_id)->delete();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }
}

