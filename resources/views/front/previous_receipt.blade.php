<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pos System</title>
</head>

<link rel="stylesheet" type="text/css" href="{{ asset('assets/bootstrap-4.3.1-dist/css/bootstrap.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/front.css') }}">
<!-- datatables -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/datatables/datatables.min.css') }}">
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset('assets/iCheck/all.css') }}">
<link rel="stylesheet" href="{{ asset('assets/iCheck/square/blue.css') }}">
<link rel="stylesheet" href="{{ asset('assets/sweetAlert2/sweetalert2.css') }}">

<!-- Fontawesome -->
<script src="https://kit.fontawesome.com/e5dc55166e.js" crossorigin="anonymous"></script>

<!-- jQuery -->
<script src="{{ asset('assets/jquery/jquery-3.5.1.min.js') }}"></script>

<!-- bootstrap -->
<script src="{{ asset('assets/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js') }}"></script>
<!-- datatables -->
<script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>
<!-- iCheck 1.0.1 -->
<script src="{{ asset('assets/iCheck/icheck.min.js') }}"></script>
<!-- sweet alert 2 -->
<script src="{{ asset('assets/sweetAlert2/sweetalert2.js') }}"></script>

<body>
  <div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 3; background: #fff; padding: 20px;">
    <div class="container-fluid" style="background: #fff; margin-top: 20px; box-shadow: 0px 1px 5px 2px #ccc; padding: 15px;">
      <h4 style="text-align: center;">Previous Receipt</h4>
      <div class="row" style="border-top: 1px solid #ccc; padding-top: 10px;">
        <div class="col-12">
          <form method="GET" action="{{ route('serverPreviousReceipt') }}">
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label>Date From</label>
                  <input type="date" class="form-control" name="date_from" value="{{ date('Y-m-d', strtotime($date_from)) }}" />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label>Date To</label>
                  <input type="date" class="form-control" name="date_to" value="{{ date('Y-m-d', strtotime($date_to)) }}" />
                </div>
              </div>
            </div>

            <button class="btn btn-primary" type="submit">Submit</button>
          </form>
        </div>

        <div class="col-12" style="margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px;">
          <table id="previous_receipt_table" class="table table-bordered table-striped" cellspacing="0" width="100%" style="width: 100% !important;">
            <thead style="width: 100% !important;">
              <tr>
                <th>Cashier</th>
                <th>Transaction No</th>
                <th>Payment type</th>
                <th>Reference No</th>
                <th>Total</th>
                <th>Received payment</th>
                <th>Balance</th>
                <!-- <th>Void</th> -->
                <th>Transaction date</th>
                <th>Print</th>
              </tr>
            </thead>
            <tbody>
              @foreach($completed_transaction as $completed)
                <tr>
                  <td>
                    @if($completed->cashier_name)
                      {{ $completed->cashier_name }}
                    @else
                      {{ $completed->ip }}
                    @endif
                  </td>
                  <td>{{ $completed->transaction_no }}</td>
                  <td>{{ $completed->payment_type_text }}</td>
                  <td>{{ $completed->reference_no }}</td>
                  <td>RM {{ number_format($completed->total, 2) }}</td>
                  <td>RM {{ number_format($completed->payment, 2) }}</td>
                  <td>RM {{ number_format($completed->balance, 2) }}</td>
                  <td data-order="{{ $completed->transaction_date }}">{{ date('d M Y g:i:s A', strtotime($completed->transaction_date)) }}</td>
                  <td>
                    <button class="btn btn-success" onclick="printReceipt('{{ $completed->id }}', 1)">Print Receipt</button>
                    <br>
                    <a class="btn btn-primary" target="_blank" href="{{ route('getInvoice', ['transaction_id' => $completed->id ]) }}" style="margin-top: 10px;">Print Invoice</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- print receipt -->
  <div id="receipt">
    <div>
      <div style="display: flex; flex-direction: column; text-align: center;">
        <label style="font-size: 11px;">HOME U(M) SDN BHD (125272-P)</label>
        <label style="font-size: 11px;">{{ $contact_number }}</label>
        <label style="font-size: 11px;">{!! nl2br(e($branch_address)) !!}</label>
        <label style="font-size: 11px;">011-1780 9623</label>
        <!-- <label>RESIT</label> -->
        <label id="refund_title" style="display: none;">( Refund )</label>
        <label id="refund_transaction_no" style="display: none;"></label>
        <br/>
      </div>
      <div style="border: 2px dashed #000; height: 2px;"></div>
      <div id="receipt_items">
        
      </div>
      <div style="display: flex; margin-top: 10px; font-size: 11px;">
        <div style="flex: 1;">Barang: <label id="receipt_total_items"></label></div>
        <div style="flex: 1;">Kuantiti: <label id="receipt_total_quantity"></label></div>
      </div>
      <div style="border: 1px dashed #000; margin: 5px 0;"></div>
      <div style="margin-bottom: 10px;">
        <div style="width: 100%; font-size: 11px; display: flex; justify-content: space-between;">
          <div>Jenis Bayaran</div>
          <div id="receipt_payment_type"></div>
        </div>
        <div id="receipt_voucher" style="display: none;">
          <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
            <div>Jumlah Harga Asal</div>
            <div id="receipt_ori_payment"></div>
          </div>
          <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
            <div id="receipt_voucher_name"></div>
            <div id="receipt_discount"></div>
          </div>
        </div>
        <!-- receipt round off -->
        <!-- <div id="receipt_round_off_box" style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
          <div>Round off</div>
          <div id="receipt_round_off"></div>
        </div> -->
        <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
          <div>Jumlah Bil</div>
          <div id="receipt_total"></div>
        </div>
        <div id="receipt_cash" style="display: none;">
          <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
            <div>Jumlah Bayaran</div>
            <div id="receipt_received_payment"></div>
          </div>
          <div style="width: 100%; font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
            <div>Jumlah Baki</div>
            <div id="receipt_change"></div>
          </div>
        </div>
        <div id="receipt_other_payment" style="width: 100%; display: flex; justify-content: space-between; font-size: 11px; font-weight: bold;"></div>
      </div>
      <div style="border: 1px dashed #000; margin: 10px 0;"></div>
      <div>
        <div style="text-align: center; font-size: 9px;">TERIMA KASIH KERANA MEMBELI-BELAH DENGAN KAMI</div>
        <div style="text-align: center; font-size: 9px;">BARANG YANG DIJUAL TIDAK DAPAT DIKEMBALIKAN</div>

        <div style="font-size: 11px;">
          <div style="display: inline-block;" id="receipt_date"></div>
          <div style="display: inline-block; float: right;" id="receipt_time"></div> 
        </div>

        <div style="display: block;font-size: 11px;" id="receipt_by"></div>

        <div style="font-size: 8px; text-align: center;" id="receipt_transaction_no_box">
          <div style="display: inline-block;">INVOIS : <label id="receipt_transaction_no"></label></div>
        </div>

        <!-- <div>
          <label>Juruwang : <label id="receipt_completed_by"></label> </label>
          <div style="display: flex; justify-content: space-between;">
            <div id="receipt_completed_by_2"></div>
            <div>INVOIS : <label id="receipt_transaction_no"></label></div>
          </div>
        </div> -->

        <div style="text-align: center; font-size: 9px;" id="receipt_reprint">
          <div style="font-weight: bold;">... CETAK SEMULA SALINAN ...</div>
          <div id="reprint_date_time"></div>
        </div>
      </div>
    </div>
  </div>
  <!-- end print receipt -->
