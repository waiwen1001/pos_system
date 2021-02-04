<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\product;
use App\transaction;
use App\transaction_detail;
use App\cashier;
use App\voucher;
use App\session;

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
      $ip = $_SERVER['REMOTE_ADDR'];

      $user_list = User::get();
      $user = Auth::user();

      $subtotal = 0;
      $discount = 0;
      $have_discount = 0;
      $total = 0;
      $real_total = 0;
      $payment = 0;
      $balance = 0;
      $round_off = 0;
      $transaction_id = null;

      $pending_transaction = transaction::where('completed', null)->first();
      if($pending_transaction)
      {
        if($pending_transaction->total_discount)
        {
          $have_discount = 1;
        }

        $transaction_summary = $this->transaction_summary($pending_transaction);

        $pending_transaction->items_list = $transaction_summary->items_list;
        $subtotal = $transaction_summary->subtotal;
        $total = $transaction_summary->total;
        $discount = $transaction_summary->total_discount;
        $real_total = $transaction_summary->real_total;
        $round_off = $transaction_summary->round_off;

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

      $cashier_ip = $_SERVER['REMOTE_ADDR'];
      // get latest cashier, incase session out, some opening do not have closing
      $cashier = cashier::where('ip', $cashier_ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      $opening = 0;
      if($cashier)
      {
        $opening = 1;

        if($cashier->opening_by == null)
        {
          $now = date('Y-m-d H:i:s', strtotime(now()));

          cashier::where('id', $cashier->id)->update([
            'opening_by' => $user->id,
            'opening_date_time' => $now
          ]);
        }
      }

      return view('front.index', compact('user', 'pending_transaction', 'subtotal', 'discount', 'have_discount', 'total', 'real_total', 'round_off', 'payment', 'balance', 'transaction_id', 'completed_transaction', 'opening'));
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
          $session = session::where('closed', null)->first();

          $session_id = null;
          if($session)
          {
            $session_id = $session->id;
          }

          $transaction = transaction::create([
            'session_id' => $session_id,
            'transaction_no' => uniqid(),
            'user_id' => $user->id
          ]);

          transaction_detail::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'price' => round($product->price, 2),
            'quantity' => 1,
            'discount' => 0,
            'subtotal' => round($product->price, 2),
            'total' => round($product->price, 2)
          ]);
        }
        else
        {
          $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->where('product_id', $product->id)->where('void', null)->first();

          if($transaction_detail)
          {
            transaction_detail::where('id', $transaction_detail->id)->update([
              'quantity' => $transaction_detail->quantity + 1,
              'subtotal' => round($transaction_detail->subtotal + $product->price, 2),
              'total' => round($transaction_detail->total + $product->price, 2)
            ]);
          }
          else
          {
            transaction_detail::create([
              'transaction_id' => $transaction->id,
              'product_id' => $product->id,
              'product_name' => $product->product_name,
              'price' => round($product->price, 2),
              'quantity' => 1,
              'discount' => 0,
              'subtotal' => round($product->price, 2),
              'total' => round($product->price, 2)
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
      $total = 0;
      $round_off = 0;

      $discount = $transaction->total_discount;
      $round_off = $transaction->round_off;

      $items_list = transaction_detail::where('transaction_id', $transaction->id)->where('void', null)->get();
      if(count($items_list) > 0)
      {
        foreach($items_list as $item)
        {
          $item->subtotal_text = number_format($item->subtotal, 2);

          $subtotal = $subtotal + $item->subtotal;
          $total = $total + $item->total;
        }
      }

      if($discount)
      {
        $total = $total - $discount;
      }

      if($total < 0)
      {
        $total = 0;
      }

      $total_summary = $this->roundDecimal($total);

      $round_off = $total_summary->round_off;
      $total = $total_summary->final_total;

      $transaction_summary = new \stdClass();
      $transaction_summary->items_list = $items_list;
      $transaction_summary->subtotal = number_format($subtotal, 2);
      $transaction_summary->total = number_format($total, 2);
      $transaction_summary->total_discount = number_format($discount, 2);
      $transaction_summary->round_off = number_format($round_off, 2);
      $transaction_summary->real_total = $total;
      $transaction_summary->payment = number_format($transaction->payment, 2);
      $transaction_summary->balance = number_format($transaction->balance, 2);
      $transaction_summary->transaction_id = $transaction->id;

      return $transaction_summary;
    }

    public function submitDeleteItem(Request $request)
    {
      transaction_detail::where('id', $request->item_id)->delete();

      $transaction = transaction::where('id', $request->transaction_id)->first();

      $transaction_summary = $this->transaction_summary($transaction);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->transaction_summary = $transaction_summary;

      return response()->json($response);
    }

    public function submitTransaction(Request $request)
    {
      $user = Auth::user();

      $transaction = transaction::where('id', $request->transaction_id)->first();
      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();

      $payment_type = $request->payment_type;

      $received_cash = 0;
      $invoice_no = null;
      $total = 0;
      $subtotal = 0;
      $total_discount = 0;
      $balance = null;
      $round_off = null;

      $valid_payment = false;

      foreach($transaction_detail as $detail)
      {
        $total = round($total, 2) + round($detail->total, 2);
        $subtotal = round($subtotal, 2) + round($detail->subtotal, 2);
        $total_discount = round($total_discount, 2) + round($detail->total_discount, 2);
      }

      if($transaction->total_discount)
      {
        $total_discount = $transaction->total_discount;
      }

      $total = $total - $total_discount;

      if($payment_type == "cash")
      {
        $received_cash = $request->received_cash;

        $total_summary = $this->roundDecimal($total);
        $total = $total_summary->final_total;

        if(round($received_cash, 2) >= round($total, 2))
        {
          $valid_payment = true;
        }
        
        $round_off = $total_summary->round_off;
        $balance = $received_cash - $total;
      } 
      else
      {
        $invoice_no = $request->invoice_no;
        if($invoice_no)
        {
          $valid_payment = true;
        }
      }
      
      if($valid_payment)
      {
        transaction::where('id', $request->transaction_id)->update([
          'invoice_no' => $invoice_no,
          'subtotal' => round($subtotal, 2),
          'total_discount' => round($total_discount, 2),
          'payment' => round($received_cash, 2),
          'payment_type' => $payment_type,
          'balance' => round($balance, 2),
          'total' => round($total, 2),
          'round_off' => round($round_off, 2),
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

      transaction::where('id', $request->transaction_id)->update([
        'total_discount' => 0,
        'voucher_id' => null
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function editInvoiceNo(Request $request)
    {
      transaction::where('id', $request->transaction_id)->update([
        'invoice_no' => $request->invoice_no
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function editQuantity(Request $request)
    {
      $transaction_detail = transaction_detail::where('id', $request->item_id)->first();

      $quantity = $transaction_detail->quantity;
      if($request->type == "plus")
      {
        $quantity--;
      }
      elseif($request->type == "minus")
      {
        $quantity++;
      }

      $subtotal = $transaction_detail->price * $quantity;
      $total = $transaction_detail->price * $quantity;

      if($quantity == 0)
      {
        transaction_detail::where('id', $request->item_id)->delete();
      }
      else
      {
        transaction_detail::where('id', $request->item_id)->update([
          'quantity' => $quantity,
          'subtotal' => round($subtotal, 2),
          'total' => round($total, 2)
        ]);
      }

      $transaction = transaction::where('id', $transaction_detail->transaction_id)->first();

      $transaction_summary = $this->transaction_summary($transaction);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->quantity = $quantity;
      $response->transaction_summary = $transaction_summary;
      $response->subtotal = number_format($subtotal, 2);
      $response->total = number_format($total, 2);

      return response()->json($response);
    }

    public function submitVoucher(Request $request)
    {
      $active_voucher = voucher::where('code', $request->code)->where('active', 1)->first();

      if($active_voucher)
      {
        $transaction = transaction::where('id', $request->transaction_id)->first();

        if(!$transaction)
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "No transaction";

          return response()->json($response);
        }

        $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->get();

        $total = 0;

        foreach($transaction_detail as $value)
        {
          $total = $total + $value->total;
        }

        $total_discount = 0;
        if($active_voucher->type == "fixed")
        {
          $total_discount = $active_voucher->amount;
        }
        elseif($active_voucher->type == "percentage")
        {
          $total_discount = ($total * $active_voucher->amount / 100); 
        }

        $total = $total - $total_discount;

        if($total < 0)
        {
          $total = 0;
        }

        $total_summary = $this->roundDecimal($total);
        $total = $total_summary->final_total;
        $round_off = $total_summary->round_off;

        transaction::where('id', $transaction->id)->update([
          'total_discount' => $total_discount,
          'voucher_id' => $active_voucher->id
        ]);

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->voucher_name = $active_voucher->name;
        $response->total_discount = number_format($total_discount, 2);
        $response->total = number_format($total, 2);
        $response->real_total = $total;
        $response->round_off = number_format($round_off, 2);

        return response()->json($response);
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Invalid voucher";

        return response()->json($response);
      }
    }

    public function removeVoucher(Request $request)
    {
      transaction::where('id', $request->transaction_id)->update([
        'total_discount' => 0,
        'voucher_id' => null
      ]);

      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();
      $total = 0;
      foreach($transaction_detail as $value)
      {
        $total = $total + $value->total;
      }

      $total_summary = $this->roundDecimal($total);
      $total = $total_summary->final_total;
      $round_off = $total_summary->round_off;

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->total = number_format($total, 2);
      $response->real_total = $total;
      $response->round_off = number_format($round_off, 2);

      return response()->json($response);
    }

    public function roundDecimal($total)
    {
      $total_summary = new \stdClass();
      $total_summary->final_total = $total;
      $total_summary->round_off = 0;

      $total_floor = floor($total);
      $decimal = round($total - $total_floor, 2);

      $decimal_array = explode(".", $decimal);
      if(count($decimal_array) == 2)
      {
        $decimal_number = $decimal_array[1];
        if(strlen($decimal_number) == 2)
        {
          $round_decimal = $decimal_number[1];

          $round_off = 0;
          if($round_decimal >= 0 && $round_decimal <= 2)
          {
            $round_off = 0;
          }
          elseif($round_decimal >= 3 && $round_decimal <= 4)
          {
            $round_off = 0.05;
          }
          elseif($round_decimal >= 5 && $round_decimal <= 7)
          {
            $round_off = -0.05;
          }
          else
          {
            $round_off = 0;
          }

          $final_decimal = round($decimal, 1) + $round_off;

          $final_total = round($total_floor + $final_decimal, 2);
          $total_round_off = round($final_total - $total, 2);

          $total_summary->final_total = $final_total;
          $total_summary->round_off = $total_round_off;
          
          return $total_summary;
        }
        else
        {
          return $total_summary;
        }
      }
      else
      {
        return $total_summary;
      }
    }

    public function submitOpening(Request $request)
    {
      $user = Auth::user();
      $cashier_ip = $_SERVER['REMOTE_ADDR'];
      $cashier = cashier::where('ip', $cashier_ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      $now = date('Y-m-d H:i:s', strtotime(now()));

      if(!$cashier)
      {
        $session = session::where('closed', null)->first();

        if(!$session)
        {
          $session = session::create([
            'ip' => $cashier_ip,
            'opening_date_time' => $now
          ]);
        }

        cashier::create([
          'ip' => $cashier_ip,
          'session_id' => $session->id,
          'opening' => 1,
          'opening_amount' => $request->opening_amount,
          'opening_by' => $user->id,
          'opening_date_time' => $now
        ]);
      }
      else
      {
        cashier::where('id', $cashier->id)->update([
          'opening' => 1,
          'opening_amount' => $request->opening_amount,
          'opening_by' => $user->id,
          'opening_date_time' => $now
        ]);
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function submitClosing(Request $request)
    {
      $user = Auth::user();
      $cashier_ip = $_SERVER['REMOTE_ADDR'];
      $cashier = cashier::where('ip', $cashier_ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      $now = date('Y-m-d H:i:s', strtotime(now()));

      if(!$cashier)
      {
        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";

        return response()->json($response);
      }
      else
      {
        $session = session::where('closed', null)->first();

        if(!$session)
        {
          $session = session::create([
            'ip' => $cashier_ip,
            'opening_date_time' => $now
          ]);
        }

        cashier::where('id', $cashier->id)->update([
          'closing' => 1,
          'closing_amount' => $request->closing_amount,
          'closing_by' => $user->id,
          'closing_date_time' => $now
        ]);

        cashier::create([
          'session_id' => $session->id,
          'ip' => $cashier_ip,
          'opening' => 1,
          'opening_amount' => $request->closing_amount,
        ]);
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function submitDailyClosing(Request $request)
    {
      $session_id = null;
      $session = session::where('closed', null)->first();

      if($session)
      {
        $session_id = $session->id;
      }
      $transaction = transaction::where('completed', null)->where('session_id', $session_id)->first();

      if($transaction)
      {
        $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->get();

        if(count($transaction_detail) > 0)
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "Please clear the transaction before doing closing";

          return response()->json($response);
        }
      }

      $credentials = [
        'user_type' => 1,
        'email' => $request->email,
        'password' => $request->password
      ];

      if (Auth::validate($credentials))
      {
        $manager = User::where('email', $request->email)->first();
        $cashier_ip = $_SERVER['REMOTE_ADDR'];
        $now = date('Y-m-d H:i:s', strtotime(now()));

        $cashier = cashier::where('session_id', $session_id)->where('ip', $cashier_ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

        if($cashier)
        {
          cashier::where('id', $cashier->id)->update([
            'closing' => 1,
            'closing_amount' => $request->closing_amount,
            'closing_by' => $manager->id,
            'closing_date_time' => $now
          ]);
        }
        
        if($session)
        {
          session::where('id', $session_id)->update([
            'closing_date_time' => $now,
            'closed' => 1,
          ]);
        }

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";

        return response()->json($response);
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Account invalid";

        return response()->json($response);
      }

    }

    public function myIP()
    {
      dd($_SERVER['REMOTE_ADDR']);
    }
}

