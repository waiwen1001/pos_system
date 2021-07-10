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
<link rel="stylesheet" href="{{ asset('assets/boostrap-toggle/bootstrap-toggle.min.css') }}" rel="stylesheet">

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
<script src="{{ asset('assets/boostrap-toggle/bootstrap-toggle.min.js') }}"></script>

<body>
  
  <div class="" style="position: absolute; background: #F2F2F2; padding: 20px; width: 100%; height: 100%;">
    <div class="row" style="height: 100%;">
      <div class="col-lg-5 col-sm-12" style="max-height: 100%;">
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
                      <td>
                        <div class="quantity">
                          <i class="fa fa-minus" onclick="editQuantity(this, 'plus', '{{ $item->id }}')"></i>
                          <label>{{ $item->quantity + $item->wholesale_quantity }}</label>
                          <i class="fa fa-plus" onclick="editQuantity(this, 'minus', '{{ $item->id }}')"></i>
                        </div>
                      </td>
                      <td class="subtotal">
                        @if($item->wholesale_quantity > 0)
                          <span style="color:#9c27b0;">RM {{ number_format( ($item->wholesale_quantity * $item->wholesale_price), 2) }}</span>
                          <br>
                        @endif

                        @if($item->quantity > 0)
                          RM {{ number_format( ($item->quantity * $item->price ), 2) }}
                        @endif
                      </td>
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

            <div class="summary-detail" style="display: {{ $item_quantity == 0 ? 'none' : '' }};">
              <label>Total Quantity</label>
              <div class="summary_price" id="total_quantity">{{ $item_quantity }}</div>
            </div>

            <div class="summary-detail">
              <label>Price</label>
              <div>RM</div>
              <div class="summary_price" id="price">{{ $subtotal }}</div>
            </div>

            <div class="summary-detail">
              <label style="max-width: calc(50% - 16px); height: 30px;"><label class="discount_name" id="discount_name">{{ $voucher_name ? $voucher_name : 'Discount'}}</label> <i style="display: {{ $have_discount == 0 ? 'none' : '' }}; float: right; margin-right: 10px;" class="fa fa-trash remove_voucher" id="remove_voucher"></i></label>
              <div>RM</div>
              <div class="summary_price" id="discount">{{ $discount }}</div>
            </div>

            <div class="summary-detail" id="round_off_box" style="display: {{ $round_off == 0 ? 'none' : '' }};">
              <label>Round off</label>
              <div>RM</div>
              <div class="summary_price" id="round_off">{{ $round_off }}</div>
            </div>

            <div class="summary-detail bold">
              <label>Total</label>
              <div>RM</div>
              <div class="summary_price" id="total">{{ $total }}</div>
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
        <div class="bar_code_box">
          <input type="text" class="form-control" placeholder="Bar Code Scanner & Product Code" id="barcode" disabled />

          <div class="checkbox icheck" style="display: inline-block; margin-left: 10px;">
            <label>
              <input class="form-check-input" type="checkbox" name="barcode_manual" value="1" id="barcode_manual" /> Manual keyin
            </label>
          </div>

        </div>
        <div class="login-info">
          <span style="margin-right: 20px;">{{date("d-M-Y")}}</span>
          <span id="time">12:15:18 PM</span>
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
              <a class="dropdown-item" href="#" onclick="closing()">Logout</a>
            </div>
          </div>

        </div>

        <div class="memo">
          <!-- <div class="memo-title"></div> -->
          <div class="memo-content">
            <table style="width: 100%; text-align: left;">
              <thead>
                <th>Barcode</th>
                <th>Item name</th>
                <th>UOM</th>
                <th>Price</th>
              </thead>
              <tbody id="related_item">
              </tbody>
            </table>
          </div>
        </div>

        <div class="pos-btn">
          <div class="col-12">
            <div class="row">
              <div class="col-4">
                <button class="btn btn-dark" id="voucherBtn" onclick="showVoucher()">Voucher</button>
                <span class="shortcut_func_key" style="display: none;" func_name="showVoucher()"></span>
              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="previousReceiptBtn" onclick="showPreviousReceipt()">Previous Receipt</button>
                <span class="shortcut_func_key" style="display: none;" func_name="showPreviousReceipt()"></span>
              </div>

              <div class="col-4">
                <div class="dropup">
                  <button class="btn btn-dark dropdown-toggle" type="button" id="otherDropDownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Other
                  </button>
                  <span class="shortcut_func_key" style="display: none; left: -15px; top: -10px;" func_name="showOtherMenu()"></span>

                  <div class="dropdown-menu" aria-labelledby="otherDropDownBtn">

                    @if($device_type == 2)
                      <div id="barcode_toggle" class="dropdown-item">
                        <label style="white-space: pre-wrap; display: inline-block;">Barcode alert</label>
                        <input id="barcode_toggle_checkbox" type="checkbox" data-toggle="toggle" data-onstyle="success" data-height="20" style="display: inline-block;">
                      </div>
                      <div class="dropdown-divider"></div>

                      <button class="dropdown-item" id="openingBtn" {{ $opening == 0 ? '' : 'disabled' }}>
                        Opening
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showOpening()"></span>
                      </button>
                      <button class="dropdown-item" id="closingBtn" {{ $opening == 1 ? '' : 'disabled' }}>
                        Closing
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showClosing()"></span>
                      </button>
                    @endif

                    @if($user->user_type == 1 && $device_type == 1)
                      <button class="dropdown-item" id="dailyClosingBtn">
                        Daily Closing
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showDailyClosing()"></span>
                      </button>
                    @endif

                    @if($device_type == 2)
                      <div class="dropdown-divider"></div>
                      <button class="dropdown-item" id="floatInBtn" {{ $opening == 1 ? '' : 'disabled' }}>
                        Cash Float ( In )
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showCashFloatIn()"></span>
                      </button>
                      <button class="dropdown-item" id="floatOutBtn" {{ $opening == 1 ? '' : 'disabled' }}>
                        Cash Float ( Out )
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showCashFloatOut()"></span>
                      </button>