</body>

<script>
  
  $(document).ready(function(){

    var previous_receipt_table = $("#previous_receipt_table").DataTable( {
      // responsive: true,
      order: [[ 7, "desc" ]]
    });

  });

  function printReceipt(transaction_id, reprint)
  {
    $.post("{{ route('getTransactionDetail') }}", {"_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id }, function(result){
      if(result.error == 0)
      {
        let transaction = result.transaction;
        let transaction_detail = result.transaction_detail;
        let items_html = "";
        items_html += "<table style='font-size: 11px;width:100%;border-spacing:0px 1px;'>";
        for(var a = 0; a < transaction_detail.length; a++)
        {
          items_html += "<tr>";
          items_html += "<td style='vertical-align:top;' colspan='3'>";
          items_html += transaction_detail[a].product_name;
          if(transaction_detail[a].measurement_type == "kilogram")
          {
            items_html += " ( "+transaction_detail[a].measurement+"KG )";
          }
          else if(transaction_detail[a].measurement_type == "meter")
          {
            items_html += " ( "+transaction_detail[a].measurement+"Meter )";
          }

          items_html += "</td>";
          items_html += "</tr>";
          items_html += "<tr>";
          items_html += "<td style='vertical-align:top;'>"+transaction_detail[a].barcode+"</td>";
          items_html += "<td style='width: 120px;vertical-align:top;text-align:right;'>";

          if(transaction_detail[a].quantity > 0)
          {
            items_html += transaction_detail[a].measurement_quantity+" X RM "+transaction_detail[a].price_text;

            items_html += "</td>";
            items_html += "<td style='width:70px;text-align:right;vertical-align:top;'>RM "+transaction_detail[a].original_price+"</td>";
            items_html += "</tr>";

            if(transaction_detail[a].wholesale_price && parseFloat(transaction_detail[a].wholesale_price).toFixed(2) != parseFloat(transaction_detail[a].price).toFixed(2))
            {
              items_html += "<tr>";
              items_html += "<td></td><td style='width:70px;text-align:right;vertical-align:top;'>"+transaction_detail[a].measurement_quantity+" X RM "+transaction_detail[a].wholesale_price_text+"</td>";
              items_html += "<td style='width:70px;text-align:right;vertical-align:top;'>- RM "+transaction_detail[a].diff+"</td>";
              items_html += "</tr>";
            }
          }
        }

        items_html += "</table>";
        
        $("#receipt_items").html(items_html);

        var payment_type_text = transaction.payment_type_text;
        if(payment_type_text == "Card")
        {
          payment_type_text = "Kredit Kad";
        }

        $("#receipt_total_quantity").html(transaction.total_quantity);
        $("#receipt_total_items").html(transaction.total_items);
        $("#receipt_total").html("RM "+transaction.total_text);
        $("#receipt_payment_type").html(payment_type_text);
        $("#refund_title, #refund_transaction_no").hide();
        $("#receipt_transaction_no_box").show();

        $("#receipt_voucher").hide();
        $("#receipt_ori_payment").html("");
        $("#receipt_voucher_name").html("");
        $("#receipt_discount").html("");

        $("#receipt_round_off_box").hide();
        $("#receipt_round_off").html("");

        if(transaction.payment_type != "cash")
        {
          $("#receipt_other_payment").show();
          $("#receipt_other_payment").html("<div>"+payment_type_text+"</div><div>RM "+transaction.total_text+"</div>");
          $("#receipt_cash").hide();
        }
        else
        {
          if(transaction.round_off)
          {
            $("#receipt_round_off_box").show();
            $("#receipt_round_off").html("RM "+transaction.round_off);
          }
          $("#receipt_other_payment").hide();
          $("#receipt_cash").show();
          $("#receipt_received_payment").html("RM "+transaction.payment_text);
          $("#receipt_change").html("RM "+transaction.balance_text);
        }

        if(transaction.total_discount > 0)
        {
          $("#receipt_voucher").show();
          $("#receipt_ori_payment").html("RM "+transaction.subtotal_text);
          $("#receipt_voucher_name").html("Diskaun : ( "+transaction.voucher_name+" )");
          $("#receipt_discount").html("RM "+transaction.total_discount_text);
        }

        $("#receipt_date").html(transaction.receipt_date);
        $("#receipt_time").html("Time : "+transaction.receipt_time);
        $("#receipt_by").html("Juruwang counter : "+transaction.cashier_name+"<br>Juruwang : "+transaction.user_name);

        $("#receipt_completed_by, #receipt_completed_by_2").html(transaction.completed_by_name);
        $("#receipt_transaction_no").html(transaction.transaction_no);

        if(reprint == 1)
        {
          var d = new Date();
          var day = d.getDate();
          var month = d.getMonth();
          var year = d.getFullYear();

          var hour = d.getHours();
          var minute = d.getMinutes();

          if(day < 10)
          {
            day = "0"+day;
          }

          if(month < 10)
          {
            month = "0"+month;
          }

          $("#reprint_date_time").html(day+"-"+month+"-"+year+" "+hour+":"+minute);
          $("#receipt_reprint").show();
        }
        else
        {
          $("#receipt_reprint").hide();
        }

        var receiptPrint = document.getElementById('receipt');
        var newWin = window.open('','Print-Window');

        newWin.document.open();
        newWin.document.write('<html><body onload="window.print()">'+receiptPrint.innerHTML+'</body></html>');
        newWin.document.close();

        setTimeout(function(){newWin.close();},100);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

</script>

</html>