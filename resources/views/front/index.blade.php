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

<!-- Fontawesome -->
<script src="https://kit.fontawesome.com/e5dc55166e.js" crossorigin="anonymous"></script>

<!-- jQuery -->
<script src="{{ asset('assets/jquery/jquery-3.5.1.min.js') }}"></script>

<!-- bootstrap -->
<script src="{{ asset('assets/bootstrap-4.3.1-dist/js/bootstrap.bundle.min.js') }}"></script>
<!-- datatables -->
<script src="{{ asset('assets/datatables/datatables.min.js') }}"></script>

<body>
  
  <div class="" style="position: absolute; background: #F2F2F2; padding: 20px; width: 100%; height: 100%;">
    <div class="row" style="height: 100%;">
      <div class="col-lg-5 col-sm-12">
        <div class="left-box">
          <div class="items-list">
            <table id="items-table">
              <thead>
                <th>Product Name</th>
                <th width="100px">Quantity</th>
                <th width="200px">Amount</th>
                <th width="50px;"></th>
              </thead>
              <tbody>
                @if($pending_transaction)
                  @foreach($pending_transaction->items_list as $item)
                    <tr item_id="{{ $item->id }}">
                      <td>{{ $item->product_name }}</td>
                      <td>X {{ $item->quantity }}</td>
                      <td>RM {{ number_format($item->subtotal, 2) }}</td>
                      <td>
                        <button class="btn btn-dark items-cancel" onclick="cancelItem('{{ $item->id }}')">Cancel</button>
                      </td>
                    </tr>
                  @endforeach
                @endif
                
              </tbody>
            </table>
          </div>
          <div class="items-summary">
            <input type="hidden" name="transaction_id" id="transaction_id" value="{{ $transaction_id }}" />
            <div class="summary-detail">
              <label>Price</label>
              <div id="price">RM {{ number_format($subtotal, 2) }}</div>
            </div>

            <div class="summary-detail">
              <label>Discount</label>
              <div id="discount">RM {{ number_format($discount, 2) }}</div>
            </div>

            <div class="summary-detail bold">
              <label>Total</label>
              <div id="total">RM {{ number_format($total, 2) }}</div>
            </div>

            <!-- <div class="summary-detail">
              <label>Payment</label>
              <div id="payment">RM {{ number_format($payment, 2) }}</div>
            </div>

            <div class="summary-detail bold">
              <label>Balance</label>
              <div id="balance">RM {{ number_format($balance, 2) }}</div>
            </div> -->
            
          </div>
        </div>
      </div>
      <div class="col-lg-7 col-sm-12" style="position: relative; padding-bottom: 150px;">
        <input type="text" class="form-control" placeholder="Bar Code Scanner & Product Code" id="barcode" />
        <div class="login-info">
          <span style="margin-right: 20px;">24 - December 2020</span>
          <span>12:15:18 PM</span>
          <!-- <div style="float: right;">
            <i class="fas fa-user" style="font-size: 20px; color: #999;"></i>
            <span>{{ $user->name }}</span>
          </div> -->

          <div class="dropdown" style="float: right;">
            <button type="button" class="user_dropdown dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user" style="font-size: 20px; color: #999;"></i>
              <span>{{ $user->name }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item" href="#" onclick="logout()">Logout</a>
            </div>
          </div>

        </div>

        <div class="memo">
          <div class="memo-title">Notification</div>
          <div class="memo-content">
            <p>Testing memo</p>
          </div>
        </div>

        <div class="pos-btn">
          <div class="col-12">
            <div class="row">
              <div class="col-4">
                <div class="btn btn-dark">Voucher</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark" id="show_previous_receipt">Previous Receipt</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Other</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark" id="cashCheckout">Cash Checkout</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Card Payment</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark" onclick="clearTransaction()">Clear</div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div id="index_toast" aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
        <!-- Position it -->
        <div style="position: absolute; top: 0; right: 0;">

          <div id="search_error_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon error"></div>
              <strong class="mr-auto" id="search_error_title">Product not found</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="search_error_content">
            </div>
          </div>

          <div id="added_item_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon success"></div>
              <strong class="mr-auto" id="added_item_title">Product added</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="added_item_content">
            </div>
          </div>
          
        </div>
      </div>

    </div>

  </div>

  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Delete item?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure to delete this item ?
        </div>
        <div class="modal-footer">
          <input type="hidden" name="item_id" id="delete_item_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" id="deleteSubmit">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="numpadModal" tabindex="-1" role="dialog" aria-labelledby="numpadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="numpadModalLabel">Cash received</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="numpad">
            <div class="numpad_input">
              <input type="text" class="form-control" name="received_payment" value="0" /> 
            </div>
            <div class="numpad_func_btn">
              <div class="numpad_btn clear">Clear</div>
              <div class="numpad_btn decrease"><</div>
            </div>
            <div class="numpad_enter">
              <div class="numpad_number">
                <div class="numpad_number_group">
                  <div class='numpad_number_btn' type='1' number="7">7</div>
                  <div class='numpad_number_btn' type='1' number="8">8</div>
                  <div class='numpad_number_btn' type='1' number="9">9</div>
                </div>
                <div class="numpad_number_group">
                  <div class='numpad_number_btn' type='1' number="4">4</div>
                  <div class='numpad_number_btn' type='1' number="5">5</div>
                  <div class='numpad_number_btn' type='1' number="6">6</div>
                </div>
                <div class="numpad_number_group">
                  <div class='numpad_number_btn' type='1' number="1">1</div>
                  <div class='numpad_number_btn' type='1' number="2">2</div>
                  <div class='numpad_number_btn' type='1' number="3">3</div>
                </div>
                <div class="numpad_number_group">
                  <div class='numpad_number_btn' type='1' number="0" style="flex:2;">0</div>
                  <div class='numpad_number_btn' type='1' number=".">.</div>
                </div>
              </div>
              <div class="numpad_preset">
                <div class='numpad_number_btn' type='2' number="100">100</div>
                <div class='numpad_number_btn' type='2' number="50">50</div>
                <div class='numpad_number_btn' type='2' number="10">10</div>
                <div class='numpad_number_btn' type='2' number="5">5</div>
              </div>
            </div>
            <div class="numpad_btn_box">
              <button type="button" class="numpad_btn red exit" data-dismiss="modal">Exit</button>
              <button type="button" class="numpad_btn green submit">Submit</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="completedTransactionModal" tabindex="-1" role="dialog" aria-labelledby="completedTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="completedTransactionModalLabel">Transaction completed</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="transaction_completed_icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h4 style="text-align: center;">Transaction completed.</h4>
          <h5 style="text-align: center;">Balance : RM <span id="transaction_balance"></span></h5>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>

  <div id="previous_receipt" class="full_page">
    <div class="close_full_page">
      <i class="fas fa-times"></i>
    </div>
    <h4 class="title">
      Previous receipt
    </h4>
    <div class="content">
      <div class="row">
        <div class="col-12">
          <table id="previous_receipt_table" class="table table-bordered table-striped" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>Payment type</th>
                <th>Total</th>
                <th>Received payment</th>
                <th>Balance</th>
                <th>Void</th>
                <th>Transaction date</th>
              </tr>
            </thead>
            <tbody>
              @foreach($completed_transaction as $completed)
                <tr>
                  <td>{{ $completed->payment_type }}</td>
                  <td>{{ number_format($completed->total, 2) }}</td>
                  <td>{{ number_format($completed->payment, 2) }}</td>
                  <td>{{ number_format($completed->balance, 2) }}</td>
                  <td>
                    <div class="void_column" transaction_id="{{ $completed->id }}">
                      @if($completed->void == 1)
                        <span class="void">Voided by {{ $completed->void_by_name }}</span>
                        <br>
                        <button type="button" class="btn btn-secondary" onclick="undoVoidTransaction('{{ $completed->id }}')">Undo</button>
                      @else
                        <button type="button" class="btn btn-danger" onclick="voidTransaction('{{ $completed->id }}')">Void</button>
                      @endif
                    </div>
                  </td>
                  <td data-order="{{ $completed->transaction_date }}">{{ date('d M Y g:i:s A', strtotime($completed->transaction_date)) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-labelledby="voidModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="voidModalLabel">Void transaction ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure to void this transaction ?
        </div>
        <div class="modal-footer">
          <input type="hidden" id="void_transaction_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" onclick="voidSubmit()">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="unvoidModal" tabindex="-1" role="dialog" aria-labelledby="unvoidModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="unvoidModalLabel">Un-void transaction ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure to un-void this transaction ?
        </div>
        <div class="modal-footer">
          <input type="hidden" id="unvoid_transaction_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" onclick="unvoidSubmit()">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="clearItemsModal" tabindex="-1" role="dialog" aria-labelledby="clearItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="clearItemsModalLabel">Clear transaction ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure to clear this transaction ?
        </div>
        <div class="modal-footer">
          <input type="hidden" id="clear_transaction_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" onclick="submitClearTransaction(1)">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <form id="logout_form" method="POST" action="{{ route('logout') }}">
    @csrf
  </form>


</body>

<script>
  
  var transaction_total = "{{ $real_total }}";

  var previous_receipt_table = $("#previous_receipt_table").DataTable( {
    responsive: true,
    order: [[ 5, "desc" ]]
  });

  $(document).ready(function(){
    $("#barcode").on('keydown', function(e){
      // enter
      // delay 100ms because keydown will not capture on change
      if(e.which == 13)
      {
        setTimeout(searchAndAddItem, 50);
      }else if(e.which != 17){
        setTimeout(searchAndAddItem, 50);
      }
    });

    $("#deleteSubmit").click(function(){
      var item_id = $("#delete_item_id").val();
      submitDeleteItem(item_id);
    });

    $("#cashCheckout").click(function(){
      showNumPad();
    });

    $(".numpad_number_btn").click(function(){
      var type = $(this).attr("type");
      var number = $(this).attr("number");
      // type 1 = 0 to 9
      // type 2 = 100, 50, 10, 5

      var received_number = $("input[name='received_payment']").val();
      if(type == 1)
      {
        if(received_number > 0)
        {
          var added_number = received_number + number;
          $("input[name='received_payment']").val(added_number);
        }
        else
        {
          $("input[name='received_payment']").val(number);
        }
      }
      else if(type == 2)
      {
        number += ".00";
        $("input[name='received_payment']").val(number);
      }
    });

    $(".numpad_btn.decrease").click(function(){
      var received_number = $("input[name='received_payment']").val();
      if(received_number > 0)
      {
        var edited_number = received_number.slice(0, -1);
        if(edited_number.length == 0)
        {
          edited_number = 0;
        }
        $("input[name='received_payment']").val(edited_number);
      }
    });

    $(".numpad_btn.clear, .numpad_btn.exit").click(function(){
      $("input[name='received_payment']").val(0);
    });

    $(".numpad_btn.submit").click(function(){
      submitCashPayment();
    });

    $("#show_previous_receipt").click(function(){
      $("#previous_receipt").show();
    });

    $(".close_full_page").click(function(){
      $(".full_page").hide();
    });

  });

  function searchAndAddItem()
  {
    $(".toast").toast('hide');
    $("#barcode").focus();

    let barcode = $("#barcode").val();

    $.post("{{ route('searchAndAddItem') }}", { "_token" : "{{ csrf_token() }}", "barcode" : barcode }, function(result){
      if(result.error == 1)
      {
        $("#search_error_title").html(result.title);
        $("#search_error_content").html(result.message);

        $("#search_error_toast").toast('show');
      }
      else if(result.error == 0)
      {
        let transaction_summary = result.transaction_summary;
        let product = result.product;

        transaction_total = transaction_summary.real_total;
        $("#transaction_id").val(transaction_summary.transaction_id);

        $("#added_item_title").html(result.title);
        $("#added_item_content").html("Product "+product.product_name+" is successfully added");

        $("#added_item_toast").toast('show');

        generateItemList(transaction_summary);
      }
      $("#barcode").val('');
    });
  }

  function cancelItem(item_id)
  {
    $("#delete_item_id").val(item_id);
    $("#deleteModal").modal('show');
  }

  function generateItemList(transaction_summary)
  {
    let html = "";
    for(var a = 0; a < transaction_summary.items_list.length; a++)
    {
      let item_detail = transaction_summary.items_list[a];
      html += "<tr item_id="+item_detail.id+">";
      html += "<td>"+item_detail.product_name+"</td>";
      html += "<td>X "+item_detail.quantity+"</td>";
      html += "<td>RM "+item_detail.subtotal_text+"</td>";
      html += "<td>";
      html += "<button class='btn btn-dark items-cancel' onclick='cancelItem(\""+item_detail.id+"\")'>Cancel</button>";
      html += "</td>";
      html += "</tr>";
    }

    $("#items-table tbody").html(html);
    $("#price").html("RM "+transaction_summary.subtotal);
    $("#discount").html("RM "+transaction_summary.discount);
    $("#total").html("RM "+transaction_summary.total);
    // $("#payment").html("RM "+transaction_summary.payment);
    // $("#balance").html("RM "+transaction_summary.balance);
  }

  function submitDeleteItem(item_id)
  {
    $.post("{{ route('submitDeleteItem') }}", { "_token" : "{{ csrf_token() }}", "item_id" : item_id }, function(result){
      if(result.error == 0)
      {
        $("#deleteModal").modal('hide');
        $("#items-table tbody tr[item_id="+item_id+"]").remove();
      }
      else
      {
        console.log("something wrong");
      }
    });
  }

  function showNumPad()
  {
    $("#numpadModal").modal('show');
  }

  function submitCashPayment()
  {
    var received_cash = $("input[name='received_payment']").val();

    if(received_cash <= 0)
    {
      alert("Cannot submit as RM 0.00");
      return;
    }

    if(transaction_total <= 0)
    {
      alert("Transaction total is RM 0.00");
      return;
    }

    if(received_cash < transaction_total)
    {
      alert("Received cash is lesser than transaction price");
      return;
    }

    var transaction_id = $("#transaction_id").val();

    $.post("{{ route('submitTransaction') }}", {"_token" : "{{ csrf_token() }}", "received_cash" : received_cash, "transaction_id" : transaction_id}, function(result){

      $("#numpadModal").modal('hide');
      if(result.error == 0)
      {
        $("#completedTransactionModal").modal('show');
        $("#transaction_balance").html(result.balance);

        submitClearTransaction(0);
        prependCompletedTransaction(result.completed_transaction);
      }
      else
      {
        alert("Error");
      }

    });
  }

  function submitClearTransaction(type)
  {
    // type
    // 0 = clear transaction at front only
    // 1 = clear transaction at database too

    if(type == 1)
    {
      var transaction_id = $("#transaction_id").val();

      console.log(transaction_id);

      if(transaction_id)
      {
        $.post("{{ route('clearTransaction') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
          if(result.error == 0)
          {
            $("#items-table tbody").html("");
            $("#price, #discount, #total").html("RM 0.00");

            $("#clearItemsModal").modal('hide');
          }
        });
      }
      else
      {
        $("#clearItemsModal").modal('hide');
      }
    }
    else if(type == 0)
    {
      $("#items-table tbody").html("");
      $("#price, #discount, #total").html("RM 0.00");
    }
  }

  function prependCompletedTransaction(completed_transaction)
  {
    var void_html = "<div class='void_column' transaction_id="+completed_transaction.id+">";
    void_html += "<button type='button' class='btn btn-danger' onclick='voidTransaction(\""+completed_transaction.id+"\")'>Void</button>";
    void_html += "</div>";

    var data = "<tr>";
    data += "<td>"+completed_transaction.payment_type+"</td>";
    data += "<td>"+completed_transaction.total_text+"</td>";
    data += "<td>"+completed_transaction.payment_text+"</td>";
    data += "<td>"+completed_transaction.balance_text+"</td>";
    data += "<td>"+void_html+"</td>";
    data += "<td data-order='"+completed_transaction.transaction_date+"'>"+completed_transaction.transaction_date_text+"</td>";
    data += "</tr>";

    previous_receipt_table.row.add($(data)).node();

    previous_receipt_table.draw();
    previous_receipt_table.responsive.recalc();
  }

  function voidTransaction(transaction_id)
  {
    $("#void_transaction_id").val(transaction_id);
    $("#voidModal").modal('show');
  }

  function undoVoidTransaction(transaction_id)
  {
    $("#unvoid_transaction_id").val(transaction_id);
    $("#unvoidModal").modal('show');
  }

  function voidSubmit()
  {
    var transaction_id = $("#void_transaction_id").val();
    $.post("{{ route('submitVoidTransaction') }}", {"_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
      if(result.error == 0)
      {
        var html = "<span class='void'>Voided by "+result.void_by_name+"</span>";
        html += "<br>";
        html += "<button type='button' class='btn btn-secondary' onclick='undoVoidTransaction(\""+transaction_id+"\")'>Undo</button>";
      
        $(".void_column[transaction_id="+transaction_id+"]").html(html);
        $("#voidModal").modal('hide');
      }
    })
  }

  function unvoidSubmit()
  {
    var transaction_id = $("#unvoid_transaction_id").val();
    $.post("{{ route('submitUnvoidTransaction') }}", {"_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
      if(result.error == 0)
      {
        var html = "<button type='button' class='btn btn-danger' onclick='voidTransaction(\""+transaction_id+"\")'>Void</button>";
                      
        $(".void_column[transaction_id="+transaction_id+"]").html(html);
        $("#unvoidModal").modal('hide');
      }
    })
  }

  function clearTransaction()
  {
    $("#clearItemsModal").modal('show');
  }

  function logout()
  {
    $("#logout_form").submit();
  }

</script>


</html>
