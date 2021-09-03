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
use App\cash_float;
use App\pos_cashier;
use App\shortcut_key;
use App\Invoice_sequence;
use App\profile;
use App\refund;
use App\refund_detail;
use App\delivery;
use App\delivery_detail;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->branch_id = env('branch_id');
        $this->branchSyncURL = env('branchSyncURL');
        $this->branchProductSyncURL = env('branchProductSyncURL');
        $this->hqProductSyncURL = env('hqProductSyncURL');
        $this->hqProductSyncCompletedURL = env('hqProductSyncCompletedURL');

        $this->middleware('auth', ['except' => ['testing']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      $user_list = User::where('removed', null)->get();
      $user = Auth::user();

      $branch_name = null;
      $branch_address = null;
      $contact_number = null;
      $profile = profile::first();
      if($profile)
      {
        $branch_name = $profile->branch_name;
        $branch_address = $profile->address;
        $contact_number = $profile->contact_number;
      }

      $subtotal = 0;
      $discount = 0;
      $have_discount = 0;
      $total = 0;
      $real_total = 0;
      $payment = 0;
      $balance = 0;
      $round_off = 0;
      $transaction_id = null;
      $voucher_name = null;
      $item_quantity = 0;

      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      $pending_transaction = array();
      if($session)
      {
        $pending_transaction = transaction::where('session_id', $session->id)->where('completed', null)->where('ip', $this->ip)->first();
      }
      
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

        if($pending_transaction->voucher_code)
        {
          $voucher_detail = voucher::where('code', $pending_transaction->voucher_code)->first();
          if($voucher_detail)
          {
            $voucher_name = $voucher_detail->name;
          }
        }

        $item_quantity = transaction_detail::where('transaction_id', $pending_transaction->id)->count();
      }

      $completed_transaction = [];
      if($session)
      {
        $completed_transaction = transaction::where('completed', 1)->where('session_id', $session->id)->where('ip', $this->ip)->get();
      }

      $pos_cashier = pos_cashier::get();
      
      foreach($completed_transaction as $completed)
      {
        $completed->void_by_name = "";
        $completed->cashier_name = "";
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

        if($completed->ip)
        {
          foreach($pos_cashier as $pos_cashier_detail)
          {
            if($pos_cashier_detail->ip == $completed->ip)
            {
              $completed->cashier_name = $pos_cashier_detail->cashier_name;
              break;
            }
          }
        }
      }

      // get latest cashier, incase session out, some opening do not have closing
      $cashier = null;
      $now = date('Y-m-d H:i:s', strtotime(now()));
      $opening = 0;
      $new_session = 1;

      $ip = $this->ip;
      // default is cashier
      $device_type = 2;
      $pos_cashier = pos_cashier::where('ip', $ip)->first();
      $cashier_name = null;
      if($pos_cashier)
      {
        $device_type = $pos_cashier->type;
        $cashier_name = $pos_cashier->cashier_name;
      }

      if($session)
      {
        $new_session = 0;
        $cashier = cashier::where('session_id', $session->id)->where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

        $prev_cashier = cashier::where('session_id', $session->id)->where('ip', $this->ip)->where('opening', 1)->where('closing', 1)->orderBy('id', 'desc')->first();

        if($cashier)
        {
          $opening = 1;
        }
        elseif(!$cashier && $prev_cashier)
        {
          cashier::create([
            'ip' => $this->ip,
            'cashier_name' => $cashier_name,
            'session_id' => $session->id,
            'opening' => 1,
            'opening_amount' => $prev_cashier->closing_amount,
            'opening_by' => $user->id,
            'opening_by_name' => $user->name,
            'opening_date_time' => $now
          ]);

          $opening = 1;
        }
      }

      if($user->user_type == 1)
      {
        $user_management_list = $user_list;
      }
      else
      {
        $user_management_list = [$user];
      }

      $shortcut_key = shortcut_key::get();  

      $branchSyncURL = $this->branchSyncURL;
      $branch_id = $this->branch_id;
      $branchProductSyncURL = $this->branchProductSyncURL;

      return view('front.index', compact('user', 'user_management_list', 'pending_transaction', 'subtotal', 'discount', 'have_discount', 'total', 'real_total', 'round_off', 'payment', 'balance', 'transaction_id', 'completed_transaction', 'opening', 'voucher_name', 'session', 'shortcut_key', 'ip', 'device_type', 'branch_name', 'branch_address', 'item_quantity', 'contact_number', 'pos_cashier', 'new_session', 'branch_id', 'branchSyncURL', 'branchProductSyncURL'));
    }

    public function getSetupPage()
    {
      $user = Auth::user();
      $access = false;

      if($user)
      {
        if($user->user_type == 1)
        {
          $access = true;
        }
      }

      if($access)
      {
        $pos_cashier = pos_cashier::get();
        $ip = $this->ip;

        return view('front.setup', compact('user', 'pos_cashier', 'ip'));
      }
      else
      {
        return redirect(route('home'));  
      }
    }

    public function getKeySetupPage()
    {
      $user = Auth::user();
      $access = false;

      if($user)
      {
        if($user->user_type == 1)
        {
          $access = true;
        }
      }

      if($access)
      {
        $front_function_list = $this->front_function_list();

        $shortcut_key = shortcut_key::get();

        foreach($shortcut_key as $key)
        {
          foreach($front_function_list as $fkey => $front)
          {
            if($key->function == $front['function'])
            {
              $front_function_list[$fkey]['code'] = $key->code;
              $front_function_list[$fkey]['character'] = $key->character;
              break;
            }
          }
        }

        return view('front.key_setup', compact('shortcut_key', 'front_function_list'));
      }
      else
      {
        return redirect(route('home'));  
      }
    }

    public function saveShortcutKey(Request $request)
    {
      foreach($request->function as $key => $function)
      {
        $code_name = $function."_code";
        $code = $request->$code_name;

        $char_name = $function."_char";
        $char = $request->$char_name;

        shortcut_key::updateOrCreate([
          'function' => $function
        ],[
          'function' => $function,
          'function_name' => $request->function_name[$key],
          'code' => $code,
          'character' => $char
        ]);
      }

      return redirect(route('key_setup'));
    }

    public function searchAndAddItem(Request $request)
    {
      $now = date('Y-m-d H:i:s', strtotime(now()));
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

        if($product->promotion_start && $product->promotion_end && $product->promotion_price)
        {
          if($product->promotion_start <= $now && $product->promotion_end >= $now)
          {
            $product->price = $product->promotion_price;
          }
        }

        $wholesale_price = null;
        $wholesale_quantity = null;
        $wholesale_price2 = null;
        $wholesale_quantity2 = null;
        $wholesale_price3 = null;
        $wholesale_quantity3 = null;
        $wholesale_price4 = null;
        $wholesale_quantity4 = null;
        $wholesale_price5 = null;
        $wholesale_quantity5 = null;
        $wholesale_price6 = null;
        $wholesale_quantity6 = null;
        $wholesale_price7 = null;
        $wholesale_quantity7 = null;

        $normal_wholesale_price = $product->normal_wholesale_price;
        $normal_wholesale_quantity = $product->normal_wholesale_quantity;
        $normal_wholesale_price2 = $product->normal_wholesale_price2;
        $normal_wholesale_quantity2 = $product->normal_wholesale_quantity2;
        $normal_wholesale_price3 = $product->normal_wholesale_price3;
        $normal_wholesale_quantity3 = $product->normal_wholesale_quantity3;
        $normal_wholesale_price4 = $product->normal_wholesale_price4;
        $normal_wholesale_quantity4 = $product->normal_wholesale_quantity4;
        $normal_wholesale_price5 = $product->normal_wholesale_price5;
        $normal_wholesale_quantity5 = $product->normal_wholesale_quantity5;
        $normal_wholesale_price6 = $product->normal_wholesale_price6;
        $normal_wholesale_quantity6 = $product->normal_wholesale_quantity6;
        $normal_wholesale_price7 = $product->normal_wholesale_price7;
        $normal_wholesale_quantity7 = $product->normal_wholesale_quantity7;


        $quantity = 1;
        $subtotal = round($product->price, 2);
        $total = round($product->price, 2);

        if($product->wholesale_start_date && $product->wholesale_end_date)
        {
          if($product->wholesale_start_date <= $now && $product->wholesale_end_date >= $now)
          {
            $wholesale_price = $product->wholesale_price;
            $wholesale_quantity = $product->wholesale_quantity;
            $wholesale_price2 = $product->wholesale_price2;
            $wholesale_quantity2 = $product->wholesale_quantity2;
            $wholesale_price3 = $product->wholesale_price3;
            $wholesale_quantity3 = $product->wholesale_quantity3;
            $wholesale_price4 = $product->wholesale_price4;
            $wholesale_quantity4 = $product->wholesale_quantity4;
            $wholesale_price5 = $product->wholesale_price5;
            $wholesale_quantity5 = $product->wholesale_quantity5;
            $wholesale_price6 = $product->wholesale_price6;
            $wholesale_quantity6 = $product->wholesale_quantity6;
            $wholesale_price7 = $product->wholesale_price7;
            $wholesale_quantity7 = $product->wholesale_quantity7;
          }
        }

        $product->using_price = $product->price;

        $user = Auth::user();
        $transaction = transaction::where('completed', null)->where('ip', $this->ip)->first();

        $item_count = 0;
        if(!$transaction)
        {
          $cashier_name = null;
          $cashier_detail = pos_cashier::where('ip', $this->ip)->first();

          if($cashier_detail)
          {
            $cashier_name = $cashier_detail->cashier_name;
          }

          $session_id = null;
          $session = session::where('closed', null)->orderBy('id', 'desc')->first();
          if($session)
          {
            $session_id = $session->id;
          }

          $transaction = transaction::create([
            'session_id' => $session_id,
            'ip' => $this->ip,
            'cashier_name' => $cashier_name,
            'user_id' => $user->id,
            'user_name' => $user->name
          ]);

          $transaction_wholesale_price = null;
          if($normal_wholesale_quantity == 1)
            $transaction_wholesale_price = $normal_wholesale_price;

          if($normal_wholesale_quantity2 == 1)
            $transaction_wholesale_price = $normal_wholesale_price2;

          if($normal_wholesale_quantity3 == 1)
            $transaction_wholesale_price = $normal_wholesale_price3;

          if($normal_wholesale_quantity4 == 1)
            $transaction_wholesale_price = $normal_wholesale_price4;

          if($normal_wholesale_quantity5 == 1)
            $transaction_wholesale_price = $normal_wholesale_price5;

          if($normal_wholesale_quantity6 == 1)
            $transaction_wholesale_price = $normal_wholesale_price6;

          if($normal_wholesale_quantity7 == 1)
            $transaction_wholesale_price = $normal_wholesale_price7;

          if($wholesale_quantity == 1)
            $transaction_wholesale_price = $wholesale_price;

          if($wholesale_quantity2 == 1)
            $transaction_wholesale_price = $wholesale_price2;

          if($wholesale_quantity3 == 1)
            $transaction_wholesale_price = $wholesale_price3;

          if($wholesale_quantity4 == 1)
            $transaction_wholesale_price = $wholesale_price4;

          if($wholesale_quantity5 == 1)
            $transaction_wholesale_price = $wholesale_price5;

          if($wholesale_quantity6 == 1)
            $transaction_wholesale_price = $wholesale_price6;

          if($wholesale_quantity7 == 1)
            $transaction_wholesale_price = $wholesale_price7;

          if($transaction_wholesale_price)
          {
            $product->using_price = $transaction_wholesale_price;
          }
          
          $transaction_detail = transaction_detail::create([
            'transaction_id' => $transaction->id,
            'department_id' => $product->department_id,
            'category_id' => $product->category_id,
            'product_id' => $product->id,
            'barcode' => $product->barcode,
            'product_name' => $product->product_name,
            'price' => round($product->price, 2),
            'quantity' => 1,
            'measurement_type' => $product->measurement,
            'measurement' => 1,
            'wholesale_price' => $transaction_wholesale_price,
            'discount' => 0,
            'subtotal' => $subtotal,
            'total' => $total
          ]);

          $item_count = 1;
        }
        else
        {
          $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->where('product_id', $product->id)->where('void', null)->first();

          if($transaction_detail && $product->measurement != "kilogram" && $product->measurement != "meter")
          {
            $quantity = $transaction_detail->quantity + 1;
            $price = $product->price;
            $transaction_wholesale_price = null;

            $subtotal = round( ($transaction_detail->quantity + 1) * $product->price, 2);
            $total = round( ($transaction_detail->quantity + 1) * $product->price, 2);

            if($normal_wholesale_quantity)
            {
              if($quantity >= $normal_wholesale_quantity)
              {
                $transaction_wholesale_price = $normal_wholesale_price;
              }
            }

            if($normal_wholesale_quantity2)
            {
              if($quantity >= $normal_wholesale_quantity2)
              {
                $transaction_wholesale_price = $normal_wholesale_price2;
              }
            }

            if($normal_wholesale_quantity3)
            {
              if($quantity >= $normal_wholesale_quantity3)
              {
                $transaction_wholesale_price = $normal_wholesale_price3;
              }
            }

            if($normal_wholesale_quantity4)
            {
              if($quantity >= $normal_wholesale_quantity4)
              {
                $transaction_wholesale_price = $normal_wholesale_price4;
              }
            }

            if($normal_wholesale_quantity5)
            {
              if($quantity >= $normal_wholesale_quantity5)
              {
                $transaction_wholesale_price = $normal_wholesale_price5;
              }
            }

            if($normal_wholesale_quantity6)
            {
              if($quantity >= $normal_wholesale_quantity6)
              {
                $transaction_wholesale_price = $normal_wholesale_price6;
              }
            }

            if($normal_wholesale_quantity7)
            {
              if($quantity >= $normal_wholesale_quantity7)
              {
                $transaction_wholesale_price = $normal_wholesale_price7;
              }
            }

            if($wholesale_quantity)
            {
              if($quantity >= $wholesale_quantity)
              {
                $transaction_wholesale_price = $wholesale_price;
              }
            }
            
            if($wholesale_quantity2)
            {
              if($quantity >= $wholesale_quantity2)
              {
                $transaction_wholesale_price = $wholesale_price2;
              }
            }

            if($wholesale_quantity3)
            {
              if($quantity >= $wholesale_quantity3)
              {
                $transaction_wholesale_price = $wholesale_price3;
              }
            }

            if($wholesale_quantity4)
            {
              if($quantity >= $wholesale_quantity4)
              {
                $transaction_wholesale_price = $wholesale_price4;
              }
            }

            if($wholesale_quantity5)
            {
              if($quantity >= $wholesale_quantity5)
              {
                $transaction_wholesale_price = $wholesale_price5;
              }
            }

            if($wholesale_quantity6)
            {
              if($quantity >= $wholesale_quantity6)
              {
                $transaction_wholesale_price = $wholesale_price6;
              }
            }

            if($wholesale_quantity7)
            {
              if($quantity >= $wholesale_quantity7)
              {
                $transaction_wholesale_price = $wholesale_price7;
              }
            }

            if($transaction_wholesale_price)
            {
              $subtotal = round( ($transaction_detail->quantity + 1) * $transaction_wholesale_price, 2);
              $total = round( ($transaction_detail->quantity + 1) * $transaction_wholesale_price, 2);

              $product->using_price = $transaction_wholesale_price;
            }

            transaction_detail::where('id', $transaction_detail->id)->update([
              'price' => round($product->price, 2),
              'quantity' => $quantity,
              'measurement_type' => $product->measurement,
              'wholesale_price' => $transaction_wholesale_price,
              'subtotal' => $subtotal,
              'total' => $total
            ]);
          }
          else
          {
            $transaction_wholesale_price = null;
            if($normal_wholesale_quantity == 1)
              $transaction_wholesale_price = $normal_wholesale_price;

            if($normal_wholesale_quantity2 == 1)
              $transaction_wholesale_price = $normal_wholesale_price2;

            if($normal_wholesale_quantity3 == 1)
              $transaction_wholesale_price = $normal_wholesale_price3;

            if($normal_wholesale_quantity4 == 1)
              $transaction_wholesale_price = $normal_wholesale_price4;

            if($normal_wholesale_quantity5 == 1)
              $transaction_wholesale_price = $normal_wholesale_price5;

            if($normal_wholesale_quantity6 == 1)
              $transaction_wholesale_price = $normal_wholesale_price6;

            if($normal_wholesale_quantity7 == 1)
              $transaction_wholesale_price = $normal_wholesale_price7;

            if($wholesale_quantity == 1)
              $transaction_wholesale_price = $wholesale_price;

            if($wholesale_quantity2 == 1)
              $transaction_wholesale_price = $wholesale_price2;

            if($wholesale_quantity3 == 1)
              $transaction_wholesale_price = $wholesale_price3;

            if($wholesale_quantity4 == 1)
              $transaction_wholesale_price = $wholesale_price4;

            if($wholesale_quantity5 == 1)
              $transaction_wholesale_price = $wholesale_price5;

            if($wholesale_quantity6 == 1)
              $transaction_wholesale_price = $wholesale_price6;

            if($wholesale_quantity7 == 1)
              $transaction_wholesale_price = $wholesale_price7;

            if($transaction_wholesale_price)
            {
              $product->using_price = $transaction_wholesale_price;
            }
          
            $transaction_detail = transaction_detail::create([
              'transaction_id' => $transaction->id,
              'department_id' => $product->department_id,
              'category_id' => $product->category_id,
              'product_id' => $product->id,
              'barcode' => $product->barcode,
              'product_name' => $product->product_name,
              'price' => round($product->price, 2),
              'quantity' => $quantity,
              'measurement_type' => $product->measurement,
              'measurement' => 1,
              'wholesale_price' => $transaction_wholesale_price,
              'discount' => 0,
              'subtotal' => $subtotal,
              'total' => $total
            ]);
          }

          $item_count = transaction_detail::where('transaction_id', $transaction->id)->count();
        }

        $transaction_summary = $this->transaction_summary($transaction);

        $product->using_price_text = number_format($product->using_price, 2);

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->transaction_summary = $transaction_summary;
        $response->product = $product;
        $response->item_count = $item_count;
        $response->transaction_detail = $transaction_detail;

        return response()->json($response);
      }
    }

    public function searchAndAddItemRefund(Request $request)
    {
      $product_detail = product::where('barcode', $request->barcode)->first();

      if($product_detail)
      {
        $product_detail->price_text = number_format($product_detail->price, 2);
        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->product_detail = $product_detail;

        return response()->json($response);
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Barcode not found";

        return response()->json($response);
      }
    }

    public function searchRelatedItem(Request $request)
    {
      $barcode = $request->barcode;
      $related_item = product::where(function($query) use ($barcode){
        if(is_numeric($barcode))
        {
          $query->where('barcode', 'LIKE', $barcode."%");
        }
        else
        {
          $query->where('barcode', 'LIKE', $barcode."%")->orWhere('product_name', 'LIKE', '%'.$barcode.'%');
        }
      })->limit(30)->get();

      foreach($related_item as $related)
      {
        $related->price_text = "";
        if(is_numeric($related->price) && $related->price)
        {
          $related->price_text = number_format($related->price, 2);
        }
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->related_item = $related_item;

      return response()->json($response);
    }

    public function transaction_summary($transaction)
    {
      $subtotal = 0;
      $total = 0;  
      $round_off = 0;

      $discount = $transaction->total_discount;
      $round_off = $transaction->round_off;
      $total_quantity = 0;

      $items_list = transaction_detail::where('transaction_id', $transaction->id)->where('void', null)->orderBy('updated_at', 'desc')->get();
      if(count($items_list) > 0)
      {
        foreach($items_list as $item)
        {
          $total_quantity += $item->quantity;
          $transaction_price = 0;

          if($item->quantity > 0)
          {
            if($item->wholesale_price)
            {
              $transaction_price = ($item->quantity * $item->measurement) * $item->wholesale_price;
            }
            else
            {
              $transaction_price = ($item->quantity * $item->measurement) * $item->price;
            }
          }

          $item->subtotal_text = number_format($item->subtotal, 2);
          $item->measurement_text = round(floatval($item->measurement), 4);

          $subtotal = $subtotal + $item->subtotal;
          $total = $total + $item->total;
          $item->total_price_text = number_format($transaction_price, 2);
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
      $transaction_summary->total_quantity = $total_quantity;

      return $transaction_summary;
    }

    public function submitDeleteItem(Request $request)
    {
      transaction_detail::where('id', $request->item_id)->delete();

      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();

      $item_quantity = count($transaction_detail);
      $transaction_summary = null;
      if(count($transaction_detail) == 0)
      {
        transaction::where('id', $request->transaction_id)->delete();
      }
      else
      {
        $transaction = transaction::where('id', $request->transaction_id)->first();
        $transaction_summary = $this->transaction_summary($transaction);
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->transaction_summary = $transaction_summary;
      $response->item_quantity = $item_quantity;

      return response()->json($response);
    }

    public function submitTransaction(Request $request)
    {
      $user = Auth::user();

      $transaction = transaction::where('id', $request->transaction_id)->first();

      if(!$transaction)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Transaction not found";
        return response()->json($response);
      }

      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();

      $payment_type = $request->payment_type;

      $received_cash = 0;
      $reference_no = null;
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
      $total_summary = $this->roundDecimal($total);
      $total = $total_summary->final_total;
      $round_off = $total_summary->round_off;
      
      $payment_type_text = "";
      if($payment_type == "cash")
      {
        $received_cash = $request->received_cash;
        if(round($received_cash, 2) >= round($total, 2))
        {
          $valid_payment = true;
        }
        
        $payment_type_text = "Cash";
        $balance = $received_cash - $total;
      } 
      else
      {
        $reference_no = $request->reference_no;
        if($reference_no)
        {
          $valid_payment = true;
        }

        if($payment_type == "card")
        {
          $payment_type_text = "Kredit Kad";
        }
        elseif($payment_type == "tng")
        {
          $payment_type_text = "Touch & Go";
        }
        elseif($payment_type == "maybank_qr")
        {
          $payment_type_text = "Maybank QRCode";
        }
        elseif($payment_type == "grab_pay")
        {
          $payment_type_text = "Grab Pay";
        }
        elseif($payment_type == "boost")
        {
          $payment_type_text = "Boost";
        }
      }

      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      $opening_id = null;
      if($session)
      {
        $opening = cashier::where('session_id', $session->id)->where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

        if($opening)
        {
          $opening_id = $opening->id;
        }
      }  
      
      if($valid_payment)
      {
        //Generate new invoice number

        $seq = Invoice_sequence::first();
        // $now = now();
        // if(date("Y-m-d",strtotime($seq->updated_at)) == date("Y-m-d", strtotime($now))){
        //   $transaction_no = $seq->branch_code.date("Ymd").$seq->next_seq;
        // }else{
        //   $transaction_no = $seq->branch_code.date("Ymd", strtotime($now))."00001";
        // }

        // if(date("Y-m-d",strtotime($seq->updated_at)) == date('Y-m-d', strtotime($now))){
        //   $next = $seq->next_seq;
        //   $next = intval($next) + 1;
        //   $i=5;
        //   while($i>strlen($next)){
        //     $next = "0".$next;
        //   }
          
        //   Invoice_sequence::where('id',$seq->id)->update([
        //     'current_seq' => $seq->next_seq,
        //     'next_seq' => $next,
        //   ]);
        // }else{
        //   Invoice_sequence::where('id',$seq->id)->update([
        //     'current_seq' => '00001',
        //     'next_seq' => '00002',
        //   ]);
        // }

        $count = transaction::whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])->count();
        for($a = strlen($count); $a < 5; $a++)
        {
          $count = "0".$count;
        }

        $transaction_no = $seq->branch_code.date('Ymd').$count;

        transaction::where('id', $request->transaction_id)->update([
          'opening_id' => $opening_id,
          'transaction_no' => $transaction_no,
          'reference_no' => $reference_no,
          'subtotal' => round($subtotal, 2),
          'total_discount' => round($total_discount, 2),
          'payment' => round($received_cash, 2),
          'payment_type' => $payment_type,
          'payment_type_text' => $payment_type_text,
          'balance' => round($balance, 2),
          'total' => round($total, 2),
          'round_off' => round($round_off, 2),
          'completed' => 1,
          'completed_by' => $user->id,
          'transaction_date' => date('Y-m-d H:i:s', strtotime(now()))
        ]);

        $completed_transaction = transaction::where('id', $request->transaction_id)->first();

        $completed_transaction->cashier_name = "";
        if($completed_transaction->ip)
        {
          $pos_cashier = pos_cashier::where('ip', $completed_transaction->ip)->first();
          if($pos_cashier)
          {
            $completed_transaction->cashier_name = $pos_cashier->cashier_name;
          }
        }

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

      transaction::where('id', $request->transaction_id)->delete();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function editReferenceNo(Request $request)
    {
      transaction::where('id', $request->transaction_id)->update([
        'reference_no' => $request->reference_no
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function editQuantity(Request $request)
    {
      $now = date('Y-m-d H:i:s', strtotime(now()));
      $transaction_detail = transaction_detail::where('id', $request->item_id)->first();

      $product = product::where('id', $transaction_detail->product_id)->first();
      if($product->promotion_start && $product->promotion_end && $product->promotion_price)
      {
        if($product->promotion_start <= $now && $product->promotion_end >= $now)
        {
          $product->price = $product->promotion_price;
        }
      }

      $transaction_detail->price = $product->price;

      if($request->type == "number")
      {
        $total_quantity = $request->quantity;
      }
      else
      {
        $total_quantity = $transaction_detail->quantity;
        if($request->type == "plus")
        {
          $total_quantity--;
        }
        elseif($request->type == "minus")
        {
          $total_quantity++;
        } 
      }

      $final_quantity = $total_quantity * $transaction_detail->measurement;

      $subtotal = $final_quantity * $product->price;
      $total = $final_quantity * $product->price;

      $wholesale_price = null;
      $wholesale_quantity = null;
      $wholesale_price2 = null;
      $wholesale_quantity2 = null;
      $wholesale_price3 = null;
      $wholesale_quantity3 = null;
      $wholesale_price4 = null;
      $wholesale_quantity4 = null;
      $wholesale_price5 = null;
      $wholesale_quantity5 = null;
      $wholesale_price6 = null;
      $wholesale_quantity6 = null;
      $wholesale_price7 = null;
      $wholesale_quantity7 = null;

      $normal_wholesale_price = $product->normal_wholesale_price;
      $normal_wholesale_quantity = $product->normal_wholesale_quantity;
      $normal_wholesale_price2 = $product->normal_wholesale_price2;
      $normal_wholesale_quantity2 = $product->normal_wholesale_quantity2;
      $normal_wholesale_price3 = $product->normal_wholesale_price3;
      $normal_wholesale_quantity3 = $product->normal_wholesale_quantity3;
      $normal_wholesale_price4 = $product->normal_wholesale_price4;
      $normal_wholesale_quantity4 = $product->normal_wholesale_quantity4;
      $normal_wholesale_price5 = $product->normal_wholesale_price5;
      $normal_wholesale_quantity5 = $product->normal_wholesale_quantity5;
      $normal_wholesale_price6 = $product->normal_wholesale_price6;
      $normal_wholesale_quantity6 = $product->normal_wholesale_quantity6;
      $normal_wholesale_price7 = $product->normal_wholesale_price7;
      $normal_wholesale_quantity7 = $product->normal_wholesale_quantity7;

      $transaction_wholesale_price = null;
      $is_wholesale = 0;

      if($product->wholesale_start_date && $product->wholesale_end_date)
      {
        if($product->wholesale_start_date <= $now && $product->wholesale_end_date >= $now)
        {
          $wholesale_price = $product->wholesale_price;
          $wholesale_quantity = $product->wholesale_quantity;
          $wholesale_price2 = $product->wholesale_price2;
          $wholesale_quantity2 = $product->wholesale_quantity2;
          $wholesale_price3 = $product->wholesale_price3;
          $wholesale_quantity3 = $product->wholesale_quantity3;
          $wholesale_price4 = $product->wholesale_price4;
          $wholesale_quantity4 = $product->wholesale_quantity4;
          $wholesale_price5 = $product->wholesale_price5;
          $wholesale_quantity5 = $product->wholesale_quantity5;
          $wholesale_price6 = $product->wholesale_price6;
          $wholesale_quantity6 = $product->wholesale_quantity6;
          $wholesale_price7 = $product->wholesale_price7;
          $wholesale_quantity7 = $product->wholesale_quantity7;
        }
      }

      if($normal_wholesale_quantity && $normal_wholesale_price)
      {
        if($final_quantity >= $normal_wholesale_quantity)
        {
          $subtotal = $final_quantity * $normal_wholesale_price;
          $total = $final_quantity * $normal_wholesale_price;

          $transaction_wholesale_price = $normal_wholesale_price;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity2 && $normal_wholesale_price2)
      {
        if($final_quantity >= $normal_wholesale_quantity2)
        {
          $subtotal = $final_quantity * $normal_wholesale_price2;
          $total = $final_quantity * $normal_wholesale_price2;

          $transaction_wholesale_price = $normal_wholesale_price2;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity3 && $normal_wholesale_price3)
      {
        if($final_quantity >= $normal_wholesale_quantity3)
        {
          $subtotal = $final_quantity * $normal_wholesale_price3;
          $total = $final_quantity * $normal_wholesale_price3;

          $transaction_wholesale_price = $normal_wholesale_price3;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity4 && $normal_wholesale_price4)
      {
        if($final_quantity >= $normal_wholesale_quantity4)
        {
          $subtotal = $final_quantity * $normal_wholesale_price4;
          $total = $final_quantity * $normal_wholesale_price4;

          $transaction_wholesale_price = $normal_wholesale_price4;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity5 && $normal_wholesale_price5)
      {
        if($final_quantity >= $normal_wholesale_quantity5)
        {
          $subtotal = $final_quantity * $normal_wholesale_price5;
          $total = $final_quantity * $normal_wholesale_price5;

          $transaction_wholesale_price = $normal_wholesale_price5;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity6 && $normal_wholesale_price6)
      {
        if($final_quantity >= $normal_wholesale_quantity6)
        {
          $subtotal = $final_quantity * $normal_wholesale_price6;
          $total = $final_quantity * $normal_wholesale_price6;

          $transaction_wholesale_price = $normal_wholesale_price6;
          $is_wholesale = 1;
        }
      }

      if($normal_wholesale_quantity7 && $normal_wholesale_price7)
      {
        if($final_quantity >= $normal_wholesale_quantity7)
        {
          $subtotal = $final_quantity * $normal_wholesale_price7;
          $total = $final_quantity * $normal_wholesale_price7;

          $transaction_wholesale_price = $normal_wholesale_price7;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity && $wholesale_price)
      {
        if($final_quantity >= $wholesale_quantity)
        {
          $subtotal = $final_quantity * $wholesale_price;
          $total = $final_quantity * $wholesale_price;

          $transaction_wholesale_price = $wholesale_price;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity2 && $wholesale_price2)
      {
        if($final_quantity >= $wholesale_quantity2)
        {
          $subtotal = $final_quantity * $wholesale_price2;
          $total = $final_quantity * $wholesale_price2;

          $transaction_wholesale_price = $wholesale_price2;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity3 && $wholesale_price3)
      {
        if($final_quantity >= $wholesale_quantity3)
        {
          $subtotal = $final_quantity * $wholesale_price3;
          $total = $final_quantity * $wholesale_price3;

          $transaction_wholesale_price = $wholesale_price3;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity4 && $wholesale_price4)
      {
        if($final_quantity >= $wholesale_quantity4)
        {
          $subtotal = $final_quantity * $wholesale_price4;
          $total = $final_quantity * $wholesale_price4;

          $transaction_wholesale_price = $wholesale_price4;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity5 && $wholesale_price5)
      {
        if($final_quantity >= $wholesale_quantity5)
        {
          $subtotal = $final_quantity * $wholesale_price5;
          $total = $final_quantity * $wholesale_price5;

          $transaction_wholesale_price = $wholesale_price5;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity6 && $wholesale_price6)
      {
        if($final_quantity >= $wholesale_quantity6)
        {
          $subtotal = $final_quantity * $wholesale_price6;
          $total = $final_quantity * $wholesale_price6;

          $transaction_wholesale_price = $wholesale_price6;
          $is_wholesale = 1;
        }
      }

      if($wholesale_quantity7 && $wholesale_price7)
      {
        if($final_quantity >= $wholesale_quantity7)
        {
          $subtotal = $final_quantity * $wholesale_price7;
          $total = $final_quantity * $wholesale_price7;

          $transaction_wholesale_price = $wholesale_price7;
          $is_wholesale = 1;
        }
      }

      if($total_quantity == 0)
      {
        transaction_detail::where('id', $request->item_id)->delete();
      }
      else
      {
        transaction_detail::where('id', $request->item_id)->update([
          'price' => $product->price,
          'quantity' => $total_quantity,
          'wholesale_price' => $transaction_wholesale_price,
          'subtotal' => round($subtotal, 2),
          'total' => round($total, 2)
        ]);
      }

      $transaction_detail_list = transaction_detail::where('transaction_id', $transaction_detail->transaction_id)->get();
      $item_quantity = count($transaction_detail_list);
      $transaction_summary = null;
      if(count($transaction_detail_list) == 0)
      {
        transaction::where('id', $transaction_detail->transaction_id)->delete();
      }
      else
      {
        $transaction = transaction::where('id', $transaction_detail->transaction_id)->first();
        $transaction_summary = $this->transaction_summary($transaction);
      } 

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->quantity = $total_quantity;
      $response->is_wholesale = $is_wholesale;
      $response->subtotal = number_format($subtotal, 2);
      $response->total = number_format($total, 2);
      $response->item_quantity = $item_quantity;
      $response->transaction_summary = $transaction_summary;
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
          'voucher_id' => $active_voucher->id,
          'voucher_code' => $active_voucher->code
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
        'voucher_id' => null,
        'voucher_code' => null,
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
      $now = date('Y-m-d H:i:s', strtotime(now()));

      $session = session::where('closed', null)->orderBy('id', 'desc')->first();
      if(!$session)
      {
        $session = session::create([
          'ip' => $this->ip,
          'opening_date_time' => $now,
          'synced' => null,
          'closed' => null
        ]);
      }

      $cashier = cashier::where('ip', $this->ip)->where('session_id', $session->id)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      $cashier_name = null;
      $pos_cashier = pos_cashier::where('ip', $this->ip)->first();
      if($pos_cashier)
      {
        $cashier_name = $pos_cashier->cashier_name;
      }

      if(!$cashier)
      {
        cashier::create([
          'ip' => $this->ip,
          'cashier_name' => $cashier_name,
          'session_id' => $session->id,
          'opening' => 1,
          'opening_amount' => round($request->opening_amount, 2),
          'opening_by' => $user->id,
          'opening_by_name' => $user->name,
          'opening_date_time' => $now
        ]);
      }
      else
      {
        cashier::where('id', $cashier->id)->update([
          'opening' => 1,
          'opening_amount' => round($request->opening_amount, 2),
          'opening_by' => $user->id,
          'opening_by_name' => $user->name,
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
      $cashier = cashier::where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

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
        $session = session::where('closed', null)->orderBy('id', 'desc')->first();

        if(!$session)
        {
          $session = session::create([
            'ip' => $this->ip,
            'opening_date_time' => $now,
            'synced' => null,
            'closed' => null
          ]);
        }

        $user_name = "";
        $cashier_name = "";
        $opening_by = User::where('id', $cashier->opening_by)->first();
        if($opening_by)
        {
          $user_name = $opening_by->name;
        }

        $cash_float_list = cash_float::where('session_id', $session->id)->where('ip', $cashier->ip)->where('opening_id', $cashier->id)->get();

        $total_cash_float = 0;
        foreach($cash_float_list as $cash_float)
        {
          $cash_float->created_time_text = date('h:i:s A', strtotime($cash_float->created_at));
          $cash_float->amount_text = number_format($cash_float->amount, 2);

          if($cash_float->type == "in")
          {
            $total_cash_float += $cash_float->amount;
          }
          elseif($cash_float->type == "out")
          {
            $total_cash_float -= $cash_float->amount;
          }
          elseif($cash_float->type == "boss")
          {
            $total_cash_float -= $cash_float->amount;
          }

          if(!$cash_float->remarks)
          {
            $cash_float->remarks = "";
          }
        }

        $pos_cashier = pos_cashier::where('ip', $cashier->ip)->first();
        if($pos_cashier)
        {
          $cashier_name = $pos_cashier->cashier_name;
        }

        $total_cash_sales = transaction::where('session_id', $session->id)->where('ip', $cashier->ip)->where('opening_id', $cashier->id)->where('payment_type', 'cash')->select('*')->selectRaw('SUM(transaction.total) as total_cash_sales')->groupBy('session_id')->get();

        $total_cash = 0;
        foreach($total_cash_sales as $cash_sales)
        {
          $total_cash += $cash_sales->total_cash_sales;
        }

        $total_refund = 0;
        $refund = refund::where('session_id', $session->id)->where('ip', $cashier->ip)->where('opening_id', $cashier->id)->get();
        foreach($refund as $value)
        {
          $value->total_text = number_format($value->total, 2);
          $value->created_time_text = date('h:i:s A', strtotime($value->created_at));
          $total_refund += $value->total;
        }

        $cash_flow = $cashier->opening_amount + $total_cash + $total_cash_float - $total_refund;

        $closing_report = new \stdClass();
        $closing_report->opening_by = $user_name;
        $closing_report->cashier_name = $cashier_name;
        $closing_report->now = date('j - M - Y h:i:s A');
        $closing_report->opening = number_format($cashier->opening_amount, 2);
        $closing_report->opening_time = date('h:i:s A', strtotime($cashier->opening_date_time));
        $closing_report->closing = number_format($request->closing_amount, 2);
        $closing_report->closing_time = date('h:i:s A', strtotime($now));
        $closing_report->diff = number_format( ($request->closing_amount - $request->calculated_amount), 2);
        $closing_report->cash_float = $cash_float_list;
        $closing_report->refund_list = $refund;
        $closing_report->cash_sales = number_format($total_cash, 2);
        $closing_report->cash_flow = number_format($cash_flow, 2);

        cashier::where('id', $cashier->id)->update([
          'closing' => 1,
          'closing_amount' => round($request->closing_amount, 2),
          'calculated_amount' => $request->calculated_amount,
          'diff' => round($request->closing_amount, 2) - $request->calculated_amount,
          'closing_by' => $user->id,
          'closing_by_name' => $user->name,
          'closing_date_time' => $now
        ]);
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->closing_report = $closing_report;

      return response()->json($response);
    }

    public function submitDailyClosing(Request $request)
    {
      $session_id = null;
      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      if($session)
      {
        $session_id = $session->id;
      }
      else
      {
        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Session not found";

        return response()->json($response);
      }

      $pending_cashier = cashier::where('session_id', $session->id)->where('ip', '<>', $this->ip)->whereNull('closing')->get();
      if(count($pending_cashier) > 0)
      {
        $cashier_list = array();
        $cashier_name = "";

        foreach($pending_cashier as $cashier)
        {
          if($cashier->ip)
          {
            $pos_cashier = pos_cashier::where('ip', $cashier->ip)->first();
            if($pos_cashier)
            {
              if(!in_array($pos_cashier->id, $cashier_list))
              {
                array_push($cashier_list, $pos_cashier->id);
                $cashier_name .= $pos_cashier->cashier_name.", ";
              }
            }
            else
            {
              $cashier_name .= $cashier->ip.", ";
            }
          }
        }

        $cashier_name = substr($cashier_name, 0, -2);

        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Cashier <b>".$cashier_name."</b> still opening, please close the cashier before proceed daily closing";

        return response()->json($response);
      }

      $pending_transaction = transaction::where('completed', null)->where('session_id', $session_id)->get();

      $on_pending = false;
      if(count($pending_transaction) > 0)
      {
        $cashier_list = array();
        $cashier_name = "";

        foreach($pending_transaction as $pending)
        {
          $transaction_detail = transaction_detail::where('transaction_id', $pending->id)->get();
          if(count($transaction_detail) > 0)
          {
            $on_pending = true;
            if($pending->ip)
            {
              $pos_cashier = pos_cashier::where('ip', $pending->ip)->first();
              if($pos_cashier)
              {
                if(!in_array($pos_cashier->id, $cashier_list))
                {
                  array_push($cashier_list, $pos_cashier->id);
                  $cashier_name .= $pos_cashier->cashier_name.", ";
                }
              }
              else
              {
                $cashier_name .= $pending->ip.", ";
              }
            }
          }
        }

        if($on_pending)
        {
          $cashier_name = substr($cashier_name, 0, -2);

          $response = new \stdClass();
          $response->error = 1;
          $response->message = "Cashier <b>".$cashier_name."</b> still have pending transaction, please clear the transaction before closing";

          return response()->json($response);
        }
      }

      $credentials = [
        'user_type' => 1,
        'username' => $request->username,
        'password' => $request->password
      ];

      if (Auth::validate($credentials))
      {
        $manager = User::where('username', $request->username)->first();
        // $cashier = cashier::where('session_id', $session_id)->where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

        // if($cashier)
        // {
        //   cashier::where('id', $cashier->id)->update([
        //     'closing' => 1,
        //     'closing_amount' => $request->closing_amount,
        //     'calculated_amount' => $request->calculated_amount,
        //     'diff' => $request->closing_amount - $request->calculated_amount,
        //     'closing_by' => $manager->id,
        //     'closing_date_time' => $now
        //   ]);
        // }

        // $response = $this->branchSync();

        session::where('id', $session->id)->update([
          'closing_date_time' => date('Y-m-d H:i:s'),
          'closed' => 1
        ]);

        if($manager)
        {
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
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Account invalid";

        return response()->json($response);
      }
    }

    public function submitCashFloat(Request $request)
    {
      $user = Auth::user();

      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      if(!$session)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Session not found";

        return response()->json($response);
      }

      $cashier = cashier::where('session_id', $session->id)->where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      if(!$cashier)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Cashier not found";

        return response()->json($response);
      }

      $cashier_name = "Not available";
      $pos_cashier = pos_cashier::where('ip', $this->ip)->first();
      if($pos_cashier)
      {
        $cashier_name = $pos_cashier->cashier_name;
      }

      cash_float::create([
        'user_id' => $user->id,
        'created_by' => $user->name,
        'ip' => $this->ip,
        'cashier_name' => $cashier_name,
        'session_id' => $session->id,
        'opening_id' => $cashier->id,
        'type' => $request->type,
        'amount' => $request->amount,
        'remarks' => $request->remarks
      ]);

      $message = "Cash Float ".ucfirst($request->type)." : RM ".$request->amount;
      if($request->type == "boss")
      {
        $message = "Bagi Ke Ketua : RM ".$request->amount;
      }

      $remarks = "";
      if($request->remarks)
      {
        $remarks = $request->remarks;
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = $message;
      $response->cashier_name = $cashier_name;
      $response->remarks = $remarks;
      $response->datetime = date('Y-M-d h:i:s A');
      $response->user_name = $user->name;

      return response()->json($response);
    }

    public function calculateClosingAmount()
    {
      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      if(!$session)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Session not found";
        $response->closing_amount = 0;

        return response()->json($response);
      }

      $closing_amount = 0;

      $cashier = cashier::where('ip', $this->ip)->where('session_id', $session->id)->where('opening', 1)->where('closing', null)->orderBy('created_at', 'desc')->first();
      $transaction_list = transaction::where('session_id', $session->id)->where('opening_id', $cashier->id)->where('ip', $this->ip)->where('payment_type', 'cash')->where('completed', 1)->get();
      
      $cash_float_list = cash_float::where('ip', $this->ip)->where('session_id', $session->id)->where('opening_id', $cashier->id)->get();

      foreach($transaction_list as $transaction)
      {
        $closing_amount += $transaction->total;
      }

      if($cashier)
      {
        $closing_amount += $cashier->opening_amount;
      }
      
      foreach($cash_float_list as $cash_float)
      {
        if($cash_float->type == "in")
        {
          $closing_amount += $cash_float->amount;
        }
        elseif($cash_float->type == "out")
        {
          $closing_amount -= $cash_float->amount;
        }
        elseif($cash_float->type == "boss")
        {
          $closing_amount -= $cash_float->amount;
        }
      }

      $refund = refund::where('session_id', $session->id)->where('ip', $this->ip)->where('opening_id', $cashier->id)->get();
      $total_refund = 0;

      foreach($refund as $value)
      {
        $total_refund += $value->total;
      }

      $closing_amount = $closing_amount - $total_refund;

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->closing_amount = round($closing_amount, 2);
      $response->closing_amount_text = number_format($closing_amount, 2);

      return response()->json($response);
    }

    public function getTransactionDetail(Request $request)
    {
      $transaction = transaction::where('transaction.id', $request->transaction_id)->leftJoin('users', 'users.id', '=', 'transaction.completed_by')->select('transaction.*', 'users.name as completed_by_name')->first();

      $transaction_detail_list = transaction_detail::where('transaction_detail.transaction_id', $request->transaction_id)->leftJoin('product', 'product.id', '=', 'transaction_detail.product_id')->select('transaction_detail.*', 'product.product_name', 'product.barcode')->get();

      $total_quantity = 0;
      $total_items = count($transaction_detail_list);

      foreach($transaction_detail_list as $transaction_detail)
      {
        $transaction_detail->price_text = number_format( ($transaction_detail->price * $transaction_detail->measurement), 2);
        $transaction_detail->wholesale_price_text = number_format( ($transaction_detail->wholesale_price * $transaction_detail->measurement), 2);
        $transaction_detail->total_text = number_format($transaction_detail->total, 2);

        $transaction_detail->measurement = round(floatval($transaction_detail->measurement), 3);

        $total_quantity += $transaction_detail->quantity;
      }

      $transaction->total_quantity = $total_quantity;
      $transaction->total_items = $total_items;
      $transaction->total_text = number_format($transaction->total, 2);
      $transaction->payment_text = "";
      $transaction->balance_text = "";
      if($transaction->payment_type == "cash")
      {
        $transaction->payment_text = number_format($transaction->payment, 2);
        $transaction->balance_text = number_format($transaction->balance, 2);
      }

      $transaction->receipt_date = date('l, d-m-Y', strtotime($transaction->created_at));
      $transaction->receipt_time = date('H:i', strtotime($transaction->created_at));

      $transaction->voucher_name = "";
      if($transaction->total_discount > 0 && $transaction->voucher_code)
      {
        $voucher = voucher::where('code', $transaction->voucher_code)->first();
        if($voucher)
        {
          $transaction->voucher_name = $voucher->name;
        }
      }

      $transaction->total_discount_text = number_format($transaction->total_discount, 2);
      $transaction->subtotal_text = number_format($transaction->subtotal, 2);

      $user_name = "";
      if($transaction->completed_by)
      {
        $user_detail = User::where('id', $transaction->completed_by)->first();
        if($user_detail)
        {
          $user_name = $user_detail->name;
        }
      }

      $transaction->user_name = $user_name;

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->transaction = $transaction;
      $response->transaction_detail = $transaction_detail_list;

      return response()->json($response);
    }

    public function branchSync()
    {
      // last session
      $resync = 0;
      $manual = null;
      $now = date('Y-m-d H:i:s', strtotime(now()));

      if(isset($_GET['resync']))
      {
        $resync = $_GET['resync'];
      }

      if(isset($_GET['manual']))
      {
        $manual = $_GET['manual'];
      }

      if($resync == 0)
      {
        $session_list = session::where('synced', null)->pluck('id')->toArray();
      }
      elseif($resync == 1)
      {
        $session_list = session::where('synced', null)->where('closed', 1)->pluck('id')->toArray();
      }

      if($manual == 2)
      {
        $session = session::where('closed', null)->first();
        if(!$session)
        {
          $session = session::create([
            'ip' => $this->ip,
            'opening_date_time' => $now,
            'synced' => null,
            'closed' => null
          ]);
        }
      }

      $branch_id = $_GET['branch_id'];
      $branchSyncURL = $_GET['branchSyncURL'];
      $branchProductSyncURL = $_GET['branchProductSyncURL'];

      if(!$branch_id)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch ID is empty, please add in ENV file or clear cache.";

        return response()->json($response);
      }

      $transaction = transaction::whereIn('session_id', $session_list)->get();
      $transaction_detail = transaction_detail::leftJoin('transaction', 'transaction.id', '=', 'transaction_detail.transaction_id')->whereIn('transaction.session_id', $session_list)->select('transaction_detail.*', 'transaction.session_id', 'transaction.created_at as transaction_date')->get();

      $cashier = cashier::where('synced', null)->where('closing', 1)->get();
      $cash_float = cash_float::where('synced', null)->get();
      $refund = refund::where('synced', null)->get();
      $refund_detail = refund_detail::leftJoin('refund', 'refund.id', '=', 'refund_detail.refund_id')->where('refund.synced', null)->select('refund_detail.*', 'refund.transaction_no')->get();
      $delivery = delivery::where('synced', null)->get();
      $delivery_detail = delivery_detail::leftJoin('delivery', 'delivery.id', '=', 'delivery_detail.delivery_id')->where('delivery.synced', null)->select('delivery_detail.*', 'delivery.transaction_no')->get();

      if(count($session_list) == 0 && count($transaction) == 0 && count($transaction_detail) == 0 && count($cashier) == 0 && count($cash_float) == 0 && count($refund) == 0 && count($refund_detail) == 0 && count($delivery) == 0 && count($delivery_detail) == 0)
      {
        $response = new \stdClass();
        $response->error = 2;
        $response->message = "No need to sync.";

        return response()->json($response);
      }

      if($branchSyncURL)
      {
        $response = Http::post($branchSyncURL, [
          'session_list' => $session_list,
          'branch_id' => $this->branch_id,
          'transaction' => $transaction,
          'transaction_detail' => $transaction_detail,
          'cashier' => $cashier,
          'cash_float' => $cash_float,
          'refund' => $refund,
          'refund_detail' => $refund_detail,
          'delivery' => $delivery,
          'delivery_detail' => $delivery_detail
        ]);

        if($response['error'] === 0)
        {
          session::whereIn('id', $session_list)->where('closed', 1)->update([
            'synced' => 1
          ]);

          cashier::where('synced', null)->where('closing', 1)->update([
            'synced' => 1
          ]);

          cash_float::where('synced', null)->update([
            'synced' => 1
          ]);

          refund::where('synced', null)->update([
            'synced' => 1
          ]);

          delivery::where('synced', null)->update([
            'synced' => 1
          ]);

          if($resync == 1)
          {
            $response = new \stdClass();
            $response->error = 0;
            $response->message = "Success";
          }
          elseif($resync == 0)
          {
            $response = $this->syncHQProductList($response['product_list'], $branchProductSyncURL);
          }
          
          return response()->json($response);
        }
        else
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "HQ sync API error.";

          return response()->json($response);
        }
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch sync URL not found.";
        return response()->json($response);
      }
    }

    public function syncHQProductList($product_list = [], $branchProductSyncURL)
    {
      if(!$product_list)
      {
        $product_list = [];
      }

      $barcode_array = array();
      $total_product_list = count($product_list);

      foreach($product_list as $key => $product)
      {
        \Log::info("Updating product list on ".$key." / ".$total_product_list);

        product::withTrashed()->updateOrCreate([
          'barcode' => $product['barcode']
        ],[
          'department_id' => $product['department_id'],
          'category_id' => $product['category_id'],
          'barcode' => $product['barcode'],
          'product_name' => $product['product_name'],
          'price' => $product['price'],
          'uom' => $product['uom'],
          'measurement' => $product['measurement'],
          'normal_wholesale_price' => $product['normal_wholesale_price'],
          'normal_wholesale_price2' => $product['normal_wholesale_price2'],
          'normal_wholesale_price3' => $product['normal_wholesale_price3'],
          'normal_wholesale_price4' => $product['normal_wholesale_price4'],
          'normal_wholesale_price5' => $product['normal_wholesale_price5'],
          'normal_wholesale_price6' => $product['normal_wholesale_price6'],
          'normal_wholesale_price7' => $product['normal_wholesale_price7'],
          'normal_wholesale_quantity' => $product['normal_wholesale_quantity'],
          'normal_wholesale_quantity2' => $product['normal_wholesale_quantity2'],
          'normal_wholesale_quantity3' => $product['normal_wholesale_quantity3'],
          'normal_wholesale_quantity4' => $product['normal_wholesale_quantity4'],
          'normal_wholesale_quantity5' => $product['normal_wholesale_quantity5'],
          'normal_wholesale_quantity6' => $product['normal_wholesale_quantity6'],
          'normal_wholesale_quantity7' => $product['normal_wholesale_quantity7'],
          'wholesale_price' => $product['wholesale_price'],
          'wholesale_price2' => $product['wholesale_price2'],
          'wholesale_price3' => $product['wholesale_price3'],
          'wholesale_price4' => $product['wholesale_price4'],
          'wholesale_price5' => $product['wholesale_price5'],
          'wholesale_price6' => $product['wholesale_price6'],
          'wholesale_price7' => $product['wholesale_price7'],
          'wholesale_quantity' => $product['wholesale_quantity'],
          'wholesale_quantity2' => $product['wholesale_quantity2'],
          'wholesale_quantity3' => $product['wholesale_quantity3'],
          'wholesale_quantity4' => $product['wholesale_quantity4'],
          'wholesale_quantity5' => $product['wholesale_quantity5'],
          'wholesale_quantity6' => $product['wholesale_quantity6'],
          'wholesale_quantity7' => $product['wholesale_quantity7'],
          'wholesale_start_date' => $product['wholesale_start_date'],
          'wholesale_end_date' => $product['wholesale_end_date'],
          'promotion_start' => $product['promotion_start'],
          'promotion_end' => $product['promotion_end'],
          'promotion_price' => $product['promotion_price'],
          'deleted_at' => $product['deleted_at']
        ]);

        if(!in_array($product['barcode'], $barcode_array))
        {
          array_push($barcode_array, $product['barcode']);
        }
      }

      if($branchProductSyncURL)
      {
        $response = Http::post($branchProductSyncURL, [
          'branch_id' => $this->branch_id,
          'barcode_array' => $barcode_array,
        ]);

        if($response['error'] == 0)
        {
          $response = new \stdClass();
          $response->error = 0;
          $response->message = "Success";

          return $response;
        }
        else
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "Something wrong";

          return $response;
        }
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch sync product URL not found";

        return $response;
      }
    }

    public function productSync()
    {
      $create_session = 0;
      if(isset($_GET['create_session']))
      {
        if($_GET['create_session'] == 1)
        {
          $create_session = 1;
        }
      }

      if(!$this->branch_id)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch ID is empty, please add in ENV file or clear cache.";

        return response()->json($response);
      }

      $syncURL = $this->hqProductSyncURL;

      if($syncURL)
      {
        $response = Http::post($syncURL, [
          'branch_id' => $this->branch_id,
        ]);

        if($response['error'] == 1 || $response->getStatusCode() == "500")
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "HQ server is not found or URL incorrect.";

          return response()->json($response);
        }

        $barcode_array = array();
        $product_list = $response['product_list'];

        if($product_list && is_array($product_list))
        {
          $total_product_list = count($product_list);
          foreach($product_list as $key => $product)
          {
            \Log::info("Updating product list on ".$key." / ".$total_product_list);

            product::withTrashed()->updateOrCreate([
              'barcode' => $product['barcode']
            ],[
              'department_id' => $product['department_id'],
              'category_id' => $product['category_id'],
              'barcode' => $product['barcode'],
              'product_name' => $product['product_name'],
              'price' => $product['price'],
              'uom' => $product['uom'],
              'measurement' => $product['measurement'],
              'normal_wholesale_price' => $product['normal_wholesale_price'],
              'normal_wholesale_price2' => $product['normal_wholesale_price2'],
              'normal_wholesale_price3' => $product['normal_wholesale_price3'],
              'normal_wholesale_price4' => $product['normal_wholesale_price4'],
              'normal_wholesale_price5' => $product['normal_wholesale_price5'],
              'normal_wholesale_price6' => $product['normal_wholesale_price6'],
              'normal_wholesale_price7' => $product['normal_wholesale_price7'],
              'normal_wholesale_quantity' => $product['normal_wholesale_quantity'],
              'normal_wholesale_quantity2' => $product['normal_wholesale_quantity2'],
              'normal_wholesale_quantity3' => $product['normal_wholesale_quantity3'],
              'normal_wholesale_quantity4' => $product['normal_wholesale_quantity4'],
              'normal_wholesale_quantity5' => $product['normal_wholesale_quantity5'],
              'normal_wholesale_quantity6' => $product['normal_wholesale_quantity6'],
              'normal_wholesale_quantity7' => $product['normal_wholesale_quantity7'],
              'wholesale_price' => $product['wholesale_price'],
              'wholesale_price2' => $product['wholesale_price2'],
              'wholesale_price3' => $product['wholesale_price3'],
              'wholesale_price4' => $product['wholesale_price4'],
              'wholesale_price5' => $product['wholesale_price5'],
              'wholesale_price6' => $product['wholesale_price6'],
              'wholesale_price7' => $product['wholesale_price7'],
              'wholesale_quantity' => $product['wholesale_quantity'],
              'wholesale_quantity2' => $product['wholesale_quantity2'],
              'wholesale_quantity3' => $product['wholesale_quantity3'],
              'wholesale_quantity4' => $product['wholesale_quantity4'],
              'wholesale_quantity5' => $product['wholesale_quantity5'],
              'wholesale_quantity6' => $product['wholesale_quantity6'],
              'wholesale_quantity7' => $product['wholesale_quantity7'],
              'wholesale_start_date' => $product['wholesale_start_date'],
              'wholesale_end_date' => $product['wholesale_end_date'],
              'promotion_start' => $product['promotion_start'],
              'promotion_end' => $product['promotion_end'],
              'promotion_price' => $product['promotion_price'],
              'deleted_at' => $product['deleted_at']
            ]);

            if(!in_array($product['barcode'], $barcode_array))
            {
              array_push($barcode_array, $product['barcode']);
            }
          }
        }

        $voucher_list = $response['voucher_list'];
        voucher::truncate();

        if($voucher_list && is_array($voucher_list))
        {
          foreach($voucher_list as $voucher)
          {
            voucher::create([
              'name' => $voucher['name'],
              'code' => $voucher['code'],
              'type' => $voucher['type'],
              'amount' => $voucher['amount'],
              'active' => $voucher['active']
            ]);
          }
        }

        $syncCompletedURL = $this->hqProductSyncCompletedURL;
        if($syncCompletedURL)
        {
          $response = Http::post($syncCompletedURL, [
            'branch_id' => $this->branch_id,
            'barcode_array' => $barcode_array
          ]);

          if($response['error'] == 1)
          {
            return response()->json($response);
          }

          $cashier_ip = $_SERVER['REMOTE_ADDR'];
          $now = date('Y-m-d H:i:s', strtotime(now()));

          if($create_session == 1)
          {
            $session = session::create([
              'ip' => $cashier_ip,
              'opening_date_time' => $now,
              'synced' => null,
              'closed' => null
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
          $response->message = "HQ sync product list completed URL not found.";

          return response()->json($response);
        }
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "HQ sync product list URL not found.";

        return response()->json($response);
      }
    }

    public function getDailyReport()
    {
      $reprint = 0;
      if(isset($_GET['reprint']))
      {
        $reprint = $_GET['reprint'];
      }

      $now = date('d/m/Y');
      $session = session::orderBy('id', 'desc')->first();
      // $session = session::where('id', 25)->first();

      $cashier = cashier::where('session_id', $session->id)->first();
      if(!$cashier)
      {
        $session = session::where('closed', 1)->orderBy('id', 'desc')->first();
      }

      if(!$session)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Session not found";
        return response()->json($response);
      }

      // daily report
      $all_ip = transaction::where('completed', 1)->where('session_id', $session->id)->groupBy('ip')->pluck('ip')->toArray();
      $pos_cashier = pos_cashier::whereIn('ip', $all_ip)->get();

      foreach($all_ip as $ip)
      {
        $ip_found = false;
        foreach($pos_cashier as $pos)
        {
          if($ip == $pos->ip)
          {
            $ip_found = true;
            break;
          }
        }

        if(!$ip_found)
        {
          $pos_detail = pos_cashier::create([
            'ip' => $ip,
            'type' => 2,
            'cashier_name' => null
          ]);

          $pos_cashier->push($pos_detail);
        }
      }

      $payment_type_list = ['cash', 'card', 'tng', 'maybank_qr', 'grab_pay', 'boost', 'other'];
      foreach($pos_cashier as $pos)
      {
        if(!$pos->cashier_name)
        {
          $pos->cashier_name = $pos->ip;
        }

        foreach($payment_type_list as $payment_type)
        {
          $pos[$payment_type] = 0;
        }

        $pos->total = 0;
      }

      $all_transaction = transaction::where('completed', 1)->where('session_id', $session->id)->get();

      $total_sales = 0;
      $only_cash_sales = 0;
      foreach($all_transaction as $all)
      {
        $total_sales += $all->total;
        if($all->payment_type == "cash")
        {
          $only_cash_sales += $all->total;
        }

        $payment_type = $all->payment_type;
        foreach($pos_cashier as $pos)
        {
          if($pos->ip == $all->ip)
          {
            if(in_array($payment_type, $payment_type_list))
            {
              $pos[$payment_type] += $all->total;
            }
            else
            {
              $pos['other'] += $all->total;
            }
            
            $pos->total += $all->total;
            break;
          }
        }
      }

      foreach($pos_cashier as $pos)
      {
        foreach($payment_type_list as $payment_type)
        {
          if($pos[$payment_type] == 0)
          {
            $pos[$payment_type] = "-";
          }
          else
          {
            $pos[$payment_type] = number_format($pos[$payment_type], 2);
          }
          
        }

        if($pos->total == 0)
        {
          $pos->total = "-";
        }
        else
        {
          $pos->total = number_format($pos->total, 2); 
        }
      }

      $payment_type_result = array();

      foreach($payment_type_list as $payment_type)
      {
        $payment_type_detail = new \stdClass();
        $payment_type_detail->type = $payment_type;
        $payment_type_detail->total = 0;

        array_push($payment_type_result, $payment_type_detail);
      }

      $payment_type_report = transaction::where('completed', 1)->where('session_id', $session->id)->select('*')->selectRaw('SUM(transaction.total) as payment_type_total')->groupBy('payment_type')->get();

      foreach($payment_type_report as $report_detail)
      {
        foreach($payment_type_result as $result)
        {
          if($report_detail->payment_type == "debit_card" || $report_detail->payment_type == "credit_card" || $report_detail->payment_type == "card")
          {
            $report_detail->payment_type = "card";
          }

          if($result->type == $report_detail->payment_type)
          {
            $result->total += $report_detail->payment_type_total;
            break;
          }
        }
      }

      foreach($payment_type_result as $result)
      {
        if($result->total == 0)
        {
          $result->total = "-";
        }
        else
        {
          $result->total = number_format($result->total, 2);
        }
      }

      $total_sales_text = $total_sales == 0 ? "-" : number_format($total_sales, 2);
      $only_cash_sales_text = $only_cash_sales == 0 ? "-" : number_format($only_cash_sales, 2);

      $total_opening = 0;
      $total_float_in = 0;
      $total_float_out = 0;
      $total_closing = 0;
      $total_boss = 0;
      $total_refund = 0;

      $opening_list = cashier::where('session_id', $session->id)->groupBy('ip')->get();

      foreach($opening_list as $opening_detail)
      {
        if($opening_detail->opening_amount)
        {
          $total_opening += $opening_detail->opening_amount;
        }
      }

      $closing_id_array = cashier::where('session_id', $session->id)->select('*')->selectRaw('MAX(id) as max_id')->orderBy('id', 'desc')->groupBy('ip')->pluck('max_id')->toArray();

      $closing_list = cashier::whereIn('id', $closing_id_array)->get();

      $total_diff = 0;

      foreach($closing_list as $closing_detail)
      {
        if($closing_detail->closing_amount)
        {
          $total_closing += $closing_detail->closing_amount;
        }

        if($closing_detail->diff)
        {
          $total_diff += $closing_detail->diff;
        }
      }

      $cash_float_list = cash_float::where('session_id', $session->id)->get();

      foreach($cash_float_list as $cash_float)
      {
        if($cash_float->type == 'in')
        {
          $total_float_in += $cash_float->amount;
        }
        elseif($cash_float->type == 'out')
        {
          $total_float_out += $cash_float->amount;
        }
        elseif($cash_float->type == 'boss')
        {
          $total_boss += $cash_float->amount;
        }
      }

      $refund = refund::where('session_id', $session->id)->get();

      foreach($refund as $value)
      {
        $total_refund += $value->total;
      }

      $total_cash = $total_opening + $total_float_in + $only_cash_sales + $total_diff;
      $total_deduct = $total_float_out + $total_refund + $total_opening;

      $cash_float_result = new \stdClass();
      $cash_float_result->opening = number_format($total_opening, 2);
      $cash_float_result->float_in = number_format($total_float_in, 2);
      $cash_float_result->cash_sales = $only_cash_sales_text;
      $cash_float_result->float_out = number_format($total_float_out, 2);
      $cash_float_result->closing = number_format($total_closing, 2);
      $cash_float_result->diff = $total_diff;
      $cash_float_result->diff_text = number_format( $total_diff, 2);
      $cash_float_result->total_cash = number_format($total_cash, 2);
      $cash_float_result->total_deduct = number_format($total_deduct, 2);
      $cash_float_result->total_boss = $total_boss;
      $cash_float_result->total_boss_text = number_format($total_boss, 2);
      $cash_float_result->total_refund = number_format($total_refund, 2);
      $cash_float_result->cash_to_company = number_format(($total_cash - $total_deduct), 2);

      // end daily report

      // cash float report
      $ip_array = array();
      $cashier_list = cashier::where('session_id', $session->id)->get();

      foreach($cashier_list as $cashier)
      {
        if(!in_array($cashier->ip, $ip_array))
        {
          array_push($ip_array, $cashier->ip);
        }
      }

      $pos_cashier_list = pos_cashier::whereIn('ip', $ip_array)->get();
      foreach($ip_array as $cashier_ip)
      {
        $cashier_found = false;
        foreach($pos_cashier_list as $pos_cashier_detail)
        {
          if($pos_cashier_detail->ip == $cashier_ip)
          {
            $cashier_found = true;
            break;
          }
        }

        if(!$cashier_found)
        {
          $new_pos_cashier = pos_cashier::create([
            'ip' => $cashier_ip,
            'type' => 2,
            'cashier_name' => null,
          ]);

          $pos_cashier_list->push($new_pos_cashier);
        }
      }

      foreach($pos_cashier_list as $cashier_detail)
      {
        if(!$cashier_detail->cashier_name)
        {
          $cashier_detail->cashier_name = $cashier_detail->ip;
        }

        $shift_list = cashier::where('session_id', $session->id)->where('ip', $cashier_detail->ip)->get();
        $final_remain = 0;
        foreach($shift_list as $s_key => $shift)
        {
          $drawer_cash = 0;
          $total_boss_cash = 0;
          $shift_float_in = 0;
          $shift_float_out = 0;
          $shift_boss = 0;
          $opening_amount = $shift->opening_amount;

          if($opening_amount)
          {
            $drawer_cash = $opening_amount;
          }

          $cash_float_list = cash_float::where('session_id', $session->id)->where('opening_id', $shift->id)->where('ip', $shift->ip)->get();

          $boss_cash_float = array();
          foreach($cash_float_list as $key => $cash_float_detail)
          {
            if($cash_float_detail->type == 'in')
            {
              $drawer_cash += $cash_float_detail->amount;
              $shift_float_in += $cash_float_detail->amount;
            }
            elseif($cash_float_detail->type == 'out')
            {
              $drawer_cash -= $cash_float_detail->amount;
              $shift_float_out += $cash_float_detail->amount;
            }
            elseif($cash_float_detail->type == 'boss')
            {
              $total_boss_cash += $cash_float_detail->amount;
              $shift_boss += $cash_float_detail->amount;

              array_push($boss_cash_float, $cash_float_detail);
              unset($cash_float_list[$key]);
            }
          }

          $shift_sales_transaction = transaction::where('completed', 1)->where('session_id', $session->id)->where('opening_id', $shift->id)->where('ip', $shift->ip)->select('*')->selectRaw('SUM(transaction.total) as total_sales')->groupBy('payment_type')->get();

          $total_cash_sales = 0;
          $total_card_sales = 0;
          $total_tng_sales = 0;
          $total_maybank_qr_sales = 0;
          $total_grab_pay_sales = 0;
          $total_boost_sales = 0;
          $total_other_sales = 0;

          foreach($shift_sales_transaction as $sales_transaction)
          {
            if($sales_transaction->payment_type == "cash")
            {
              $total_cash_sales = $sales_transaction->total_sales;
            }
            elseif($sales_transaction->payment_type == "card")
            {
              $total_card_sales = $sales_transaction->total_sales;
            }
            elseif($sales_transaction->payment_type == "tng")
            {
              $total_tng_sales = $sales_transaction->total_sales;
            }
            elseif($sales_transaction->payment_type == "maybank_qr")
            {
              $total_maybank_qr_sales = $sales_transaction->total_sales;
            }
            elseif($sales_transaction->payment_type == "grab_pay")
            {
              $total_grab_pay_sales = $sales_transaction->total_sales;
            }
            elseif($sales_transaction->payment_type == "boost")
            {
              $total_boost_sales = $sales_transaction->total_sales;
            }
            else
            {
              $total_other_sales = $sales_transaction->total_sales;
            }
          }

          $drawer_cash += $total_cash_sales;

          $opening_date_time = "";
          if($shift->opening_date_time)
          {
            $opening_date_time = date('d-M-Y, h:i A', strtotime($shift->opening_date_time));
          }

          $shift_refund = refund::where('session_id', $session->id)->where('opening_id', $shift->id)->where('ip', $shift->ip)->get();
          $shift_total_refund = 0;
          foreach($shift_refund as $value)
          {
            $shift_total_refund += $value->total;
          }

          $drawer_cash -= $shift_total_refund;

          $shift->opening_date_time_text = $opening_date_time;
          $shift->shift_count = $s_key + 1;
          $shift->float_in = $shift_float_in;
          $shift->float_out= $shift_float_out;
          $shift->boss = $shift_boss;
          $shift->refund = $shift_total_refund;
          $shift->cash_float = $cash_float_list;
          $shift->refund_list = $shift_refund;
          $shift->boss_cash_float = $boss_cash_float;
          $shift->cash_sales = $total_cash_sales;
          $shift->card_sales = $total_card_sales;
          $shift->tng_sales = $total_tng_sales;
          $shift->maybank_qr_sales = $total_maybank_qr_sales;
          $shift->grab_pay_sales = $total_grab_pay_sales;
          $shift->boost_sales = $total_boost_sales;
          $shift->other_sales = $total_other_sales;
          $shift->drawer_cash = $drawer_cash;
          $shift->boss_cash = $total_boss_cash;
          $shift->remain = $drawer_cash - $total_boss_cash;

          if($shift->diff != 0)
          {
            $final_remain = $shift->closing_amount;
          }
          else
          {
            $final_remain = $drawer_cash - $total_boss_cash;
          }
        }

        $cashier_detail->shift = $shift_list;
        $cashier_detail->final_remain = $final_remain;
        $cashier_detail->cash_float = cash_float::where('session_id', $session->id)->where('ip', $cashier_detail->ip)->get();
        $cashier_detail->refund_list = refund::where('session_id', $session->id)->where('ip', $shift->ip)->get();
      }
      // 

      return view('front.closing_report', compact('now', 'pos_cashier', 'payment_type_result', 'total_sales_text', 'only_cash_sales_text', 'cash_float_result', 'pos_cashier_list', 'reprint'));
    }

    public function deleteUser(Request $request)
    {
      User::where('id', $request->user_id)->update([
        'removed' => 1,
        'removed_by' => Auth::id()
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function addNewUser(Request $request)
    {
      $username_check = User::where('username', $request->username)->first();

      if($username_check)
      {
        $response = new \stdClass();
        $response->error = 2;
        $response->message = "Username has been used";

        return response()->json($response);
      }

      $user_type = $request->user_type;
      if($user_type == 0)
      {
        $user_type = null;
      }

      $user_detail = User::create([
        'user_type' => $user_type,
        'name' => $request->name,
        'username' => $request->username,
        'email' => uniqid()."@test.com",
        'password' => Hash::make($request->password),
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->user_detail = $user_detail;

      return response()->json($response);
    }

    public function editUser(Request $request)
    {
      $username_check = User::where('username', $request->username)->where('id', '<>', $request->user_id)->first();

      if($username_check)
      {
        $response = new \stdClass();
        $response->error = 2;
        $response->message = "Username has been used";

        return response()->json($response);
      }

      $update_query = [
        'username' => $request->username,
        'name' => $request->name
      ];

      if($request->password)
      {
        $update_query['password'] = Hash::make($request->password);
      }

      User::where('id', $request->user_id)->update($update_query);

      $user_detail = User::where('id', $request->user_id)->first();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->user_detail = $user_detail;

      return response()->json($response);
    }

    public function createCashier(Request $request)
    {
      $pos_cashier = pos_cashier::where('ip', $request->ip)->first();
      if($pos_cashier)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Cashier IP ".$request->ip." existed, please use another IP.";

        return response()->json($response);
      }

      $pos_cashier = pos_cashier::create([
        'type' => $request->type,
        'ip' => $request->ip,
        'cashier_name' => $request->name
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->pos_cashier = $pos_cashier;

      return response()->json($response);
    }

    public function deleteCashier(Request $request)
    {
      pos_cashier::where('id', $request->id)->delete();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";

      return response()->json($response);
    }

    public function editCashier(Request $request)
    {
      $pos_cashier = pos_cashier::where('ip', $request->ip)->where('id', '<>', $request->id)->first();
      if($pos_cashier)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Cashier IP ".$request->ip." existed, please use another IP.";

        return response()->json($response);
      }

      pos_cashier::where('id', $request->id)->update([
        'type' => $request->type,
        'ip' => $request->ip,
        'cashier_name' => $request->name
      ]);

      $updated_pos_cashier = pos_cashier::where('id', $request->id)->first();

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->pos_cashier = $updated_pos_cashier;

      return response()->json($response);
    }

    public function testing()
    {
      dd(env('DB_DATABASE'));
      print_r("start ori".date("H:i:s") . substr((string)microtime(), 1, 8).'<br>');
      $seq = Invoice_sequence::first();
      $now = now();
      if(date("Y-m-d",strtotime($seq->updated_at)) == date("Y-m-d", strtotime($now))){
        $transaction_no = $seq->branch_code.date("Ymd").$seq->next_seq;
      }else{
        $transaction_no = $seq->branch_code.date("Ymd", strtotime($now))."00001";
      }

      if(date("Y-m-d",strtotime($seq->updated_at)) == date('Y-m-d', strtotime($now))){
        $next = $seq->next_seq;
        $next = intval($next) + 1;
        $i=5;
        while($i>strlen($next)){
          $next = "0".$next;
        }
        
        Invoice_sequence::where('id',$seq->id)->update([
          'current_seq' => $seq->next_seq,
          'next_seq' => $next,
        ]);
      }else{
        Invoice_sequence::where('id',$seq->id)->update([
          'current_seq' => '00001',
          'next_seq' => '00002',
        ]);
      }

      print_r("end ori ".date("H:i:s") . substr((string)microtime(), 1, 8).'<br>');

      print_r("start ".date("H:i:s") . substr((string)microtime(), 1, 8).'<br>');
      $seq = Invoice_sequence::first();
      $count = transaction::whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])->count();
      for($a = strlen($count); $a < 5; $a++)
      {
        $count = "0".$count;
      }

      $transaction_no = $seq->branch_code.date('Ymd').$count;
      print_r("end ".date("H:i:s") . substr((string)microtime(), 1, 8).'<br>');
      dd($transaction_no);

      for($c = 0; $c <= 50; $c++)
      {
        $transaction_query = [];
        $transaction_detail_query = [];

        for($a = 0; $a <= 100; $a++)
        {
          $query = [
            "session_id" => 23,
            "ip" => "::1",
            "transaction_no" => "test001",
            "user_id" => 1,
            "subtotal" => 100,
            "payment" => 100,
            "payment_type" => "cash",
            "payment_type_text" => "Cash",
            "balance" => 0,
            "total" => 100,
            "completed" => 1,
            'transaction_date' => $now,
            'created_at' => $now,
            'updated_at' => $now
          ];

          array_push($transaction_query, $query);

          for($b = 0; $b <= 10; $b++)
          {
            $query = [
              "transaction_id" => $started_id,
              "product_id" => 1,
              "barcode" => "test001",
              "product_name" => "Test item",
              "quantity" => 100,
              "price" => 100,
              "discount" => 0,
              "subtotal" => 10000,
              "total" => 10000,
              'created_at' => $now,
              'updated_at' => $now
            ];

            array_push($transaction_detail_query, $query);
          }

          $started_id++;
        }

        transaction::insert($transaction_query);
        transaction_detail::insert($transaction_detail_query);
      }
    }

    public function front_function_list()
    {
      $front_function_list = [
        [
          'function' => "showVoucher()",
          'function_name' => "Show voucher",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showPreviousReceipt()",
          'function_name' => "Show previous receipt",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showOtherMenu()",
          'function_name' => "Show other menu",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showOpening()",
          'function_name' => "Show opening",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showClosing()",
          'function_name' => "Show closing",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showDailyClosing()",
          'function_name' => "Show daily closing",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showCashFloatIn()",
          'function_name' => "Show cash float in",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showCashFloatOut()",
          'function_name' => "Show cash float out",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showBagiKeKetua()",
          'function_name' => "Show bagi ke ketua",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showRefund()",
          'function_name' => "Show refund",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showClosingReport()",
          'function_name' => "Show closing report",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showUserManagement()",
          'function_name' => "Show user management",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "SCsyncHQTransaction()",
          'function_name' => "Sync transaction to HQ",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "SCsyncHQProductList()",
          'function_name' => "Sync HQ product list",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showCashCheckOut()",
          'function_name' => "Show cash checkout",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showPaymentTypeMenu()",
          'function_name' => "Show payment type menu",
          'code' => null,
          'character' => null
        ],
        // [
        //   'function' => "payAsDebit()",
        //   'function_name' => "Pay bill by Debit Card",
        //   'code' => null,
        //   'character' => null
        // ],
        // [
        //   'function' => "payAsCredit()",
        //   'function_name' => "Pay bill by Credit Card",
        //   'code' => null,
        //   'character' => null
        // ],
        [
          'function' => "payAsCard()",
          'function_name' => "Pay bill by Card",
          'code' => null,
          'character' => null
        ],
        // [
        //   'function' => "payAsEwallet()",
        //   'function_name' => "Pay bill by E-wallet",
        //   'code' => null,
        //   'character' => null
        // ],
        [
          'function' => "payAsTNG()",
          'function_name' => "Pay bill by Touch & Go",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "payAsMaybank()",
          'function_name' => "Pay bill by Maybank QRCode",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "payAsGrab()",
          'function_name' => "Pay bill by Grab Pay",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "payAsBoost()",
          'function_name' => "Pay bill by Boots",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "clearTransaction()",
          'function_name' => "Clear transaction",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showKeySetup()",
          'function_name' => "Show shortcut key setup page",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "showBranchProfile()",
          'function_name' => "Show branch profile setup page",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "clickManualKeyin()",
          'function_name' => "Manual keyin barcode",
          'code' => null,
          'character' => null
        ],
        [
          'function' => "clickExactButton()",
          'function_name' => "Exact button at cash checkout",
          'code' => null,
          'character' => null
        ],
      ];

      return $front_function_list;
    }

    public function getBranchProfile()
    {
      $user = Auth::user();

      if($user->user_type != 1)
      {
        return redirect(route('home'));
      }

      $invoice_sequence = Invoice_sequence::first();
      $profile = profile::first();

      $branch_code = null;
      $branch_address = null;
      $contact_number = null;

      if($invoice_sequence)
      {
        $branch_code = $invoice_sequence->branch_code;
      }

      if($profile)
      {
        $branch_address = $profile->address;
        $contact_number = $profile->contact_number;
      }

      return view('front.profile', compact('branch_code', 'branch_address', 'contact_number'));
    }

    public function updateProfile(Request $request)
    {
      $invoice_sequence = Invoice_sequence::first();
      $profile = profile::first();

      if($invoice_sequence)
      {
        Invoice_sequence::where('id', $invoice_sequence->id)->update([
          'branch_code' => $request->branch_code
        ]);
      }
      else
      {
        Invoice_sequence::create([
          'branch_code' => $request->branch_code,
          'current_seq' => '00000',
          'next_seq' => '00001',
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }

      if($profile)
      {
        profile::where('id', $profile->id)->update([
          'address' => $request->branch_address,
          'contact_number' => $request->contact_number
        ]);
      }
      else
      {
        profile::create([
          'address' => $request->branch_address,
          'contact_number' => $request->contact_number
        ]);
      }

      return redirect(route('getBranchProfile'));
    }

    public function getlogout()
    {
      Auth::logout();

      return redirect(route('home'));
    }

    public function refundNow(Request $request)
    {
      $user = Auth::user();
      $product_id = $request->product_id;
      if($product_id)
      {
        $now = date('Y-m-d H:i:s', strtotime(now()));
        $session = session::where('closed', null)->orderBy('id', 'desc')->first();

        if(!$session)
        {
          $session = session::create([
            'ip' => $this->ip,
            'opening_date_time' => $now,
            'synced' => null,
            'closed' => null
          ]);
        }

        $opening_id = null;
        $opening = cashier::where('session_id', $session->id)->where('ip', $this->ip)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

        if($opening)
        {
          $opening_id = $opening->id;
        }

        $cashier_name = null;
        $cashier_detail = pos_cashier::where('ip', $this->ip)->first();

        if($cashier_detail)
        {
          $cashier_name = $cashier_detail->cashier_name;
        }

        $refund_seq = Invoice_sequence::where('branch_code', "RN")->first();
        if(!$refund_seq)
        {
          $refund_seq = $this->init();
        }

        $transaction_no = null;

        if(date("Y-m-d",strtotime($refund_seq->updated_at)) == date("Y-m-d", strtotime($now))){
          $transaction_no = $refund_seq->branch_code.date("Ymd").$refund_seq->next_seq;
        }else{
          $transaction_no = $refund_seq->branch_code.date("Ymd", strtotime($now))."0001";
        }
        
        $refund = refund::create([
          'session_id' => $session->id,
          'opening_id' => $opening_id,
          'ip' => $this->ip,
          'cashier_name' => $cashier_name,
          'user_id' => $user->id,
          'user_name' => $user->name,
          'transaction_no' => $transaction_no,
          'synced' => null
        ]);

        if(date("Y-m-d",strtotime($refund_seq->updated_at)) == date('Y-m-d', strtotime($now))){
          $next = $refund_seq->next_seq;
          $next = intval($next) + 1;
          $i=4;
          while($i>strlen($next)){
            $next = "0".$next;
          }
          
          Invoice_sequence::where('id',$refund_seq->id)->update([
            'current_seq' => $refund_seq->next_seq,
            'next_seq' => $next,
          ]);
        }else{
          Invoice_sequence::where('id',$refund_seq->id)->update([
            'current_seq' => '0001',
            'next_seq' => '0002',
          ]);
        }

        $subtotal = 0;
        $round_off = 0;
        $total = 0;
        $total_quantity = 0;

        foreach($product_id as $id)
        {
          $product_detail = product::where('id', $id)->first();

          $refund_price_name = "price_".$id;
          $refund_price = $request->$refund_price_name;

          $quantity_name = "quantity_".$id;
          $quantity = $request->$quantity_name;

          $measurement_name = "measurement_".$id;
          $measurement = $request->$measurement_name;

          $measurement_type_name = "measurement_type_".$id;
          $measurement_type = $request->$measurement_type_name;

          if($measurement_type == "null")
          {
            $measurement_type = null;
          }
          $total_quantity += $quantity;

          refund_detail::create([
            'refund_id' => $refund->id,
            'department_id' => $product_detail->department_id,
            'category_id' => $product_detail->category_id,
            'product_id' => $id,
            'barcode' => $product_detail->barcode,
            'product_name' => $product_detail->product_name,
            'quantity' => $quantity,
            'measurement_type' => $measurement_type,
            'measurement' => $measurement,
            'price' => ($refund_price / $quantity / $measurement),
            'subtotal' => $refund_price,
            'total' => $refund_price
          ]);

          $subtotal += $refund_price;
        }

        $total_summary = $this->roundDecimal($subtotal);

        refund::where('id', $refund->id)->update([
          'subtotal' => $subtotal,
          'round_off' => $total_summary->round_off,
          'total' => $total_summary->final_total
        ]);

        $updated_refund = refund::where('id', $refund->id)->first();
        $refund_detail = refund_detail::where('refund_id', $refund->id)->get();

        $updated_refund->total_items = count($product_id);
        $updated_refund->total_quantity = $total_quantity;
        $updated_refund->total_text = number_format($updated_refund->total, 2);
        $updated_refund->date = date('l, d-m-Y', strtotime($updated_refund->created_at));
        $updated_refund->time = date('H:i', strtotime($updated_refund->created_at));

        foreach($refund_detail as $value)
        {
          $value->price_text = number_format($value->price, 2);
          $value->total_text = number_format($value->total, 2);
        }

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->refund = $updated_refund;
        $response->refund_detail = $refund_detail;

        return response()->json($response);
      }

      $response = new \stdClass();
      $response->error = 1;
      $response->message = "Empty item";

      return response()->json($response);
    }

    public function updateTransactionMeasurement(Request $request)
    {
      $transaction_detail = transaction_detail::where('id', $request->transaction_detail_id)->first();

      $response = $this->productPrice($transaction_detail->barcode, $request->measurement, null);
      if($response->wholesale == 1)
      {
        transaction_detail::where('id', $transaction_detail->id)->update([
          'wholesale_price' => $response->product_price
        ]);
      }
      elseif($response->wholesale == 0 && $response->product_price != $transaction_detail->price)
      {
        transaction_detail::where('id', $transaction_detail->id)->update([
          'price' => $response->product_price,
          'wholesale_price' => null
        ]);
      }

      $subtotal = $transaction_detail->quantity * $request->measurement * $response->product_price;
      $total = $transaction_detail->quantity * $request->measurement * $response->product_price;

      transaction_detail::where('id', $request->transaction_detail_id)->update([
        'measurement' => $request->measurement,
        'subtotal' => $subtotal,
        'total' => $total
      ]);

      $transaction = transaction::where('id', $transaction_detail->transaction_id)->first();
      $transaction_summary = $this->transaction_summary($transaction);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->transaction_summary = $transaction_summary;

      return response()->json($response);
    }

    public function removeTransactionMeasurement(Request $request)
    {
      $transaction_detail = transaction_detail::where('id', $request->transaction_detail_id)->first();
      $transaction = transaction::where('id', $transaction_detail->transaction_id)->first();

      transaction_detail::where('id', $request->transaction_detail_id)->delete();

      $transaction_summary = $this->transaction_summary($transaction);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->transaction_summary = $transaction_summary;

      return response()->json($response);
    }

    public function getProductPrice(Request $request)
    {
      $response = $this->productPrice($request->barcode, $request->quantity, $request->product_id);
      return response()->json($response);
    }

    public function productPrice($barcode, $quantity, $product_id = null)
    {
      if(!$product_id && $barcode)
      {
        $product_detail = product::where('barcode', $barcode)->first();
      }
      elseif($product_id && !$barcode)
      {
        $product_detail = product::where('id', $product_id)->first();
      }
      else
      {
        dd("no barcode and product ID ?");
      }
      
      if($product_detail)
      {
        $now = date('Y-m-d H:i:s');
        $product_price = $product_detail->price;
        $wholesale = 0;

        if($product_detail->promotion_start && $product_detail->promotion_end && $product_detail->promotion_price)
        {
          if($now >= $product_detail->promotion_start && $now <= $product_detail->promotion_end)
          {
            $product_price = $product_detail->promotion_price;
          }
        }

        if($product_detail->normal_wholesale_price && $product_detail->normal_wholesale_quantity)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity)
          {
            $product_price = $product_detail->normal_wholesale_price;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price2 && $product_detail->normal_wholesale_quantity2)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity2)
          {
            $product_price = $product_detail->normal_wholesale_price2;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price3 && $product_detail->normal_wholesale_quantity3)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity3)
          {
            $product_price = $product_detail->normal_wholesale_price3;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price4 && $product_detail->normal_wholesale_quantity4)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity4)
          {
            $product_price = $product_detail->normal_wholesale_price4;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price5 && $product_detail->normal_wholesale_quantity5)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity5)
          {
            $product_price = $product_detail->normal_wholesale_price5;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price6 && $product_detail->normal_wholesale_quantity6)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity6)
          {
            $product_price = $product_detail->normal_wholesale_price6;
            $wholesale = 1;
          }
        }

        if($product_detail->normal_wholesale_price7 && $product_detail->normal_wholesale_quantity7)
        {
          if($quantity >= $product_detail->normal_wholesale_quantity7)
          {
            $product_price = $product_detail->normal_wholesale_price7;
            $wholesale = 1;
          }
        }

        if($product_detail->wholesale_start_date && $product_detail->wholesale_end_date)
        {
          if($now >= $product_detail->wholesale_start_date && $now <= $product_detail->wholesale_end_date)
          {
            if($product_detail->wholesale_quantity && $product_detail->wholesale_price)
            {
              if($quantity >= $product_detail->wholesale_quantity)
              {
                $product_price = $product_detail->wholesale_price;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity2 && $product_detail->wholesale_price2)
            {
              if($quantity >= $product_detail->wholesale_quantity2)
              {
                $product_price = $product_detail->wholesale_price2;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity3 && $product_detail->wholesale_price3)
            {
              if($quantity >= $product_detail->wholesale_quantity3)
              {
                $product_price = $product_detail->wholesale_price3;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity4 && $product_detail->wholesale_price4)
            {
              if($quantity >= $product_detail->wholesale_quantity4)
              {
                $product_price = $product_detail->wholesale_price4;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity5 && $product_detail->wholesale_price5)
            {
              if($quantity >= $product_detail->wholesale_quantity5)
              {
                $product_price = $product_detail->wholesale_price5;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity6 && $product_detail->wholesale_price6)
            {
              if($quantity >= $product_detail->wholesale_quantity6)
              {
                $product_price = $product_detail->wholesale_price6;
                $wholesale = 1;
              }
            }

            if($product_detail->wholesale_quantity7 && $product_detail->wholesale_price7)
            {
              if($quantity >= $product_detail->wholesale_quantity7)
              {
                $product_price = $product_detail->wholesale_price7;
                $wholesale = 1;
              }
            }
          }
        }

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Success";
        $response->product_price = $product_price;
        $response->product_price_text = number_format($product_price, 2);
        $response->wholesale = $wholesale;

        return $response;
      }
      else
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Product not found.";

        return $response;
      }
    }

    public function submitDelivery(Request $request)
    {
      $user = Auth::user();
      $transaction = transaction::where('id', $request->transaction_id)->first();
      $transaction_detail = transaction_detail::where('transaction_id', $request->transaction_id)->get();

      if($transaction && count($transaction_detail) > 0)
      {
        $total = 0;
        $subtotal = 0;
        $total_discount = 0;

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
        $total_summary = $this->roundDecimal($total);
        $total = $total_summary->final_total;
        $round_off = $total_summary->round_off;

        $count = delivery::whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])->count();
        $count++;
        for($a = strlen($count); $a < 5; $a++)
        {
          $count = "0".$count;
        }

        $transaction_no = "DE".date('Ymd').$count;
        $delivery_type = $request->delivery_type;
        $delivery_type_text = null;

        if($delivery_type == "pandamart")
        {
          $delivery_type_text = "PandaMart";
        }
        elseif($delivery_type == "grabmart")
        {
          $delivery_type_text = "GrabMart";
        }

        $delivery = delivery::create([
          'session_id' => $transaction->session_id,
          'opening_id' => $transaction->opening_id,
          'ip' => $transaction->ip,
          'cashier_name' => $transaction->cashier_name,
          'transaction_no' => $transaction_no,
          'user_id' => $transaction->user_id,
          'user_name' => $transaction->user_name,
          'subtotal' => $subtotal,
          'total_discount' => $transaction->total_discount,
          'voucher_id' => $transaction->voucher_id,
          'voucher_code' => $transaction->voucher_code,
          'delivery_type' => $delivery_type,
          'delivery_type_text' => $delivery_type_text,
          'total' => $total,
          'round_off' => $round_off,
          'completed' => 1,
          'completed_by' => $user->id,
          'transaction_date' => date('Y-m-d H:i:s'),
        ]);

        foreach($transaction_detail as $detail)
        {
          delivery_detail::create([
            'delivery_id' => $delivery->id,
            'department_id' => $detail->department_id,
            'category_id' => $detail->category_id,
            'product_id' => $detail->product_id,
            'barcode' => $detail->barcode,
            'product_name' => $detail->product_name,
            'quantity' => $detail->quantity,
            'measurement_type' => $detail->measurement_type,
            'measurement' => $detail->measurement,
            'price' => $detail->price,
            'wholesale_price' => $detail->wholesale_price,
            'discount' => $detail->discount,
            'subtotal' => $detail->subtotal,
            'total' => $detail->total,
          ]);
        }

        transaction::where('id', $request->transaction_id)->forceDelete();
        transaction_detail::where('transaction_id', $request->transaction_id)->forceDelete();

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Order successfully submitted.";

        return response()->json($response);
      }

      $response = new \stdClass();
      $response->error = 1;
      $response->message = "Transaction not found.";

      return response()->json($response);
    }

    public function init()
    {
      $refund_invoice = Invoice_sequence::where('branch_code', "RN")->first();
      if(!$refund_invoice)
      {
        $refund_invoice =  Invoice_sequence::create([
          'branch_code' => 'RN',
          'current_seq' => '0000',
          'next_seq' => '0001',
        ]);
      }

      return $refund_invoice;
    }
}