<!--                       <button class="dropdown-item" id="bagiKeKetuaBtn" {{ $opening == 1 ? '' : 'disabled' }}>
                        Bagi Ke Ketua
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showBagiKeKetua()"></span>
                      </button> -->
                      <!-- <button class="dropdown-item" id="refundBtn" {{ $opening == 1 ? '' : 'disabled' }}>
                        Refund
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showRefund()"></span>
                      </button> -->
                    @endif
                    @if($user->user_type == 1)
                      <div class="dropdown-divider"></div>
                      <button class="dropdown-item" onclick="dailyReport()">
                        Closing Report
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showClosingReport()"></span>
                      </button>
                      <!-- <button class="dropdown-item" onclick="serverCashFloatReport()">
                        Cash float Report
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showServerCashFloatReport()"></span>
                      </button> -->
                      @if($device_type == 1)
                        <div class="dropdown-divider"></div>
                        <!-- <button class="dropdown-item" onclick="showBranchProfile()">
                          Branch Profile
                          <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showBranchProfile()"></span>
                        </button> -->
                        <button class="dropdown-item" onclick="userManagement()">
                          User Management
                          <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showUserManagement()"></span>
                        </button>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item" onclick="syncTOHQ()">
                          Sync transaction to HQ
                          <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="SCsyncHQTransaction()"></span>
                        </button>
                        <button class="dropdown-item" onclick="syncProductList(0)">
                          Sync HQ product list
                          <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="SCsyncHQProductList()"></span>
                        </button>
                      @endif
                      <button class="dropdown-item" onclick="showKeySetup()">
                        Shortcut key setup
                        <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="showKeySetup()"></span>
                      </button>

                    @endif
                  </div>
                </div>

              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="cashCheckoutBtn" onclick="showCashCheckOut()">Cash Checkout</button>
                <span class="shortcut_func_key" style="display: none;" func_name="showCashCheckOut()"></span>
              </div>

              <div class="col-4">
                <div class="dropup">
                  <button class="btn btn-dark dropdown-toggle" type="button" id="paymentTypeBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Payment type
                  </button>
                  <span class="shortcut_func_key" style="display: none; left: -15px; top: -10px;" func_name="showPaymentTypeMenu()"></span>
                  <div class="dropdown-menu" aria-labelledby="paymentTypeBtn">
                    <!-- <button class="dropdown-item cardPayment" payment_type="debit_card" payment_type_text="Debit card" href="#">
                      Debit card
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsDebit()"></span>
                    </button>
                    <button class="dropdown-item cardPayment" payment_type="credit_card" payment_type_text="Credit card" href="#">
                      Credit card
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsCredit()"></span>
                    </button> -->
                    <button class="dropdown-item cardPayment" payment_type="card" payment_type_text="Kredit Kad" href="#">
                      Kredit Kad
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsCard()"></span>
                    </button>
                    <!-- <button class="dropdown-item cardPayment" payment_type="e-wallet" payment_type_text="E-wallet" href="#">
                      E-wallet
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsEwallet()"></span>
                    </button> -->
                    <button class="dropdown-item cardPayment" payment_type="tng" payment_type_text="Touch & Go" href="#">
                      Touch & Go
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsTNG()"></span>
                    </button>
                    <button class="dropdown-item cardPayment" payment_type="maybank_qr" payment_type_text="Maybank QRPay" href="#">
                      Maybank QRPay
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsMaybank()"></span>
                    </button>
                    <button class="dropdown-item cardPayment" payment_type="grab_pay" payment_type_text="Grab Pay" href="#">
                      Grab Pay
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsGrab()"></span>
                    </button>
                    <button class="dropdown-item cardPayment" payment_type="boost" payment_type_text="Boost" href="#">
                      Boost
                      <span class="shortcut_func_key" style="display: none; left: -10px;" func_name="payAsBoost()"></span>
                    </button>
                  </div>
                </div>

              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="clearBtn" onclick="clearTransaction()">Clear</button>
                <span class="shortcut_func_key" style="display: none;" func_name="clearTransaction()"></span>
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

          <div id="removed_voucher_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon error"></div>
              <strong class="mr-auto">Voucher removed</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="removed_voucher_content">
            </div>
          </div>

          <div id="added_voucher_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon success"></div>
              <strong class="mr-auto">Voucher added</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="added_voucher_content">
            </div>
          </div>

          <div id="daily_closing_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon success"></div>
              <strong class="mr-auto">Cashier are closed</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="daily_closing_content">
            </div>
          </div>

          <div id="success_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon success"></div>
              <strong class="mr-auto">Success</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="success_content">
            </div>
          </div>

          <div id="error_toast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2000"> 
            <div class="toast-header">
              <div class="toast-icon error"></div>
              <strong class="mr-auto">Error</strong>
              <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="toast-body" id="error_content">
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
          Are you sure you want to delete this item ?
        </div>
        <div class="modal-footer">
          <input type="hidden" name="item_id" id="delete_item_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" id="deleteSubmit">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="removeVoucherModal" tabindex="-1" role="dialog" aria-labelledby="removeVoucherModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Remove voucher ?</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Are you sure you want to remove this voucher ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" id="submitRemoveVoucher">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="numpadModal" tabindex="-1" role="dialog" aria-labelledby="numpadModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
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
              <span class="invalid-feedback" role="alert"></span>
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
          <h5 style="text-align: center;" id="completed_balance">Balance : RM <span id="transaction_balance"></span></h5>
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
                <tr transaction_id="{{ $completed->id }}">
                  <td>{{ $completed->cashier_name }}</td>
                  <td>{{ $completed->transaction_no }}</td>
                  <td>{{ $completed->payment_type_text }}</td>
                  <td>
                    <p class="reference_no">{{ $completed->reference_no }}</p>
                    @if($completed->payment_type != "cash")
                      <a href="#" onclick="editReferenceNo('{{ $completed->id }}', '{{ $completed->reference_no }}')">Edit</a>
                    @endif
                  </td>
                  <td>RM {{ number_format($completed->total, 2) }}</td>
                  <td>RM {{ number_format($completed->payment, 2) }}</td>
                  <td>RM {{ number_format($completed->balance, 2) }}</td>
                  <!-- <td>
                    <div class="void_column" transaction_id="{{ $completed->id }}">
                      @if($completed->void == 1)
                        <span class="void">Voided by {{ $completed->void_by_name }}</span>
                        <br>
                        <button type="button" class="btn btn-secondary" onclick="undoVoidTransaction('{{ $completed->id }}')">Undo</button>
                      @else
                        <button type="button" class="btn btn-danger" onclick="voidTransaction('{{ $completed->id }}')">Void</button>
                      @endif
                    </div>
                  </td> -->
                  <td data-order="{{ $completed->transaction_date }}">{{ date('d M Y g:i:s A', strtotime($completed->transaction_date)) }}</td>
                  <td>
                    <button class="btn btn-success" onclick="printReceipt('{{ $completed->id }}', 1)">Print</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div id="user_management" class="full_page">
    <div class="close_full_page">
      <i class="fas fa-times"></i>
    </div>
    <h4 class="title">
      User Management
    </h4>
    <div class="content">
      <div class="row">
        <div class="col-12">
          <table id="user_management_table" class="table table-bordered table-striped" cellspacing="0" width="100%" style="width: 100% !important;">
            <thead style="width: 100% !important;">
              <tr>
                <th>Role</th>
                <th>Name</th>
                <th>Login username</th>
                <th>Edit</th>
                @if($user->user_type == 1)
                  <th>Delete</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @foreach($user_management_list as $user_detail)
                <tr user_id="{{ $user_detail->id }}">
                  <td>
                    @if($user_detail->user_type == 1)
                      Management
                    @else
                      Cashier
                    @endif
                  </td>
                  <td>{{ $user_detail->name }}</td>
                  <td>{{ $user_detail->username }}</td>
                  <td>
                    <button class="btn btn-primary" type="button" onclick="editUser('{{ $user_detail->id }}', '{{ $user_detail->username }}', '{{ $user_detail->name }}')" {{ $user_detail->user_type == 1 && $user_detail->id != $user->id ? 'disabled' : '' }}>
                      <i class="fas fa-edit"></i>
                    </button>
                  </td>
                  @if($user->user_type == 1)
                    <td>
                      <button class="btn btn-danger" type="button" onclick="deleteUser('{{ $user_detail->id }}')" {{ $user_detail->user_type == 1 ? 'disabled' : '' }}>
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  @endif
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($user->user_type == 1)
            <button class="btn btn-success" type="button" onclick="addNewUser()">
              <i class="fas fa-plus"></i>
              Add New User
            </button>
          @endif
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
          You sure you want to void this transaction ?
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
          You sure you want to un-void this transaction ?
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
          You sure you want to clear this transaction ?
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

  <!-- print receipt -->
  <div id="receipt">
    <div>
      <div style="display: flex; flex-direction: column; text-align: center;">
        <label style="font-size: 11px;">HOME U(M) SDN BHD (125272-P)</label>
        <label style="font-size: 11px;">{{ $contact_number }}</label>
        <label style="font-size: 11px;">{!! nl2br(e($branch_address)) !!}</label><br/>
        <!-- <label>RESIT</label> -->
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

        <div style="font-size: 8px; text-align: center;">
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

  <div id="dailyReport" style="display: none;">
    <div style="padding: 30px;">
      <div style="display: flex; flex-direction: column; text-align: center;">
        <label>HOME U(M) SDN BHD (125272-P)</label>
        <label>{{ $contact_number }}</label>
        <label>{!! nl2br(e($branch_address)) !!}</label><br/>
      </div>
      <div id="dailyReportContent">
      </div>
    </div>
  </div>

  <div class="modal fade" id="cardCheckoutModal" tabindex="-1" role="dialog" aria-labelledby="cardCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cardCheckoutModalLabel">Reference No</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label>Payment type : </label>
          <label id="payment_type_text"></label>
          <input type="text" class="form-control" name="reference_no" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="payment_type" id="payment_type" />
          <button type="button" class="btn btn-success" id="submitCardPayment">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="voucherModal" tabindex="-1" role="dialog" aria-labelledby="voucherModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="voucherModalLabel">Voucher code</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="voucher_code" autocomplete="off" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <!-- <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitVoucher">Submit</button>
        </div> -->
      </div>
    </div>
  </div>

  <div class="modal fade" id="editReferenceNoModal" tabindex="-1" role="dialog" aria-labelledby="editReferenceNoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editReferenceNoModalLabel">Edit Reference No</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="edit_reference_no" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <input type='hidden' name='edit_transaction_no' />
          <button type="button" class="btn btn-success" id="submitEditReferenceNo">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="openingModal" tabindex="-1" role="dialog" aria-labelledby="openingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="openingModalLabel">Cashier opening</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label>Cashier cash</label>
          <input type="text" class="form-control" name="cashier_opening_amount" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitOpening" onclick="submitOpening()">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="closingModal" tabindex="-1" role="dialog" aria-labelledby="closingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="closingModalLabel">Cashier logout</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label>Cashier cash</label>
          <input type="text" class="form-control" name="cashier_closing_amount" />
          <input type="hidden" name="calculated_closing_amount" value="0" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitClosing" onclick="submitClosing()">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="dailyClosingModal" tabindex="-1" role="dialog" aria-labelledby="dailyClosingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dailyClosingModalLabel">Cashier daily closing</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h4 style="text-align: center;">Manager login</h4>
          <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" name="manager_username" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="manager_password" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <!-- <div class="form-group">
            <label>Cashier cash</label>
            <input type="text" class="form-control" name="daily_closing_amount" />
            <input type="hidden" name="daily_calculated_amount" value="0" />
            <span class="invalid-feedback" role="alert"></span>
          </div> -->

          <span id="dailyClosingFeedback" class="invalid-feedback" role="alert"></span>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitDailyClosing" onclick="submitDailyClosing()">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="cashFloatModal" tabindex="-1" role="dialog" aria-labelledby="cashFloatModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="voucherModalLabel"><label id="cash_float_title">Cash Float</label></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Amount</label>
            <input type="text" class="form-control" name="cash_float" autocomplete="off" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Remarks</label>
            <input type="text" class="form-control" name="cash_float_remarks" autocomplete="off" />
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="cash_float_type" />
          <button type="button" class="btn btn-success" id="submitCashFloat">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="syncHQModal" tabindex="-1" role="dialog" aria-labelledby="syncHQModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="syncHQModalLabel">Syncing HQ</h5>
          <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button> -->
        </div>
        <div class="modal-body" id="syncHQContent" style="text-align: center;"> 
          <div style="display: block; font-size: 50px; color: #007bff;">
            <i class="fas fa-spinner fa-spin"></i> 
          </div>
          Syncing data to HQ, please do not refresh the page.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" disabled id="syncHQBtn">Syncing...</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="syncProductListModal" tabindex="-1" role="dialog" aria-labelledby="syncProductListModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="syncProductListModalLabel">Syncing HQ Product List</h5>
          <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button> -->
        </div>
        <div class="modal-body" id="syncHQProductListContent" style="text-align: center;"> 
          <div style="display: block; font-size: 50px; color: #007bff;">
            <i class="fas fa-spinner fa-spin"></i> 
          </div>
          Syncing HQ product list, please do not refresh the page.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" disabled id="syncProductListBtn">Syncing...</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete user</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          You sure you want to delete this user ?
        </div>
        <div class="modal-footer">
          <input type="hidden" id="delete_user_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
          <button type="button" class="btn btn-primary" onclick="submitDeleteUser()">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add new cashier</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="new_user_name" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>User type</label>
            <select class="form-control" name="user_type">
              <option value="0">Cashier</option>
              <option value="1">Management</option>
            </select>
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Username</label>
            <input type="text" name="new_user_username" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" name="new_user_password" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="new_user_password_confirmation" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="submitAddUser(this)">Add</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit user</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="edit_user_name" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Username</label>
            <input type="text" name="edit_user_username" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" name="edit_user_password" class="form-control" placeholder="Leave blank if no changes" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="edit_user_password_confirmation" class="form-control" />
            <span class="invalid-feedback" role="alert"></span>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="edit_user_id" />
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="submitEditUser()">Edit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- <div class="modal fade" id="cashierLoginModal" tabindex="-1" role="dialog" aria-labelledby="cashierLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cashierLoginModalLabel">Login</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-xs-12">
              <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" name="cashier_email" />
              </div>
            </div>
            <div class="col-xs-12">
              <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="cashier_password" />
              </div>
            </div>
          </div>

          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <input type='hidden' name='edit_transaction_no' />
          <button type="button" class="btn btn-success" id="submitCashierLogin">Login</button>
        </div>
      </div>
    </div>
  </div> -->

