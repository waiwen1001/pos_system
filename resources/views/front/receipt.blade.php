<!DOCTYPE html>
<html>
<head>
  <title>Receipt</title>
</head>
<body onload="window.print()">
  <!-- print receipt -->
  <div id="receipt">
    <div>
      <div style="display: flex; flex-direction: column; text-align: center;">
        <label style="font-size: 11px;">HOME U(M) SDN BHD (125272-P)</label>
        <label style="font-size: 11px;">{{ $contact_number }}</label>
        <label style="font-size: 11px;">{!! nl2br(e($branch_address)) !!}</label>
        <!-- <label>RESIT</label> -->
        <label id="refund_title" style="display: none;">( Refund )</label>
        <label id="refund_transaction_no" style="display: none;"></label>
        <br/>
      </div>
      <div style="border: 2px dashed #000; height: 2px;"></div>
      <div id="receipt_items">
        <table style='font-size: 11px;width:100%;border-spacing:0px 1px;'>
          @foreach($transaction_detail_list as $value)
            <tr>
              <td style='vertical-align:top;' colspan='3'> {{ $value->product_name }}
                @if($value->measurement_type == "kilogram")
                  ( {{ $value->measurement }}KG )
                @elseif($value->measurement_type == "meter")
                  ( {{ $value->measurement }}Meter )
                @endif
              </td>
            </tr>
            <tr>
              <td style='vertical-align:top;'>{{ $value->barcode }}</td>
              <td style='width: 120px;vertical-align:top;text-align:right;'>
                @if($value->quantity > 0)
                  @if($value->wholesale_price)
                    {{ $value->quantity }}.00 X RM {{ $value->wholesale_price_text }}
                  @else
                    {{ $value->quantity }}.00 X RM {{ $value->price_text }}
                  @endif
                @endif
              </td>
              <td style='width:70px;text-align:right;vertical-align:top;'>RM {{ $value->total_text }}</td>
            </tr>
          @endforeach
        </table>
      </div>
      <div style="display: flex; margin-top: 10px; font-size: 11px;">
        <div style="flex: 1;">Barang: <label id="receipt_total_items">{{ $transaction->total_items }}</label></div>
        <div style="flex: 1;">Kuantiti: <label id="receipt_total_quantity">{{ $transaction->total_quantity }}</label></div>
      </div>
      <div style="border: 1px dashed #000; margin: 5px 0;"></div>
      <div style="margin-bottom: 10px;">
        <div style="width: 100%; font-size: 11px; display: flex; justify-content: space-between;">
          <div>Jenis Bayaran</div>
          <div id="receipt_payment_type">
            @if($transaction->payment_type_text == "Card")
              Kredit Card
            @else
              {{ $transaction->payment_type_text }}
            @endif
          </div>
        </div>
        @if($transaction->total_discount > 0)
          <div id="receipt_voucher">
            <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
              <div>Jumlah Harga Asal</div>
              <div id="receipt_ori_payment">{{ $transaction->subtotal_text }}</div>
            </div>
            <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
              <div id="receipt_voucher_name">{{ $transaction->voucher_name }}</div>
              <div id="receipt_discount">{{ $transaction->total_discount_text }}</div>
            </div>
          </div>
        @endif
        <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
          <div>Jumlah Bil</div>
          <div id="receipt_total">{{ $transaction->total_text }}</div>
        </div>
        @if($transaction->payment_type == "cash")
          <div id="receipt_cash">
            <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
              <div>Jumlah Bayaran</div>
              <div id="receipt_received_payment">
                {{ $transaction->payment_text }}
              </div>
            </div>
            <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
              <div>Jumlah Baki</div>
              <div id="receipt_change">
                RM {{ $transaction->balance_text }}
              </div>
            </div>
          </div>
        @else
          <div id="receipt_other_payment" style="width: 100%; display: flex; justify-content: space-between; font-size: 11px; font-weight: bold;">
            <div>{{ $transaction->payment_type_text }}</div><div>RM {{ $transaction->total_text }}</div>
          </div>
        @endif
      </div>
      <div style="border: 1px dashed #000; margin: 10px 0;"></div>
      <div>
        <div style="text-align: center; font-size: 9px;">TERIMA KASIH KERANA MEMBELI-BELAH DENGAN KAMI</div>
        <div style="text-align: center; font-size: 9px;">BARANG YANG DIJUAL TIDAK DAPAT DIKEMBALIKAN</div>

        <div style="font-size: 11px;">
          <div style="display: inline-block;" id="receipt_date">{{ $transaction->receipt_date }}</div>
          <div style="display: inline-block; float: right;" id="receipt_time">{{ $transaction->receipt_time }}</div> 
        </div>

        <div style="display: block;font-size: 11px;" id="receipt_by">
          Juruwang counter : {{ $transaction->cashier_name }} <br>
          Juruwang : {{ $transaction->user_name }}
        </div>

        <div style="font-size: 8px; text-align: center;" id="receipt_transaction_no_box">
          <div style="display: inline-block;">INVOIS : <label id="receipt_transaction_no"> {{ $transaction->transaction_no }}</label></div>
        </div>

        @if($reprint == 1)
          <div style="text-align: center; font-size: 9px;" id="receipt_reprint">
            <div style="font-weight: bold;">... CETAK SEMULA SALINAN ...</div>
            <div id="reprint_date_time">{{ date('Y-m-d H:i:s') }}</div>
          </div>
        @endif
      </div>
    </div>
  </div>
  <!-- end print receipt -->

  <script>
    window.close();
  </script>

</body>
</html>

