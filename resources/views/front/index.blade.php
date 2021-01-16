<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pos System</title>
</head>

<link rel="stylesheet" type="text/css" href="{{ asset('assets/bootstrap-4.3.1-dist/css/bootstrap.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/front.css') }}">
<!-- Fontawesome -->
<script src="https://kit.fontawesome.com/e5dc55166e.js" crossorigin="anonymous"></script>

<!-- jQuery -->
<script src="{{ asset('assets/jquery/jquery-3.5.1.min.js') }}"></script>

<!-- bootstrap -->
<script src="{{ asset('assets/bootstrap-4.3.1-dist/js/bootstrap.min.js') }}"></script>

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
                    <tr>
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
            <div class="summary-detail">
              <label>Price</label>
              <div id="price">RM {{ number_format($subtotal, 2) }}</div>
            </div>

            <div class="summary-detail">
              <label>Discount</label>
              <div id="discount">RM {{ number_format($discount, 2) }}</div>
            </div>

            <div class="summary-detail bold" style="margin-bottom: 30px;">
              <label>Total</label>
              <div id="total">RM {{ number_format($total, 2) }}</div>
            </div>

            <div class="summary-detail">
              <label>Payment</label>
              <div id="payment">RM {{ number_format($payment, 2) }}</div>
            </div>

            <div class="summary-detail bold">
              <label>Balance</label>
              <div id="balance">RM {{ number_format($balance, 2) }}</div>
            </div>
            
          </div>
        </div>
      </div>
      <div class="col-lg-7 col-sm-12" style="position: relative; padding-bottom: 150px;">
        <input type="text" class="form-control" placeholder="Bar Code Scanner & Product Code" id="barcode" />
        <div class="login-info">
          <span style="margin-right: 20px;">24 - December 2020</span>
          <span>12:15:18 PM</span>
          <div style="float: right;">
            <i class="fas fa-user" style="font-size: 20px; color: #999;"></i>
            <span>{{ $user->name }}</span>
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
                <div class="btn btn-dark">Previous Receipt</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Other</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Cash Checkout</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Card Payment</div>
              </div>

              <div class="col-4">
                <div class="btn btn-dark">Clear</div>
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


</body>

<script>
  
  $(document).ready(function(){

    $("#barcode").on('keypress', function(e){
      // enter
      if(e.which == 13)
      {
        searchAndAddItem();
      }
    })
  });

  function searchAndAddItem()
  {
    $(".toast").toast('hide');

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

        $("#added_item_title").html(result.title);
        $("#added_item_content").html("Product "+product.product_name+" is successfully added");

        $("#added_item_toast").toast('show');

        generateItemList(transaction_summary);
      }
    });
  }

  function cancelItem(item_id)
  {
    console.log(item_id);
  }

  function generateItemList(transaction_summary)
  {
    let html = "";
    for(var a = 0; a < transaction_summary.items_list.length; a++)
    {
      let item_detail = transaction_summary.items_list[a];
      html += "<tr>";
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
    $("#payment").html("RM "+transaction_summary.payment);
    $("#balance").html("RM "+transaction_summary.balance);
  }

</script>


</html>