</body>

<script>
  
  var combined_barcode = "";
  var barcode_timeout;
  var numpad_using_type = "numpad";
  var numpad_prefill = 0;
  var searchFunc;
  var voucherFunc;
  var related_timeout;
  var session = "{{ $session }}";
  var shortcut_key = @json($shortcut_key);
  var user = @json($user);
  var selecting_related = 1;
  var total_related = 0;
  var device_type = "{{ $device_type }}";
  var barcode_toggle = false;

  var transaction_total = "{{ $real_total }}";
  var opening = "{{ $opening }}";

  var previous_receipt_table = $("#previous_receipt_table").DataTable( {
    pageLength: 25,
    scrollY: '60vh',
    scrollCollapse: true,
    // responsive: true,
    order: [[ 7, "desc" ]]
  });

  var user_management_table = $("#user_management_table").DataTable( {
    pageLength: 25,
    scrollY: '60vh',
    scrollCollapse: true,
    // responsive: true,
    order: [[ 0, "desc" ]]
  });

  $(document).on('click', '#barcode_toggle', function (e) {
    e.stopPropagation();
  });

  $(document).ready(function(){

    $('.form-check-input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' /* optional */
    });

    if(session == "")
    {
      $("#syncHQModal").modal('show');
      syncHQ(2);
    }

    for(var a = 0; a < shortcut_key.length; a++)
    {
      var shortcut_func_name = shortcut_key[a].function;
      $(".shortcut_func_key[func_name='"+shortcut_func_name+"']").html(shortcut_key[a].character).show();
    }

    $(document).on('keydown', function(e){
      // ESC
      if(e.which == "27")
      {
        $("#previous_receipt").hide();
        $("#user_management").hide();
        swal.close();

        if($("#numpadModal").css("display") != "none" && $(".swal2-container").length == 0)
        {
          $("#numpadModal").modal('hide');
        }

        $("input[name='barcode_manual']").iCheck("uncheck");
      }
      else if(e.which == 13)
      {
        if(!$(e.target).closest('input[name="received_payment"]').length)
        {
          swal.close();
        }

        if($("#openingModal").css("display") != "none")
        {
          $("#submitOpening").click();
        }
        else if($("#closingModal").css("display") != "none")
        {
          $("#submitClosing").click();
        }
        else if($("#cashFloatModal").css("display") != "none")
        {
          $("#submitCashFloat").click();
        }
        else if($("#clearItemsModal").css("display") != "none")
        {
          submitClearTransaction(1);
        }
        else if($("#dailyClosingModal").css("display") != "none")
        {
          $("#submitDailyClosing").click();
        }
        else if($("#barcode_manual").is(":checked") == true && total_related > 0)
        {
          addRelatedItem();
        }
        else
        {
          $(".modal").not("#cardCheckoutModal, #numpadModal, #cashFloatModal").modal('hide');
        }
      }
      // up down
      else if(e.which == 38 || e.which == 40)
      {
        if(total_related > 0)
        {
          if(e.which == 38 && selecting_related > 1)
          {
            selecting_related--;
          }
          else if(e.which == 40 && selecting_related != total_related)
          {
            selecting_related++;
          }
          selectRelated();
        }
      }
      else if(e.key && opening == 1)
      {
        if(e.key.length == 1)
        {
          combined_barcode += e.key;
        }
      }

      clearTimeout(barcode_timeout);
      barcode_timeout = setTimeout(run_barcode, 300);
    });

    $("#barcode_manual").on('ifChanged', function(){
      $("#barcode").val("");
      var manual_checked = $(this).is(":checked");
      if(manual_checked)
      {
        $("#barcode").attr("disabled", false).focus();
      }
      else
      {
        $("#related_item").html("");
        total_related = 0;
        selecting_related = 1;
        $("#barcode").attr("disabled", true);
      }
    });

    $("#barcode").on('keyup', function(e){
      clearInterval(searchFunc);
      // enter
      // delay 50ms because keydown will not capture on change
      if($("#barcode_manual").is(":checked") == false)
      {
        // ctrl
        if(e.which != 17){
          searchFunc = setTimeout(searchAndAddItem, 10);
        }
      }
      else
      {
        if((e.key && e.key.length == 1) || e.which == 8)
        {
          clearTimeout(related_timeout);
          related_timeout = setTimeout(searchRelatedItem, 300);
        }
      }
    });

    $("input[name='voucher_code']").on('keydown', function(e){
      clearInterval(voucherFunc);
      if(e.which != 17){
        voucherFunc = setTimeout(submitVoucher, 200);
      }
    });

    $("input[name='reference_no']").on('keydown', function(e){
      if(e.which == 13)
      {
        $("#submitCardPayment").click();
      }
    });

    $("#deleteSubmit").click(function(){
      var item_id = $("#delete_item_id").val();
      submitDeleteItem(item_id);
    });

    $(".cardPayment").click(function(){
      $("input[name='reference_no']").removeClass("is-invalid");

      var payment_type = $(this).attr("payment_type");
      var payment_type_text = $(this).attr("payment_type_text");
      
      $("#payment_type").val(payment_type);
      $("#payment_type_text").html(payment_type_text);
      showReferenceInput();
    });

    $("input[name=received_payment]").on('keyup', function(e){
      if(e.which == 13)
      {
        $(".numpad_btn.submit").click();
      }
      else if(isNaN(e.key) && e.key.length == 1 && e.key != ".")
      {
        var received_payment = $("input[name='received_payment']").val().slice(0, -1);
        $("input[name='received_payment']").val(received_payment);
      }
    });

    $(".numpad_number_btn").click(function(){
      var type = $(this).attr("type");
      var number = $(this).attr("number");
      // type 1 = 0 to 9
      // type 2 = 100, 50, 10, 5

      $("input[name='received_payment']").removeClass("is-invalid");

      if(type == 1)
      {
        if(numpad_using_type == "prefill")
        {
          $("input[name='received_payment']").val(0);
        }

        numpad_using_type = "numpad";
        numpad_prefill = 0;

        var received_number = $("input[name='received_payment']").val();
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
        if(numpad_using_type == "numpad")
        {
          $("input[name='received_payment']").val("");
        }

        numpad_using_type = "prefill";
        numpad_prefill = parseFloat(numpad_prefill) + parseFloat(number);

        numpad_prefill += ".00";
        $("input[name='received_payment']").val(numpad_prefill);
      }

      $("input[name='received_payment']").focus();
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

        if(numpad_using_type == "prefill")
        {
          numpad_prefill = edited_number;
        }
      }

      $("input[name='received_payment']").focus();
    });

    $(".numpad_btn.clear, .numpad_btn.exit").click(function(){
      numpad_using_type = "numpad";
      numpad_prefill = 0;
      $("input[name='received_payment']").val("");

      $("input[name='received_payment']").focus();
    });

    $(".numpad_btn.submit").click(function(){
      if($(this).attr("disabled") != "disabled")
      {
        if($("input[name='received_payment']").val() == "0" || $("input[name='received_payment']").val() == "")
        {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Cash amount cannot be 0 or empty.',
          });

          return;
        }

        $(".numpad_btn.submit").attr("disabled", true);
        submitCashPayment();
      }
      
    });

    $(".close_full_page").click(function(){
      $(".full_page").hide();
    });

    $("#submitCardPayment").click(function(){
      var reference_no = $("input[name='reference_no']").val();
      if(reference_no)
      {
        submitCardPayment();
      }
      else
      {
        $("input[name='reference_no']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Reference No cannot be empty.</strong>");
      }
    });

    $("#submitEditReferenceNo").click(function(){

      var edit_reference_no = $("input[name='edit_reference_no']").val();
      if(edit_reference_no)
      {
        submitEditReferenceNo();
      }
      else
      {
        $("input[name='edit_reference_no']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Reference No cannot be empty.</strong>");
      }
    });

    $("#submitVoucher").click(function(){
      submitVoucher();
    });

    $("input[name='voucher_code']").on('keydown', function(e){
      if(e.which == 13)
      {
        submitVoucher();
      }
    });

    $("#remove_voucher").click(function(){
      $("#removeVoucherModal").modal('show');
    });

    $("#submitRemoveVoucher").click(function(){
      submitRemoveVoucher();
    });

    if(opening == 0 && session != "" && device_type == 2)
    {
      $("input[name='cashier_opening_amount']").val("");
      $("#openingModal").modal('show');

      setTimeout(function(){
        $("input[name='cashier_opening_amount']").focus();
      }, 500);

      disablePosSystem();
    }

    if(device_type == 1)
    {
      disablePosSystem();
    }

    $("#openingBtn").click(function(){
      $("input[name='cashier_opening_amount']").val("");
      $("#openingModal").modal('show');

      setTimeout(function(){
        $("input[name='cashier_opening_amount']").focus();
      }, 500);
    });

    $("#dailyClosingBtn").click(function(){

      $("#dailyClosingModal").modal('show');
      setTimeout(function(){
        $("input[name='manager_username']").focus();
      }, 500);

      // $.get("{{ route('calculateClosingAmount') }}", function(result){
      //   $("#dailyClosingFeedback").hide();

      //   $("input[name='manager_username'], input[name='manager_password']").val("");
        
      //   $("input[name='daily_closing_amount']").removeClass("is-invalid").val(result.closing_amount);
      //   $("input[name='daily_calculated_amount']").val(result.closing_amount);
      // }).fail(function(xhr){
      //   if(xhr.status == 401)
      //   {
      //     Swal.fire({
      //       title: 'Your account was logged out, please login again.',
      //       icon: 'error',
      //       confirmButtonText: 'OK',
      //     }).then((result) => {
      //       /* Read more about isConfirmed, isDenied below */
      //       if (result.isConfirmed) {
      //         location.reload();
      //       }
      //     })
      //   }
      // });
    });

    $("#closingBtn").click(function(){
      closing();
    });

    $("#floatInBtn, #floatOutBtn, #refundBtn, #bagiKeKetuaBtn").click(function(){
      $("input[name='cash_float']").val("");
      $("input[name='cash_float_remarks']").val("");
      if($(this).attr("id") == "floatInBtn")
      {
        $("input[name='cash_float_type']").val('in');
        $("#cash_float_title").html("Cash Float ( In )");
      }
      else if($(this).attr("id") == "floatOutBtn")
      {
        $("input[name='cash_float_type']").val('out');
        $("#cash_float_title").html("Cash Float ( Out )");
      }
      else if($(this).attr("id") == "refundBtn")
      {
        $("input[name='cash_float_type']").val('refund');
        $("#cash_float_title").html("Refund");
      }
      else if($(this).attr("id") == "bagiKeKetuaBtn")
      {
        $("input[name='cash_float_type']").val('boss');
        $("#cash_float_title").html("Bagi Ke Ketua");
      }

      $("#cashFloatModal").modal('show');
      setTimeout(function(){
        $("input[name='cash_float']").focus();
      }, 500);
    });

    $("#submitCashFloat").click(function(){
      submitCashFloat();
    });

    $("#barcode_toggle_checkbox").change(function(){
      barcode_toggle = $(this).is(":checked");

      if($(this).is(":checked")){
        localStorage.setItem('barcode_alert',1);
      }else{
        localStorage.removeItem('barcode_alert');
      }

    });

    let date = new Date();
    $("#time").text(`${date.toLocaleTimeString()}`);

    setInterval(()=>{
      let date = new Date();
      $("#time").text(`${date.toLocaleTimeString()}`);
    },1000);

    if(localStorage.getItem('barcode_alert') == 1){
      $(".toggle-group").click();
    }


  });

  function searchAndAddItem()
  {
    $(".toast").toast('hide');
    $("#barcode").focus();

    let barcode = $("#barcode").val();

    $.post("{{ route('searchAndAddItem') }}", { "_token" : "{{ csrf_token() }}", "barcode" : barcode }, function(result){
      if(result.error == 1)
      {
        if(barcode_toggle == false)
        {
          $("#search_error_title").html(result.title);
          $("#search_error_content").html(result.message);

          $("#search_error_toast").toast('show');
        }
        else if(barcode_toggle == true)
        {
          Swal.fire({
            allowOutsideClick: false,
            title: 'Barcode not found, please scan again.',
            icon: 'error',
            confirmButtonText: 'OK',
          });
        }
      }
      else if(result.error == 0)
      {
        let transaction_summary = result.transaction_summary;
        let product = result.product;

        transaction_total = transaction_summary.real_total;
        $("#transaction_id").val(transaction_summary.transaction_id);

        $("#total_quantity").parent(".summary-detail").show();
        $("#total_quantity").html(result.item_count);


        $("#added_item_title").html(result.title);
        $("#added_item_content").html("Product "+product.product_name+" is successfully added");

        $("#added_item_toast").toast('show');

        generateItemList(transaction_summary);
        $("input[name=barcode_manual]").iCheck('uncheck');

        $("#items-table tbody tr:first-child").addClass("new_item");
      }
      $("#barcode").val('');
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
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
      html += "<td>";
      html += "<div class='quantity'>";
      html += "<i class='fa fa-minus' onclick='editQuantity(this, \"plus\", \""+item_detail.id+"\")'></i>";
      html += "<label>"+(item_detail.quantity + item_detail.wholesale_quantity)+"</label>";
      html += "<i class='fa fa-plus' onclick='editQuantity(this, \"minus\", \""+item_detail.id+"\")'></i>";
      html += "</div>";
      html += "</td>";
      html += "<td class='subtotal'>";

      if(item_detail.wholesale_quantity > 0)
      {
        html += "<span style='color:#9c27b0;'>RM "+item_detail.total_wholesale_price_text+"</span><br>";
      }

      if(item_detail.quantity > 0)
      {
        html += "RM "+item_detail.total_price_text;
      }

      html += "</td>";
      html += "<td>";
      html += "<button class='btn btn-dark items-cancel' onclick='cancelItem(\""+item_detail.id+"\")'>Cancel</button>";
      html += "</td>";
      html += "</tr>";
    }

    $("#items-table tbody").html(html);
    $("#price").html(transaction_summary.subtotal);
    $("#total").html(transaction_summary.total);

    if(transaction_summary.round_off == "0.00")
    {
      $("#round_off_box").hide();
    }
    else
    {
      $("#round_off_box").show();
    }

    $("#round_off").html(transaction_summary.round_off);
    // $("#payment").html("RM "+transaction_summary.payment);
    // $("#balance").html("RM "+transaction_summary.balance);
  }

  function submitDeleteItem(item_id)
  {
    var transaction_id = $("#transaction_id").val();
    $.post("{{ route('submitDeleteItem') }}", { "_token" : "{{ csrf_token() }}", "item_id" : item_id, "transaction_id" : transaction_id }, function(result){
      if(result.error == 0)
      {
        $("#deleteModal").modal('hide');
        $("#items-table tbody tr[item_id="+item_id+"]").remove();

        let transaction_summary = result.transaction_summary;
        if(transaction_summary == null)
        {
          transaction_total = 0;
          $("#price").html("0.00");
          $("#total").html("0.00");
          $("#round_off_box").hide();
          $("#round_off").html("");
          $("#total_quantity").hide().html("");
        }
        else
        {
          transaction_total = transaction_summary.real_total;
          $("#price").html(transaction_summary.subtotal);
          $("#total").html(transaction_summary.total);
          $("#total_quantity").show().html(result.item_quantity);

          if(transaction_summary.round_off == "0.00")
          {
            $("#round_off_box").hide();
          }
          else
          {
            $("#round_off_box").show();
          }

          $("#round_off").html(transaction_summary.round_off);
        }
      }
      else
      {
        console.log("something wrong");
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function showNumPad()
  {
    $("#numpadModal").modal('show');
    setTimeout(function(){

      $("input[name='received_payment']").val("").focus();
    }, 500);
  }

  function submitCashPayment()
  {
    var received_cash = $("input[name='received_payment']").val();

    if(parseFloat(received_cash) <= 0)
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Cannot submit as RM 0.00</strong>");

      $(".numpad_btn.submit").attr("disabled", false);
      return;
    }

    if(parseFloat(transaction_total) <= 0)
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Transaction total is RM 0.00</strong>");

      $(".numpad_btn.submit").attr("disabled", false);
      return;
    }

    if(parseFloat(received_cash) < parseFloat(transaction_total))
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Received cash is lesser than transaction price</strong>");

      $(".numpad_btn.submit").attr("disabled", false);
      return;
    }

    var transaction_id = $("#transaction_id").val();

    $.post("{{ route('submitTransaction') }}", {"_token" : "{{ csrf_token() }}", "received_cash" : received_cash, "transaction_id" : transaction_id, "payment_type" : "cash" }, function(result){

      
      if(result.error == 0)
      {
        setTimeout(function(){
          $(".numpad_btn.submit").attr("disabled", false);
        }, 500);

        $("#numpadModal").modal('hide');

        $("#completedTransactionModal").modal('show');
        
        $("#transaction_balance").html(result.balance);

        transactionCompleted(result.completed_transaction.id, 1);

        submitClearTransaction(0);
        prependCompletedTransaction(result.completed_transaction);
      }
      else
      {
        $(".numpad_btn.submit").attr("disabled", false);
        alert("Error");
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }

      $(".numpad_btn.submit").attr("disabled", false);
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

      if(transaction_id)
      {
        $.post("{{ route('clearTransaction') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
          if(result.error == 0)
          {
            transaction_total = 0;

            $("#items-table tbody").html("");
            $("#price, #discount, #total, #round_off").html("0.00");
            $("#discount_name").html("Discount");
            $("#round_off_box").hide();
            $("#remove_voucher").hide();
            $("#total_quantity").hide().html("");

            $("#clearItemsModal").modal('hide');
          }
        }).fail(function(xhr){
          if(xhr.status == 401)
          {
            loggedOutAlert();
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
      $("#price, #discount, #total, #round_off").html("0.00");
      $("#discount_name").html("Discount");
      $("#remove_voucher").hide();
      $("#round_off_box").hide();
    }
  }

  function prependCompletedTransaction(completed_transaction)
  {
    var void_html = "<div class='void_column' transaction_id="+completed_transaction.id+">";
    void_html += "<button type='button' class='btn btn-danger' onclick='voidTransaction(\""+completed_transaction.id+"\")'>Void</button>";
    void_html += "</div>";

    var data = "<tr transaction_id="+completed_transaction.id+">";
    data += "<td>"+completed_transaction.cashier_name+"</td>";
    data += "<td>"+completed_transaction.transaction_no+"</td>";
    data += "<td>"+completed_transaction.payment_type_text+"</td>";
    data += "<td>";
    if(completed_transaction.payment_type != "cash")
    {
      data += "<p class='reference_no'>"+completed_transaction.reference_no+"</p>";
      data += "<a href='#' onclick='editReferenceNo(\""+completed_transaction.id+"\", \""+completed_transaction.reference_no+"\")'>Edit</a>";
    }
    data += "</td>";
    data += "<td>RM "+completed_transaction.total_text+"</td>";
    data += "<td>RM "+completed_transaction.payment_text+"</td>";
    data += "<td>RM "+completed_transaction.balance_text+"</td>";
    // data += "<td>"+void_html+"</td>";
    data += "<td data-order='"+completed_transaction.transaction_date+"'>"+completed_transaction.transaction_date_text+"</td>";

    data += '<td><div class="btn btn-success print_receipt" transaction_id=\''+completed_transaction.id+'\'>Print</div></td>';

    data += "</tr>";

    previous_receipt_table.row.add($(data)).node();

    previous_receipt_table.draw();
    // previous_receipt_table.responsive.recalc();

    $(".print_receipt").click(function(){
      let print_transaction_id = $(this).attr("transaction_id");
      printReceipt(print_transaction_id, 1);
    });
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
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
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
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function clearTransaction()
  {
    $("#clearItemsModal").modal('show');
  }

  function logout()
  {
    $("#logout_form").submit();
  }

  function transactionCompleted(transaction_id, show_balance)
  {
    printReceipt(transaction_id, 0);
    if(show_balance === 1)
    {
      $("#completed_balance").show();
    }
    else
    {
      $("#completed_balance").hide();
    }

    $("#total_quantity").html("");
    $("#total_quantity").parent(".summary-detail").hide();
  }

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
          items_html += "<td style='vertical-align:top;'>"+transaction_detail[a].product_name+"<br>"+transaction_detail[a].barcode+"</td>";
          items_html += "<td style='width: 120px;vertical-align:top;'>";

          if(transaction_detail[a].wholesale_quantity > 0)
          {
            items_html += transaction_detail[a].wholesale_quantity+".00 X RM "+transaction_detail[a].wholesale_price_text+"<br>";
          }

          if(transaction_detail[a].quantity > 0)
          {
            items_html += transaction_detail[a].quantity+".00 X RM "+transaction_detail[a].price_text;
          }

          items_html += "</td>";
          items_html += "<td style='width:70px;text-align:right;vertical-align:top;'>RM "+transaction_detail[a].total_text+"</td>";
          items_html += "</tr>";
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

        $("#receipt_voucher").hide();
        $("#receipt_ori_payment").html("");
        $("#receipt_voucher_name").html("");
        $("#receipt_discount").html("");

        if(transaction.payment_type != "cash")
        {
          $("#receipt_other_payment").show();
          $("#receipt_other_payment").html("<div>"+payment_type_text+"</div><div>RM "+transaction.total_text+"</div>");
          $("#receipt_cash").hide();
        }
        else
        {
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

        setTimeout(function(){newWin.close();},10);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function showReferenceInput()
  {
    $("input[name='reference_no']").val("");
    $("#cardCheckoutModal").modal('show');
    setTimeout(function(){
      $("input[name='reference_no']").focus();
    }, 500);
  }

  function submitCardPayment()
  {
    var transaction_id = $("#transaction_id").val();
    var reference_no = $("input[name='reference_no']").val();
    var payment_type = $("#payment_type").val();

    $.post("{{ route('submitTransaction') }}", {"_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id, "payment_type" : payment_type, "reference_no" : reference_no }, function(result){

      $("#cardCheckoutModal").modal('hide');
      if(result.error == 0)
      {
        $("#completedTransactionModal").modal('show');

        transactionCompleted(result.completed_transaction.id, 0);
        $("input[name='reference_no']").val("");

        submitClearTransaction(0);
        prependCompletedTransaction(result.completed_transaction);
      }
      else
      {
        Swal.fire({
          title: result.message,
          icon: 'error',
          confirmButtonText: 'OK',
        }).then((result) => {
          /* Read more about isConfirmed, isDenied below */
          if (result.isConfirmed) {
            swal.close();
          }
        })
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function editReferenceNo(transaction_id, reference_no)
  {
    $("input[name='edit_transaction_no']").val(transaction_id);
    $("input[name='reference_no']").val(reference_no);

    $("#editReferenceNoModal").modal('show');
  }

  function submitEditReferenceNo()
  {
    var transaction_id = $("input[name='edit_transaction_no']").val();
    var edit_reference_no = $("input[name='edit_reference_no']").val();

    $.post("{{ route('editReferenceNo') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id, "reference_no" : edit_reference_no }, function(result){

      if(result.error == 0)
      {
        $("#editReferenceNoModal").modal('hide');
        $("#previous_receipt_table tbody tr[transaction_id="+transaction_id+"]").find(".reference_no").html(edit_reference_no);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function editQuantity(_this, type, item_id)
  {
    $.post("{{ route('editQuantity') }}", { "_token" : "{{ csrf_token() }}", "type" : type, "item_id" : item_id}, function(result){
      if(result.error == 0)
      {
        if(result.quantity > 0)
        {
          $(_this).siblings("label").html(result.quantity);

          var html = "";
          if(result.wholesale_price > 0)
          {
            html += "<span style='color:#9c27b0;'>RM "+result.wholesale_price_text+"</span><br>";
          }

          if(result.price)
          {
            html += "RM "+result.price_text;
          }

          $("#items-table tbody tr[item_id="+item_id+"] td.subtotal").html(html);
        }
        else
        {
          $("#items-table tbody tr[item_id="+item_id+"]").remove();
        }

        let transaction_summary = result.transaction_summary;
        if(transaction_summary == null)
        {
          transaction_total = 0;
          $("#price").html("0.00");
          $("#total").html("0.00");
          $("#total_quantity").hide().html("");
          $("#round_off_box").hide();
          $("#round_off").html("");
        } 
        else
        {
          transaction_total = transaction_summary.real_total;
          $("#price").html(transaction_summary.subtotal);
          $("#total").html(transaction_summary.total);
          $("#total_quantity").show().html(result.item_quantity);
          if(transaction_summary.round_off == "0.00")
          {
            $("#round_off_box").hide();
          }
          else
          {
            $("#round_off_box").show();
          }
          $("#round_off").html(transaction_summary.round_off);
        }
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function showVoucher()
  {
    if($("#voucherBtn").attr("disabled") != "disabled")
    {
      $("input[name='voucher_code']").val("").removeClass("is-invalid");
      $("#voucherModal").modal('show');

      setTimeout(function(){
        $("input[name='voucher_code']").focus();
      }, 500);
    }
    
  }

  function showPreviousReceipt()
  {
    if($("#previousReceiptBtn").attr("disabled") != "disabled")
    {
      $("#previous_receipt").show();

      setTimeout(function(){
        previous_receipt_table.draw();
      }, 50);
    }
  }

  function showOtherMenu()
  {
    $("#otherDropDownBtn").click();
  }

  function showOpening()
  {
    if($("#openingBtn").attr("disabled") != "disabled")
    {
      $("#openingBtn").click();
    }
  }

  function showClosing()
  {
    if($("#closingBtn").attr("disabled") != "disabled")
    {
      $("#closingBtn").click();
    }
  }

  function showDailyClosing()
  {
    if($("#dailyClosingBtn").attr("disabled") != "disabled")
    {
      $("#dailyClosingBtn").click();
    }
  }

  function showCashFloatIn()
  {
    if($("#floatInBtn").attr("disabled") != "disabled")
    {
      $("#floatInBtn").click();
    }
  }

  function showCashFloatOut()
  {
    if($("#floatOutBtn").attr("disabled") != "disabled")
    {
      $("#floatOutBtn").click();
    }
  }

  function showBagiKeKetua()
  {
    if($("#bagiKeKetuaBtn").attr("disabled") != "disabled")
    {
      $("#bagiKeKetuaBtn").click();
    }
  }

  function showClosingReport()
  {
    if(user.user_type == 1)
    {
      dailyReport();
    }
  }

  function showServerCashFloatReport()
  {
    if(user.user_type == 1)
    {
      serverCashFloatReport();
    }
  }

  function showUserManagement()
  {
    if(user.user_type == 1)
    {
      userManagement();
    }
  }

  function SCsyncHQTransaction()
  {
    if(user.user_type == 1)
    {
      syncTOHQ();
    }
  }

  function SCsyncHQProductList()
  {
    if(user.user_type == 1)
    {
      syncProductList(0);
    }
  }

  function showCashCheckOut()
  {
    $("input[name='received_payment']").val(0);
    numpad_using_type = "numpad";
    numpad_prefill = 0;

    showNumPad();
  }

  function showPaymentTypeMenu()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $("#paymentTypeBtn").click();
    }
  }

  // function payAsDebit()
  // {
  //   if($("#paymentTypeBtn").attr("disabled") != "disabled")
  //   {
  //     $(".cardPayment[payment_type='debit_card']").click();
  //   }
  // }

  // function payAsCredit()
  // {
  //   if($("#paymentTypeBtn").attr("disabled") != "disabled")
  //   {
  //     $(".cardPayment[payment_type='credit_card']").click();
  //   }
  // }

  function payAsCard()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $(".cardPayment[payment_type='card']").click();
    }
  }

  // function payAsEwallet()
  // {
  //   if($("#paymentTypeBtn").attr("disabled") != "disabled")
  //   {
  //     $(".cardPayment[payment_type='e-wallet']").click();
  //   }
  // }

  function payAsTNG()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $(".cardPayment[payment_type='tng']").click();
    }
  }

  function payAsMaybank()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $(".cardPayment[payment_type='maybank_qr']").click();
    }
  }

  function payAsGrab()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $(".cardPayment[payment_type='grab_pay']").click();
    }
  }

  function payAsBoost()
  {
    if($("#paymentTypeBtn").attr("disabled") != "disabled")
    {
      $(".cardPayment[payment_type='boost']").click();
    }
  }

  function clickManualKeyin()
  {
    let manual_val = $("input[name='barcode_manual']").is(":checked");
    if(!manual_val)
    {
      $("input[name='barcode_manual']").iCheck("check");
      $("#barcode").focus();
    }
  }

  function showKeySetup()
  {
    if(user.user_type == 1)
    {
      window.open(
        '{{ route("key_setup") }}',
        '_blank'
      );
    }
  }

  function showBranchProfile()
  {
    if(user.user_type == 1 && device_type == 1)
    {
      window.open(
        '{{ route("getBranchProfile") }}',
        '_blank'
      );
    }
  }
  
  function submitVoucher()
  {
    var voucher_code = $("input[name='voucher_code']").val();
    if(voucher_code)
    {
      $("input[name='voucher_code']").removeClass("is-invalid");
    }
    else
    {
      $("input[name='voucher_code']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Voucher code cannot be empty.</strong>");
      return false;
    }

    let code = $("input[name='voucher_code']").val();
    let transaction_id = $("#transaction_id").val();

    $.post("{{ route('submitVoucher') }}", { "_token" : "{{ csrf_token() }}", "code" : code, "transaction_id" : transaction_id}, function(result){
      if(result.error == 0)
      {
        transaction_total = result.real_total;

        $("#total").html(result.total);
        $("#discount").html(result.total_discount);
        $("#discount_name").html(result.voucher_name);

        if(result.round_off == "0.00")
        {
          $("#round_off_box").hide();
        }
        else
        {
          $("#round_off_box").show();
        }

        $("#round_off").html(result.round_off);

        $("#remove_voucher").css('display', '');

        $("input[name='voucher_code']").val("");

        $("#voucherModal").modal('hide');

        $("#added_voucher_content").html("Voucher is successfully added");
        $("#added_voucher_toast").toast('show');
      }
      else if(result.error == 1)
      {
        $("input[name='voucher_code']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>"+result.message+".</strong>");
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function submitRemoveVoucher()
  {
    let transaction_id = $("#transaction_id").val();
    $.post("{{ route('removeVoucher') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
      if(result.error == 0)
      {
        transaction_total = result.real_total;

        $("#removeVoucherModal").modal('hide');

        $("#removed_voucher_content").html("Voucher is removed.");
        $("#removed_voucher_toast").toast('show');

        $("#remove_voucher").hide();
        $("#discount").html("0.00");
        $("#discount_name").html("Discount");
        $("#total").html(result.total);

        if(result.round_off == "0.00")
        {
          $("#round_off_box").hide();
        }
        else
        {
          $("#round_off_box").show();
        }

        $("#round_off").html(result.round_off);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function disablePosSystem()
  {
    $("#voucherBtn, #cashCheckoutBtn, #paymentTypeBtn, #clearBtn, #previousReceiptBtn, #closingBtn, input[name='barcode_manual']").attr("disabled", true);
  }

  function enablePosSystem()
  {
    $("#voucherBtn, #cashCheckoutBtn, #paymentTypeBtn, #clearBtn, #previousReceiptBtn, #closingBtn, input[name='barcode_manual']").attr("disabled", false);
  }

  function submitOpening()
  {
    var opening_amount = $("input[name='cashier_opening_amount']").val();

    if(opening_amount)
    {
      $.post("{{ route('submitOpening') }}", {"_token" : "{{ csrf_token() }}", "opening_amount" : opening_amount}, function(result){
        if(result.error == 0)
        {
          $("#openingModal").modal('hide');
          enablePosSystem();

          opening = 1;

          $("#openingBtn").attr("disabled", true);
          $("#closingBtn, #floatInBtn, #floatOutBtn, #bagiKeKetuaBtn").attr("disabled", false);

        }
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
    else
    {
      $("input[name='cashier_opening_amount']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Opening amount cannot be empty.</strong>");
    }
  }

  function closing()
  {
    if(opening == 0)
    {
      logout();
    }
    else
    {
      $.get("{{ route('calculateClosingAmount') }}", function(result){
        $("input[name='cashier_closing_amount']").removeClass("is-invalid").val(result.closing_amount);
        $("#closingModal").modal('show');
        $("input[name='calculated_closing_amount']").val(result.closing_amount);

        setTimeout(function(){
          $("input[name='cashier_closing_amount']").focus();
          openDrawer("Closing amount  <br> RM "+result.closing_amount_text);
        }, 500);
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
  }

  function submitClosing()
  {
    var closing_amount = $("input[name='cashier_closing_amount']").val();
    var calculated_closing_amount = $("input[name='calculated_closing_amount']").val();

    if(closing_amount)
    {
      $.post("{{ route('submitClosing') }}", {"_token" : "{{ csrf_token() }}", "closing_amount" : closing_amount, ' calculated_amount' : calculated_closing_amount}, function(result){
        if(result.error == 0)
        {
          $("#closingModal").modal('hide');
          closingReport(result.closing_report);
          logout();
          // location.reload();
        }
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
    else
    {
      $("input[name='cashier_closing_amount']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Closing amount cannot be empty.</strong>");
    }
  }

  function submitDailyClosing()
  {
    $("#submitDailyClosing").html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);

    $("#dailyClosingFeedback").hide();
    $("input[name='manager_username'], input[name='manager_password'], input[name='daily_closing_amount']").removeClass("is-invalid");

    var manager_username = $("input[name='manager_username']").val();
    var manager_password = $("input[name='manager_password']").val();
    // var daily_closing_amount = $("input[name='daily_closing_amount']").val();
    // var daily_calculated_amount = $("input[name='daily_calculated_amount']").val();

    var proceed = 1;

    if(!manager_username)
    {
      $("input[name='manager_username']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Email cannot be empty.</strong>");
      proceed = 0;
    }

    if(!manager_password)
    {
      $("input[name='manager_password']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Password cannot be empty.</strong>");
      proceed = 0;
    }

    // if(!daily_closing_amount)
    // {
    //   $("input[name='daily_closing_amount']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Closing amount cannot be empty.</strong>");
    //   proceed = 0;
    // }

    if(proceed == 0)
    {
      $("#submitDailyClosing").html("Submit").attr("disabled", false);
      return;
    }

    $.post("{{ route('submitDailyClosing') }}", {"_token" : "{{ csrf_token() }}", "username" : manager_username, "password" : manager_password }, function(result){
      if(result.error == 0)
      {
        $("#dailyClosingModal").modal('hide');
        $("#syncHQModal").modal('show');
        disablePosSystem();

        $("#openingBtn").attr("disabled", false);
        $("#closingBtn, #floatInBtn, #floatOutBtn, #bagiKeKetuaBtn").attr("disabled", true);

        $("#daily_closing_content").html("This cashier are now closed.");
        $("#daily_closing_toast").toast('show');

        opening = 0;

        dailyReport();
        logout();
        // syncHQ(0);
      }
      else
      {
        $("#submitDailyClosing").html("Submit").attr("disabled", false);
        $("#dailyClosingFeedback").html("<strong>"+result.message+".</strong>").show();
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
      else
      {
        Swal.fire({
          title: 'Something wrong.',
          icon: 'error',
          confirmButtonText: 'OK',
        });
        $("#submitDailyClosing").html("Submit").attr("disabled", false);
      }
    });
  }

  function submitCashFloat()
  {
    let amount = $("input[name='cash_float']").val();
    let remarks = $("input[name='cash_float_remarks']").val();
    let cash_float_type = $("input[name='cash_float_type']").val();

    if(!amount)
    {
      $("input[name='cash_float']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Cash float amount cannot be empty.</strong>");
      return;
    }

    $.post("{{ route('submitCashFloat') }}", {"_token" : "{{ csrf_token() }}", "amount" : amount, "type" : cash_float_type, "remarks" : remarks }, function(result){
      if(result.error == 1)
      {
        $("#error_content").html(result.message);
        $("#error_toast").toast('show');

        return;
      }
      else
      {
        $("#cashFloatModal").modal('hide');

        $("#success_content").html("Cash float submitted");
        $("#success_toast").toast('show');

        var html = result.message;
        html += "<br>Counter : "+result.cashier_name+"<br>Float By : "+result.user_name;
        html += "<br>Remarks : "+result.remarks;
        html += "<br>Date time : "+result.datetime;

        openDrawer(html);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function syncHQ(manual)
  {
    window.onbeforeunload = function() {
      return "Please do not refresh the page.";
    }

    var html = "";
    html += '<div style="display: block; font-size: 50px; color: #007bff;">';
    html += '<i class="fas fa-spinner fa-spin"></i> ';
    html += '</div>';
    html += 'Syncing data to HQ, please do not refresh the page.';
    html += '</div>';

    $("#syncHQContent").html(html);
    $("#syncHQBtn").html("Syncing...").attr("disabled", true);

    var resync = 0;
    if(manual == 1)
    {
      resync = 1;
    }

    $.get("{{ route('branchSync') }}", { "resync" : resync }, function(result){
      if(result.error == 0)
      {
        $("#syncHQContent").html("Sync completed.");
        $("#submitDailyClosing").html("Submit").attr("disabled", false);

        $("#syncHQBtn").html("Sync completed").attr("disabled", false).off('click').click(function(){
          $("#syncHQModal").modal('hide');
          if(manual == 0)
          {
            logout();
          }
        });

        window.onbeforeunload = function () {
          // blank function do nothing
        }

        if(manual == 1 && opening == 1 && device_type == 2)
        {
          enablePosSystem();
        }
        else if(manual == 2)
        {
          $("#syncHQModal").modal('hide');
          syncProductList(1);
        }
      }
      else
      {
        // no session to sync
        if(manual == 2 && result.error == 2)
        {
          setTimeout(function(){
            $("#syncHQModal").modal('hide');
          }, 500); 
          syncProductList(1);
        }
        else if(result.error == 2)
        {

        }
        else
        {
          $("#syncHQBtn").html("Re-sync").attr("disabled", false).off('click').click(function(){
            syncHQ(manual);
            $(this).html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);
          });

          $("#syncHQContent").html("Sync failed, please sync again.");

          Swal.fire({
            title: result.message,
            icon: 'error',
            confirmButtonText: 'OK',
          });
        }
      }
    }).fail(function(){

      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
      else
      {
        $("#syncHQBtn").html("Re-sync").attr("disabled", false).off('click').click(function(){
          syncHQ(manual);
          $(this).html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);
        });

        $("#syncHQContent").html("Sync failed, please sync again.");

        Swal.fire({
          title: 'Something wrong, click Re-sync to sync again.',
          icon: 'error',
          confirmButtonText: 'OK',
        })
      }
      
    });
  }

  function dailyReport()
  {
    $.get("{{ route('getDailyReport') }}", function(result){
      if(result.error == 0)
      {
        // var category_report = result.category_report;
        // var department_report = result.department_report;
        var payment_type_result = result.payment_type_result;
        var pos_cashier = result.pos_cashier;
        var session = result.session;

        var html = "";
        html += "<div style='text-align:center;'>"
        html += "<h4 style='margin:0px;'>Report Jualan Harian</h4>";
        html += "<p style='margin:0px;'>Tarikh : "+session.opening_date_time+"</p>";
        html += "</div>";

        html += "<table style='width:100%;border-spacing: 0px;margin:auto;'>";
        html += "<tr>";
        html += "<td style='border:1px solid #000;'></td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Kutipan Tunai</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Kredit Kad</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Touch & Go</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Maybank QRPay</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Grab Pay</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Boost</td>";
        html += "<td style='border:1px solid #000;text-align:center;padding:0px 3px;width:12%;'>Jumlah Jualan</td>";
        html += "</tr>";
        html += "<tr><td style='border:1px solid #000; height:22px;'></td><td style='border:1px solid #000;'></td><td style='border:1px solid #000;'></td><td style='border:1px solid #000;'></td><td style='border:1px solid #000;'></td><td style='border:1px solid #000;'></td><td style='border:1px solid #000;'></td></td><td style='border:1px solid #000;'></td></tr>";
        
        for(var a = 0; a < pos_cashier.length; a++)
        {
          html += "<tr>";
          html += "<td style='border:1px solid #000;text-align:left;padding:0px 3px;'>"+pos_cashier[a].cashier_name+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].cash+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].card+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].tng+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].maybank_qr+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].grab_pay+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].boost+"</td>";
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+pos_cashier[a].total+"</td>";
          html += "</tr>";
        }

        html += "<tr>";
        html += "<td style='border:1px solid #000;text-align:left;padding:0px 3px;'>Jumlah</td>";
        for(var b = 0; b < payment_type_result.length; b++)
        { 
          html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+payment_type_result[b].total+"</td>";
        }
        html += "<td style='border:1px solid #000;text-align:right;padding:0px 3px;'>"+result.total_sales+"</td>";
        html += "</tr>";
        
        html += "</table>";
        $("#dailyReportContent").html(html);

        var dailyReportPrint = document.getElementById('dailyReport');
        var newWin = window.open('','Print-Window');

        newWin.document.open();
        newWin.document.write('<html><body onload="window.print()">'+dailyReportPrint.innerHTML+'</body></html>');
        newWin.document.close();

        setTimeout(function(){newWin.close();},10);
      }
      else
      {
        alert(result.message);
      }
    }).fail(function(xhr){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
    });
  }

  function userManagement()
  {
    setTimeout(function(){
      user_management_table.draw();
    }, 50);
    
    $("#user_management").show();
  }

  function deleteUser(user_id)
  {
    $("#delete_user_id").val(user_id);
    $("#deleteUserModal").modal('show');
  }

  function addNewUser()
  {
    $("#addUserModal").modal('show');
  }

  function editUser(user_id, username, name)
  {
    $("input[name='edit_user_name'], input[name='edit_user_username'], input[name='edit_user_password'], input[name='edit_user_password_confirmation']").removeClass("is-invalid");

    $("input[name='edit_user_id']").val(user_id);
    $("input[name='edit_user_name']").val(name);
    $("input[name='edit_user_username']").val(username);

    $("#editUserModal").modal('show');
  }

  function submitDeleteUser()
  {
    var delete_user_id = $("#delete_user_id").val();
    $.post("{{ route('deleteUser') }}", {"_token" : "{{ csrf_token() }}", "user_id" : delete_user_id }, function(result){
      if(result.error == 0)
      {
        user_management_table.row($("#user_management_table tbody tr[user_id="+delete_user_id+"]")).remove().draw();
        $("#deleteUserModal").modal('hide');

        $("#success_content").html("User has deleted");
        $("#success_toast").toast('show');
      }
    });
  }

  function submitAddUser(_this)
  { 
    $(_this).html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);

    $("input[name='new_user_name'], input[name='new_user_username'], input[name='new_user_password'], input[name='new_user_password_confirmation']").removeClass("is-invalid");

    var new_user_name = $("input[name='new_user_name']").val();
    var new_user_username = $("input[name='new_user_username']").val();
    var new_user_password = $("input[name='new_user_password']").val();
    var new_user_password_confirmation = $("input[name='new_user_password_confirmation']").val();
    var user_type = $("select[name='user_type']").val();

    var failed = false;
    if(!new_user_name)
    {
      $("input[name='new_user_name']").addClass("is-invalid").siblings(".invalid-feedback").html("Name cannot be empty.");
      failed = true;
    }

    if(!new_user_username)
    {
      $("input[name='new_user_username']").addClass("is-invalid").siblings(".invalid-feedback").html("Username cannot be empty.");
      failed = true;
    }

    if(!new_user_password)
    {
      $("input[name='new_user_password']").addClass("is-invalid").siblings(".invalid-feedback").html("Password cannot be empty.");
      failed = true;
    }

    if(!new_user_password_confirmation)
    {
      $("input[name='new_user_password_confirmation']").addClass("is-invalid").siblings(".invalid-feedback").html("Confirm password cannot be empty.");
      failed = true;
    }

    if(new_user_password != new_user_password_confirmation)
    {
      $("input[name='new_user_password_confirmation']").addClass("is-invalid");
      $("input[name='new_user_password']").addClass("is-invalid").siblings(".invalid-feedback").html("Password and confirm password must be same.");
      failed = true;
    }

    if(failed == false)
    { 
      $.post("{{ route('addNewUser') }}", { "_token" : "{{ csrf_token() }}", "name" : new_user_name, "username" : new_user_username, "password" : new_user_password, "user_type" : user_type }, function(result){
        $(_this).html("Add").attr("disabled", false);
        if(result.error == 0)
        {
          var created_user_detail = result.user_detail;

          var data = "<tr user_id="+created_user_detail.id+">";
          data += "<td>";
          if(created_user_detail.user_type == 1)
            data += "Management";
          else
            data += "Cashier";
          data += "</td>";
          data += "<td>"+created_user_detail.name+"</td>";
          data += "<td>"+created_user_detail.username+"</td>";
          data += "<td>";
          data += "<button class='btn btn-primary' type='button' onclick='editUser(\""+created_user_detail.id+"\")'>";
          data += "<i class='fas fa-edit'></i>";
          data += "</button>";
          data += "</td>";
          data += "<td>";
          data += "<button class='btn btn-danger' type='button' onclick='deleteUser(\""+created_user_detail.id+"\")'>";
          data += "<i class='fas fa-trash-alt'></i>";
          data += "</button>";
          data += "</td>";
          data += "</tr>";

          user_management_table.row.add($(data)).node();
          user_management_table.draw();

          $("#addUserModal").modal('hide');
        }
        else if(result.error == 2)
        {
          $("input[name='new_user_username']").addClass("is-invalid").siblings(".invalid-feedback").html("Username has been used, please keyin a new username.");
        }
      }).fail(function(xhr){
        $(_this).html("Add").attr("disabled", false);
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
    else
    {
      $(_this).html("Add").attr("disabled", false);
    }
  }

  function submitEditUser()
  {
    $("input[name='edit_user_name'], input[name='edit_user_username'], input[name='edit_user_password'], input[name='edit_user_password_confirmation']").removeClass("is-invalid");

    var edit_user_name = $("input[name='edit_user_name']").val();
    var edit_user_username = $("input[name='edit_user_username']").val();
    var edit_user_password = $("input[name='edit_user_password']").val();
    var edit_user_password_confirmation = $("input[name='edit_user_password_confirmation']").val();

    var failed = false;
    if(!edit_user_name)
    {
      $("input[name='edit_user_name']").addClass("is-invalid").siblings(".invalid-feedback").html("Name cannot be empty.");
      failed = true;
    }

    if(!edit_user_username)
    {
      $("input[name='edit_user_username']").addClass("is-invalid").siblings(".invalid-feedback").html("Username cannot be empty.");
      failed = true;
    }

    // if(!edit_user_password)
    // {
    //   $("input[name='edit_user_password']").addClass("is-invalid").siblings(".invalid-feedback").html("Password cannot be empty.");
    //   failed = true;
    // }

    // if(!edit_user_password_confirmation)
    // {
    //   $("input[name='edit_user_password_confirmation']").addClass("is-invalid").siblings(".invalid-feedback").html("Confirm password cannot be empty.");
    //   failed = true;
    // }

    if(edit_user_password != edit_user_password_confirmation)
    {
      $("input[name='edit_user_password_confirmation']").addClass("is-invalid");
      $("input[name='edit_user_password']").addClass("is-invalid").siblings(".invalid-feedback").html("Password and confirm password must be same.");
      failed = true;
    }

    var edit_user_id = $("input[name='edit_user_id']").val();

    if(failed == false)
    {
      $.post("{{ route('editUser') }}", { "_token" : "{{ csrf_token() }}", "user_id" : edit_user_id, 'username' : edit_user_username, 'name' : edit_user_name, 'password' : edit_user_password }, function(result){
        if(result.error == 0)
        {
          var edit_user_detail = result.user_detail;

          $("#user_management_table tbody tr[user_id='"+edit_user_detail.id+"'] td:eq(1)").html(edit_user_detail.name);
          $("#user_management_table tbody tr[user_id='"+edit_user_detail.id+"'] td:eq(2)").html(edit_user_detail.username);

          $("#editUserModal").modal('hide');
        }
        else if(result.error == 2)
        {
          $("input[name='edit_user_username']").addClass("is-invalid").siblings(".invalid-feedback").html("Username has been used, please keyin a new username.");
        }
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
  }

  function syncTOHQ()
  {
    $("#syncHQModal").modal('show');
    disablePosSystem();
    syncHQ(1);
  }

  function syncProductList(create_session)
  {
    window.onbeforeunload = function() {
      return "Please do not refresh the page.";
    }

    var html = "";
    html += '<div style="display: block; font-size: 50px; color: #007bff;">';
    html += '<i class="fas fa-spinner fa-spin"></i>';
    html += '</div>';
    html += 'Syncing data to HQ, please do not refresh the page.';
    html += '</div>';

    $("#syncHQProductListContent").html(html);
    $("#syncProductListBtn").html("Syncing...").attr("disabled", true);

    $("#syncProductListModal").modal('show');
    disablePosSystem();

    var route_url = "{{ route('productSync', ['create_session' => 'create_value']) }}";
    route_url = route_url.replace('create_value', create_session);
    
    $.get(route_url, function(result){
      if(result.error == 0)
      {
        $("#syncHQProductListContent").html("Sync completed.");

        window.onbeforeunload = function () {
          // blank function do nothing
        }

        $("#syncProductListBtn").html("Sync completed").attr("disabled", false).off('click').click(function(){
          location.reload();
          // logout();
        });
      }
      else
      {
        $("#syncProductListBtn").html("Re-sync").attr("disabled", false).off('click').click(function(){
          syncProductList(create_session);
          $(this).html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);
        });

        $("#syncHQProductListContent").html("Sync failed, please sync again.");

        var message = result.message;
        if(!message)
        {
          message = "Something wrong, click Re-sync to sync again.";
        }

        Swal.fire({
          title: result.message,
          icon: 'error',
          confirmButtonText: 'OK',
        });
      }
      
    }).fail(function(){
      if(xhr.status == 401)
      {
        loggedOutAlert();
      }
      else
      {
        $("#syncProductListBtn").html("Re-sync").attr("disabled", false).off('click').click(function(){
          syncProductList(create_session);
          $(this).html("<i class='fas fa-spinner fa-spin'></i>").attr("disabled", true);
        });

        $("#syncHQProductListContent").html("Sync failed, please sync again.");

        Swal.fire({
          title: 'Something wrong, click Re-sync to sync again.',
          icon: 'error',
          confirmButtonText: 'OK',
        })
      }
    });
  }

  function run_barcode()
  {
    var run = true;
    if($("#barcode_manual").is(":checked"))
    {
      run = false;
      combined_barcode = "";
    }

    if($("#voucherModal").css("display") != "none" || $("#user_management").css("display") != "none" || $("#dailyClosingModal").css("display") != "none" || $("#numpadModal").css("display") != "none" || $("#openingModal").css("display") != "none" || $("#previous_receipt").css("display") != "none" || $("#cardCheckoutModal").css("display") != "none" || $("#cashFloatModal").css("display") != "none" || $("#openingModal").css("display") != "none" || $("#closingModal").css("display") != "none")
    {
      run = false;
      combined_barcode = "";
    }

    if(run)
    {
      // check product using barcode
      if(combined_barcode.length > 1)
      {
        $("#barcode").val(combined_barcode);
        combined_barcode = "";
        clearInterval(searchFunc);
        searchFunc = setTimeout(searchAndAddItem, 10);
      }
      // check shortcut key
      else if(combined_barcode.length == 1)
      {
        for(var a = 0; a < shortcut_key.length; a++)
        {
          if(shortcut_key[a].character)
          {
            if(shortcut_key[a].character.toLowerCase() == combined_barcode.toLowerCase())
            {
              var func_name = shortcut_key[a].function;
              func_name = func_name.replace('()','');
              window[func_name]();
            }
          }
        }
        combined_barcode = "";
      }
    }
    else
    {
      combined_barcode = "";
    }
  }

  function openDrawer(message)
  {
    var newWin = window.open('','Print-Window');

    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()" style="text-align:center;">'+message+'</body></html>');
    newWin.document.close();
    setTimeout(function(){newWin.close();},10);
  }

  function searchRelatedItem()
  {
    let barcode = $("#barcode").val();
    if(barcode != "")
    {
      $.post("{{ route('searchRelatedItem') }}", { "_token" : "{{ csrf_token() }}", "barcode" : barcode }, function(result){
        if(result.error == 0)
        {
          var related_item = result.related_item;

          total_related = related_item.length;
          selecting_related = 1;

          var html = "";
          for(var a = 0; a < related_item.length; a++)
          {
            let uom = "";
            if(related_item[a].uom)
            {
              uom = related_item[a].uom;
            }
            html += "<tr class='"+(a == 0 ? "selected" : "" )+"' barcode='"+related_item[a].barcode+"'>";
            html += "<td>"+related_item[a].barcode+"</td>";
            html += "<td>"+related_item[a].product_name+"</td>";
            html += "<td>"+uom+"</td>";
            html += "<td>RM <span style='float: right;'>"+related_item[a].price_text+"</span></td>";
            html += "</tr>";
          }

          $("#related_item").html(html);
        }
      }).fail(function(xhr){
        if(xhr.status == 401)
        {
          loggedOutAlert();
        }
      });
    }
  }

  function selectRelated()
  {
    $("#related_item tr").removeClass("selected");
    $("#related_item tr:nth-child("+selecting_related+")").addClass("selected");
  }

  function addRelatedItem()
  {
    var barcode = $("#related_item tr.selected").attr("barcode");
    $("#barcode").val(barcode);
    searchAndAddItem();

    total_related = 0;
    selecting_related = 1;
    $("#related_item").html("");
  }

  function closingReport(closing_report)
  {
    var newWin = window.open('','Print-Window');

    var html = "";
    html += "<style>tr td{ border:1px solid #000; }</style>";
    html += "<table style='width:100%;font-size:11px;border-collapse:collapse;'>";
    html += "<tr>";
    html += "<td colspan='4' style='text-align:center;font-weight:bold;vertical-align:top;border:none;'>Counter : "+closing_report.cashier_name+"</td>";
    html += "</tr>";
    html += "<tr>";
    html += "<td colspan='4' style='text-align:center;font-weight:bold;vertical-align:top;border:none;'>Opening by : "+closing_report.opening_by+"</td>";
    html += "</tr>";
    html += "<tr>";
    html += "<td colspan='4' style='text-align:center;font-weight:bold;vertical-align:top;border:none;'>"+closing_report.now+"</td>";
    html += "</tr>";
    html += "<tr>";
    html += "<td></td>";
    html += "<td style='vertical-align:top;'>Amount</td>";
    html += "<td style='vertical-align:top;'>Masa</td>";
    html += "<td style='vertical-align:top;'>Remarks</td>";
    html += "</tr>";
    html += "<tr>";
    html += "<td style='vertical-align:top;'>Modal</td>";
    html += "<td style='vertical-align:top;text-align:right;'>"+closing_report.opening+"</td>";
    html += "<td style='vertical-align:top;'>"+closing_report.opening_time+"</td>";
    html += "<td></td>";
    html += "</tr>";
    for(var a = 0; a < closing_report.cash_float.length; a++)
    {
      var cash_float = closing_report.cash_float[a];
      html += "<tr>";
      html += "<td style='vertical-align:top;'>";
      if(cash_float.type == "in")
      {
        html += "Duit masuk";
      }
      else if(cash_float.type == "out")
      {
        html += "Duit keluar";
      }
      html += "</td>";
      
      html += "<td style='vertical-align:top;text-align:right;'>"
      if(cash_float.type == "in")
      {
        html += cash_float.amount_text;
      }
      else if(cash_float.type == "out")
      {
        html += "( "+cash_float.amount_text+" )";
      }
      html += "</td>";
      
      html += "<td style='vertical-align:top;'>"+cash_float.created_time_text+"</td>";
      html += "<td style='vertical-align:top;'>"+cash_float.remarks+"</td>";
      html += "</tr>";
    }

    html += "<tr>";
    html += "<td style='vertical-align:top;'>Jumlah jualan tunai</td>";
    html += "<td style='vertical-align:top;text-align:right;'>"+closing_report.cash_sales+"</td>";
    html += "<td></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "<tr><td style='height:14px;'></td><td></td><td></td><td></td></tr>";

    html += "<tr>";
    html += "<td style='vertical-align:top;'>Jumlah tunai di drawer</td>";
    html += "<td style='vertical-align:top;text-align:right;'>"+closing_report.cash_flow+"</td>";
    html += "<td></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "<tr>";
    html += "<td style='vertical-align:top;'>Jumlah penutupan</td>";
    html += "<td style='vertical-align:top;text-align:right;'>"+closing_report.closing+"</td>";
    html += "<td style='vertical-align:top;'>"+closing_report.closing_time+"</td>";
    html += "<td></td>";
    html += "</tr>";

    html += "<tr>";
    html += "<td style='vertical-align:top;'>Baki</td>";
    html += "<td style='vertical-align:top;text-align:right;'>"+closing_report.diff+"</td>";
    html += "<td></td>";
    html += "<td></td>";
    html += "</tr>";

    html += "</table>";

    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()" style="text-align:center;">'+html+'</body></html>');
    newWin.document.close();
    setTimeout(function(){newWin.close();},10);
  }

  function serverCashFloatReport()
  {
    window.open(
      '{{ route("serverCashFloatReport") }}',
      '_blank'
    );
  }

  function loggedOutAlert()
  {
    Swal.fire({
      title: 'Your account was logged out, please login again.',
      icon: 'error',
      confirmButtonText: 'OK',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        location.reload();
      }
    })
  }

</script>


</html>
