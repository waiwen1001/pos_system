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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    private $ip;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->middleware('auth', ['except' => ['syncHQProductList', 'testing', 'branchSync']]);
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
      $profile = profile::first();
      if($profile)
      {
        $branch_name = $profile->branch_name;
        $branch_address = $profile->address;
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
      $total_quantity = 0;

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
        $total_quantity = $transaction_summary->total_quantity;

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

      if($session)
      {
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
            'session_id' => $session->id,
            'opening' => 1,
            'opening_amount' => $prev_cashier->closing_amount,
            'opening_by' => $user->id,
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
      $ip = $this->ip;

      // default is cashier
      $device_type = 2;
      $device = pos_cashier::where('ip', $ip)->first();
      if($device)
      {
        $device_type = $device->type;
      }

      return view('front.index', compact('user', 'user_management_list', 'pending_transaction', 'subtotal', 'discount', 'have_discount', 'total', 'real_total', 'round_off', 'payment', 'balance', 'transaction_id', 'completed_transaction', 'opening', 'voucher_name', 'session', 'shortcut_key', 'ip', 'device_type', 'branch_name', 'branch_address', 'total_quantity'));
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

        $transaction_wholesale_price = 0;
        $transaction_wholesale_quantity = 0;
        $quantity = 1;
        $subtotal = round($product->price, 2);
        $total = round($product->price, 2);

        if($product->wholesale_price && $product->wholesale_start_date && $product->wholesale_end_date)
        {
          if($product->wholesale_start_date <= $now && $product->wholesale_end_date >= $now)
          {
            if($product->wholesale_quantity == 1)
            {
              $transaction_wholesale_price = $product->wholesale_price;
              $transaction_wholesale_quantity = 1;
              $quantity = 0;
              $subtotal = round($product->wholesale_price, 2);
              $total = round($product->wholesale_price, 2);
            }
          }
        }

        $user = Auth::user();
        $transaction = transaction::where('completed', null)->where('ip', $this->ip)->first();

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
            'user_id' => $user->id
          ]);

          transaction_detail::create([
            'transaction_id' => $transaction->id,
            'department_id' => $product->department_id,
            'category_id' => $product->category_id,
            'product_id' => $product->id,
            'barcode' => $product->barcode,
            'product_name' => $product->product_name,
            'price' => round($product->price, 2),
            'quantity' => $quantity,
            'wholesale_price' => $transaction_wholesale_price,
            'wholesale_quantity' => $transaction_wholesale_quantity,
            'discount' => 0,
            'subtotal' => $subtotal,
            'total' => $total
          ]);
        }
        else
        {
          $transaction_detail = transaction_detail::where('transaction_id', $transaction->id)->where('product_id', $product->id)->where('void', null)->first();

          if($transaction_detail)
          {
            $quantity = $transaction_detail->quantity + 1;
            $transaction_wholesale_quantity = $transaction_detail->wholesale_quantity;
            $transaction_wholesale_price = $transaction_detail->wholesale_price;
            $subtotal = round( ($transaction_detail->quantity + 1) * $product->price, 2);
            $total = round( ($transaction_detail->quantity + 1) * $product->price, 2);

            $check_wholesale = false;
            if($product->wholesale_quantity)
            {
              $check_wholesale = $quantity % $product->wholesale_quantity;
            }

            if($check_wholesale == 0 || $product->wholesale_quantity == 1)
            {
              $quantity = $quantity - $product->wholesale_quantity;
              $transaction_wholesale_quantity = $transaction_wholesale_quantity + $product->wholesale_quantity;

              $transaction_wholesale_price = $product->wholesale_price;
            }

            $subtotal = ( $product->price * $quantity ) + ( $transaction_wholesale_quantity * $transaction_wholesale_price );
            $total = ( $product->price * $quantity ) + ( $transaction_wholesale_quantity * $transaction_wholesale_price );

            transaction_detail::where('id', $transaction_detail->id)->update([
              'price' => round($product->price, 2),
              'quantity' => $quantity,
              'wholesale_quantity' => $transaction_wholesale_quantity,
              'wholesale_price' => $transaction_wholesale_price,
              'subtotal' => $subtotal,
              'total' => $total
            ]);
          }
          else
          {
            transaction_detail::create([
              'transaction_id' => $transaction->id,
              'department_id' => $product->department_id,
              'category_id' => $product->category_id,
              'product_id' => $product->id,
              'barcode' => $product->barcode,
              'product_name' => $product->product_name,
              'price' => round($product->price, 2),
              'quantity' => $quantity,
              'wholesale_price' => $transaction_wholesale_price,
              'wholesale_quantity' => $transaction_wholesale_quantity,
              'discount' => 0,
              'subtotal' => $subtotal,
              'total' => $total
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
      })->limit(7)->get();

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
          $total_quantity += ($item->quantity + $item->wholesale_quantity);
          $transaction_price = 0;
          $transaction_wholesale_price = 0;

          if($item->quantity > 0)
          {
            $transaction_price = $item->quantity * $item->price;
          }

          if($item->wholesale_quantity > 0)
          {
            $transaction_wholesale_price = $item->wholesale_quantity * $item->wholesale_price;
          }

          $item->subtotal_text = number_format($item->subtotal, 2);

          $subtotal = $subtotal + $item->subtotal;
          $total = $total + $item->total;
          $item->total_price_text = number_format($transaction_price, 2);
          $item->total_wholesale_price_text = number_format($transaction_wholesale_price, 2);
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
          $payment_type_text = "Card";
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
        $now = now();
        if(date("Y-m-d",strtotime($seq->updated_at)) == date("Y-m-d", strtotime($now))){
          $transaction_no = $seq->branch_code.date("Ymd").$seq->next_seq;
        }else{
          $transaction_no = $seq->branch_code.date("Ymd", strtotime($now))."00001";
        }

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

      transaction::where('id', $request->transaction_id)->update([
        'total_discount' => 0,
        'voucher_id' => null,
        'voucher_code' => null
      ]);

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

      $total_quantity = $transaction_detail->quantity + $transaction_detail->wholesale_quantity;
      if($request->type == "plus")
      {
        $total_quantity--;
      }
      elseif($request->type == "minus")
      {
        $total_quantity++;
      }

      if($product->wholesale_price && $product->wholesale_start_date && $product->wholesale_end_date)
      {
        if($product->wholesale_start_date <= $now && $product->wholesale_end_date >= $now)
        {
          if($total_quantity >= $product->wholesale_quantity)
          {
            $mod_quantity = $total_quantity % $product->wholesale_quantity;
            $transaction_detail->quantity = $mod_quantity;
            $transaction_detail->wholesale_quantity = ($total_quantity - $mod_quantity);

            $transaction_detail->wholesale_price = $product->wholesale_price;
          }
          elseif($total_quantity < $product->wholesale_quantity)
          {
            $transaction_detail->quantity = $total_quantity;
            $transaction_detail->wholesale_quantity = 0;
          }
        }
      }
      else
      {
        $transaction_detail->quantity = $total_quantity;
      }

      $subtotal = ($transaction_detail->price * $transaction_detail->quantity) + ($transaction_detail->wholesale_price * $transaction_detail->wholesale_quantity);
      $total = ($transaction_detail->price * $transaction_detail->quantity) + ($transaction_detail->wholesale_price * $transaction_detail->wholesale_quantity);

      if($total_quantity == 0)
      {
        transaction_detail::where('id', $request->item_id)->delete();
      }
      else
      {
        transaction_detail::where('id', $request->item_id)->update([
          'price' => $product->price,
          'quantity' => $transaction_detail->quantity,
          'wholesale_quantity' => $transaction_detail->wholesale_quantity,
          'wholesale_price' => $transaction_detail->wholesale_price,
          'subtotal' => round($subtotal, 2),
          'total' => round($total, 2)
        ]);
      }

      $transaction_detail_list = transaction_detail::where('transaction_id', $transaction_detail->transaction_id)->get();
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

      $transaction_price = $transaction_detail->price * $transaction_detail->quantity;
      $transaction_wholesale_price = $transaction_detail->wholesale_price * $transaction_detail->wholesale_quantity;


      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->quantity = $total_quantity;
      $response->price = $transaction_price;
      $response->price_text = number_format($transaction_price, 2);
      $response->wholesale_price = $transaction_wholesale_price;
      $response->wholesale_price_text = number_format($transaction_wholesale_price, 2);
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
          'opening_date_time' => $now
        ]);
      }

      $cashier = cashier::where('ip', $this->ip)->where('session_id', $session->id)->where('opening', 1)->where('closing', null)->orderBy('id', 'desc')->first();

      if(!$cashier)
      {
        cashier::create([
          'ip' => $this->ip,
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
            'opening_date_time' => $now
          ]);
        }

        cashier::where('id', $cashier->id)->update([
          'closing' => 1,
          'closing_amount' => $request->closing_amount,
          'calculated_amount' => $request->calculated_amount,
          'diff' => $request->closing_amount - $request->calculated_amount,
          'closing_by' => $user->id,
          'closing_date_time' => $now
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
      $session = session::where('closed', null)->orderBy('id', 'desc')->first();

      if($session)
      {
        $session_id = $session->id;
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
        $now = date('Y-m-d H:i:s', strtotime(now()));

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
        
        if($session)
        {
          session::where('id', $session_id)->update([
            'closing_date_time' => $now,
            'closed' => 1,
          ]);
        }

        session::where('closed', null)->update([
          'closed' => 1,
          'synced' => null
        ]);

        $response = $this->branchSync();
        return $response;
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

      cash_float::create([
        'user_id' => $user->id,
        'ip' => $this->ip,
        'session_id' => $session->id,
        'opening_id' => $cashier->id,
        'type' => $request->type,
        'amount' => $request->amount,
        'remarks' => $request->remarks
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Cash float ".$request->type." RM ".$request->amount;;

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
        elseif($cash_float->type == "refund")
        {
          $closing_amount -= $cash_float->amount;
        }
      }

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
        $transaction_detail->price_text = number_format($transaction_detail->price, 2);
        $transaction_detail->wholesale_price_text = number_format($transaction_detail->wholesale_price, 2);
        $transaction_detail->total_text = number_format($transaction_detail->total, 2);

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

      if(isset($_GET['resync']))
      {
        $resync = $_GET['resync'];
      }

      if($resync == 0)
      {
        $session_list = session::where('closed', 1)->where('synced', null)->pluck('id')->toArray();
      }
      elseif($resync == 1)
      {
        $session = session::where('closed', 1)->orderBy('id', 'desc')->first();
        $session_list = [$session->id];
      }
      
      $branch_id = env('branch_id');

      if(!$branch_id)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch ID is empty, please add in ENV file or clear cache.";

        return response()->json($response);
      }

      if(count($session_list) == 0)
      {
        $response = new \stdClass();
        $response->error = 2;
        $response->message = "Session not found.";

        return response()->json($response);
      }

      $transaction = transaction::whereIn('session_id', $session_list)->get();
      $transaction_detail = transaction_detail::leftJoin('transaction', 'transaction.id', '=', 'transaction_detail.transaction_id')->whereIn('transaction.session_id', $session_list)->select('transaction_detail.*', 'transaction.session_id')->get();

      $branchSyncURL = env('branchSyncURL');

      if($branchSyncURL)
      {
        $response = Http::post($branchSyncURL, [
          'session_list' => $session_list,
          'branch_id' => $branch_id,
          'transaction' => $transaction,
          'transaction_detail' => $transaction_detail
        ]);

        if($response['error'] == 0)
        {
          session::whereIn('id', $session_list)->update([
            'synced' => 1
          ]);

          $response = $this->syncHQProductList($response['product_list']);
          return response()->json($response);
        }
        else
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "HQ Sycn API error.";

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

    public function syncHQProductList($product_list = [])
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

        product::updateOrCreate([
          'barcode' => $product['barcode']
        ],[
          'department_id' => $product['department_id'],
          'category_id' => $product['category_id'],
          'barcode' => $product['barcode'],
          'product_name' => $product['product_name'],
          'price' => $product['price'],
          'promotion_start' => $product['promotion_start'],
          'promotion_end' => $product['promotion_end'],
          'promotion_price' => $product['promotion_price']
        ]);

        if(!in_array($product['barcode'], $barcode_array))
        {
          array_push($barcode_array, $product['barcode']);
        }
      }

      $branchProductSyncURL = env('branchProductSyncURL');

      if($branchProductSyncURL)
      {
        $response = Http::post($branchProductSyncURL, [
          'branch_id' => env('branch_id'),
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
      $branch_id = env('branch_id');

      $create_session = 0;
      if(isset($_GET['create_session']))
      {
        if($_GET['create_session'] == 1)
        {
          $create_session = 1;
        }
      }

      if(!$branch_id)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Branch ID is empty, please add in ENV file or clear cache.";

        return response()->json($response);
      }

      $syncURL = env('hqProductSyncURL');

      if($syncURL)
      {
        $response = Http::post($syncURL, [
          'branch_id' => $branch_id,
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

            product::updateOrCreate([
              'barcode' => $product['barcode']
            ],[
              'department_id' => $product['department_id'],
              'category_id' => $product['category_id'],
              'barcode' => $product['barcode'],
              'product_name' => $product['product_name'],
              'price' => $product['price'],
              'uom' => $product['uom'],
              'promotion_start' => $product['promotion_start'],
              'promotion_end' => $product['promotion_end'],
              'promotion_price' => $product['promotion_price'],
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

        $syncCompletedURL = env('hqProductSyncCompletedURL');
        if($syncCompletedURL)
        {
          $response = Http::post($syncCompletedURL, [
            'branch_id' => $branch_id,
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
              'opening_date_time' => $now
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
      $session = session::orderBy('id', 'desc')->first();
      // $session = session::where('id', 25)->first();
      if(!$session)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Session not found";
        return response()->json($response);
      }

      $cashier = cashier::where('session_id', $session->id)->first();
      if(!$cashier)
      {
        $session = session::where('closed', 1)->orderBy('id', 'desc')->first();
      }

      $all_ip = transaction::where('completed', 1)->where('session_id', $session->id)->groupBy('ip')->pluck('ip')->toArray();
      $pos_cashier = pos_cashier::whereIn('ip', $all_ip)->get();

      $payment_type_list = ['cash', 'card', 'tng', 'maybank_qr', 'grab_pay', 'boost'];
      foreach($pos_cashier as $pos)
      {
        foreach($payment_type_list as $payment_type)
        {
          $pos[$payment_type] = 0;
        }

        $pos->total = 0;
      }

      $all_transaction = transaction::where('completed', 1)->where('session_id', $session->id)->get();

      $total_sales = 0;
      foreach($all_transaction as $all)
      {
        $total_sales += $all->total;
        $payment_type = $all->payment_type;
        foreach($pos_cashier as $pos)
        {
          if($pos->ip == $all->ip)
          {
            $pos[$payment_type] += $all->total;
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

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Success";
      $response->payment_type_result = $payment_type_result;
      $response->total_sales = $total_sales == 0 ? "-" : number_format($total_sales, 2);
      $response->pos_cashier = $pos_cashier;
      $response->session = $session;

      return response()->json($response);
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
      dd($this->getDailyReport());
      $now = date('Y-m-d H:i:s', strtotime(now()));
      $started_id = 34;

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
}

