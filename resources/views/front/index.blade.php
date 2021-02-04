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
                      <td>
                        <div class="quantity">
                          <i class="fa fa-minus" onclick="editQuantity(this, 'plus', '{{ $item->id }}')"></i>
                          <label>{{ $item->quantity }}</label>
                          <i class="fa fa-plus" onclick="editQuantity(this, 'minus', '{{ $item->id }}')"></i>
                        </div>
                      </td>
                      <td class="subtotal">RM {{ number_format($item->subtotal, 2) }}</td>
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
              <div>RM</div>
              <div class="summary_price" id="price">{{ $subtotal }}</div>
            </div>

            <div class="summary-detail">
              <label>Discount <i style="display: {{ $have_discount == 0 ? 'none' : '' }};" class="fa fa-trash remove_voucher" id="remove_voucher"></i></label>
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
          <input type="text" class="form-control" placeholder="Bar Code Scanner & Product Code" id="barcode" />

          <div class="checkbox icheck" style="display: inline-block; margin-left: 10px;">
            <label>
              <input class="form-check-input" type="checkbox" name="barcode_manual" value="1" id="barcode_manual" /> Manual keyin
            </label>
          </div>

        </div>
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
              <a class="dropdown-item" href="#" onclick="showClosing()">Logout</a>
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
                <button class="btn btn-dark" id="voucherBtn" onclick="showVoucher()">Voucher</button>
              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="previousReceiptBtn">Previous Receipt</button>
              </div>

              <div class="col-4">
                <div class="dropup">
                  <button class="btn btn-dark dropdown-toggle" type="button" id="otherDropDownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Other
                  </button>
                  <div class="dropdown-menu" aria-labelledby="otherDropDownBtn">
                    <button class="dropdown-item" id="openingBtn" href="#" {{ $opening == 0 ? '' : 'disabled' }}>Opening</button>
                    <button class="dropdown-item" id="closingBtn" href="#" {{ $opening == 1 ? '' : 'disabled' }}>Closing</button>
                  </div>
                </div>

              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="cashCheckoutBtn">Cash Checkout</button>
              </div>

              <div class="col-4">
                <div class="dropup">
                  <button class="btn btn-dark dropdown-toggle" type="button" id="paymentTypeBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Payment type
                  </button>
                  <div class="dropdown-menu" aria-labelledby="paymentTypeBtn">
                    <button class="dropdown-item cardPayment" payment_type="debit_card" href="#">Debit card</button>
                    <button class="dropdown-item cardPayment" payment_type="credit_card" href="#">Credit card</button>
                    <button class="dropdown-item cardPayment" payment_type="e-wallet" href="#">E-wallet</button>
                  </div>
                </div>

              </div>

              <div class="col-4">
                <button class="btn btn-dark" id="clearBtn" onclick="clearTransaction()">Clear</button>
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
                <th>Invoice No</th>
                <th>Payment type</th>
                <th>Reference No</th>
                <th>Total</th>
                <th>Received payment</th>
                <th>Balance</th>
                <th>Void</th>
                <th>Transaction date</th>
                <th>Print</th>
              </tr>
            </thead>
            <tbody>
              @foreach($completed_transaction as $completed)
                <tr transaction_id="{{ $completed->id }}">
                  <td>{{ $completed->transaction_no }}</td>
                  <td>{{ $completed->payment_type }}</td>
                  <td>
                    <p class="invoice_no">{{ $completed->invoice_no }}</p>
                    @if($completed->payment_type != "cash")
                      <a href="#" onclick="editInvoiceNo('{{ $completed->id }}', '{{ $completed->invoice_no }}')">Edit</a>
                    @endif
                  </td>
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
                  <td>
                    <button class="btn btn-success" onclick="printReceipt('{{ $completed->transaction_no }}', '{{ number_format($completed->total, 2) }}')">Print</button>
                  </td>
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

  <div id="receipt">
    <div>Transaction No : <span id="receipt_transaction_no"></span></div>
    <div>Total : <span id="receipt_total"></span></div>
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
          <input type="text" class="form-control" name="invoice_no" />
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
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitVoucher">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editInvoiceNoModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceNoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editInvoiceNoModalLabel">Edit Reference No</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" name="edit_invoice_no" />
          <span class="invalid-feedback" role="alert"></span>
        </div>
        <div class="modal-footer">
          <input type='hidden' name='edit_transaction_no' />
          <button type="button" class="btn btn-success" id="submitEditInvoiceNo">Submit</button>
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
            <label>Email</label>
            <input type="text" class="form-control" name="manager_email" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="manager_password" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <div class="form-group">
            <label>Cashier cash</label>
            <input type="text" class="form-control" name="daily_closing_amount" />
            <span class="invalid-feedback" role="alert"></span>
          </div>

          <span id="dailyClosingFeedback" class="invalid-feedback" role="alert"></span>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="submitDailyClosing" onclick="submitDailyClosing()">Submit</button>
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
  
  var numpad_using_type = "numpad";
  var numpad_prefill = 0;
  var searchFunc;
  var voucherFunc;

  var transaction_total = "{{ $real_total }}";
  var opening = "{{ $opening }}";

  var previous_receipt_table = $("#previous_receipt_table").DataTable( {
    pageLength: 25,
    scrollY: '60vh',
    scrollCollapse: true,
    // responsive: true,
    order: [[ 7, "desc" ]]
  });

  $(document).ready(function(){

    $('.form-check-input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' /* optional */
    });

    $("#barcode").on('keydown', function(e){
      clearInterval(searchFunc);
      // enter
      // delay 50ms because keydown will not capture on change
      if($("#barcode_manual").is(":checked") == false)
      {
        if(e.which != 17){
          searchFunc = setTimeout(searchAndAddItem, 200);
        }
      }
      else
      {
        if(e.which == 13)
        {
          searchFunc = setTimeout(searchAndAddItem, 200);
        }
      }
    });

    $("input[name='voucher_code']").on('keydown', function(e){
      clearInterval(voucherFunc);
      if(e.which != 17){
        voucherFunc = setTimeout(submitVoucher, 200);
      }
    });

    $("#deleteSubmit").click(function(){
      var item_id = $("#delete_item_id").val();
      submitDeleteItem(item_id);
    });

    $("#cashCheckoutBtn").click(function(){
      $("input[name='received_payment']").val(0);
      numpad_using_type = "numpad";
      numpad_prefill = 0;

      showNumPad();
    });

    $(".cardPayment").click(function(){
      $("input[name='invoice_no']").removeClass("is-invalid");

      var payment_type = $(this).attr("payment_type");
      $("#payment_type").val(payment_type);
      showInvoiceInput();
    });

    $(".numpad_number_btn").click(function(){
      var type = $(this).attr("type");
      var number = $(this).attr("number");
      // type 1 = 0 to 9
      // type 2 = 100, 50, 10, 5

      $("input[name='received_payment']").removeClass("is-invalid");

      if(type == 1)
      {
        console.log(numpad_using_type);
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
    });

    $(".numpad_btn.clear, .numpad_btn.exit").click(function(){
      numpad_using_type = "numpad";
      numpad_prefill = 0;
      $("input[name='received_payment']").val(0);
    });

    $(".numpad_btn.submit").click(function(){
      submitCashPayment();
    });

    $("#previousReceiptBtn").click(function(){
      $("#previous_receipt").show();

      setTimeout(function(){
        previous_receipt_table.draw();
      }, 50);
    });

    $(".close_full_page").click(function(){
      $(".full_page").hide();
    });

    $("#submitCardPayment").click(function(){
      var invoice_no = $("input[name='invoice_no']").val();
      if(invoice_no)
      {
        submitCardPayment();
      }
      else
      {
        $("input[name='invoice_no']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Reference No cannot be empty.</strong>");
      }
    });

    $("#submitEditInvoiceNo").click(function(){

      var edit_invoice_no = $("input[name='edit_invoice_no']").val();
      if(edit_invoice_no)
      {
        submitEditInvoiceNo();
      }
      else
      {
        $("input[name='edit_invoice_no']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Reference No cannot be empty.</strong>");
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

    if(opening == 0)
    {
      $("#openingModal").modal('show');
      disablePosSystem();
    }

    $("#openingBtn").click(function(){
      $("#openingModal").modal('show');
    });

    $("#closingBtn").click(function(){
      $("#dailyClosingFeedback").hide();
      $("#dailyClosingModal").modal('show');
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
      html += "<td>";
      html += "<div class='quantity'>";
      html += "<i class='fa fa-minus' onclick='editQuantity(this, \"plus\", \""+item_detail.id+"\")'></i>";
      html += "<label>"+item_detail.quantity+"</label>";
      html += "<i class='fa fa-plus' onclick='editQuantity(this, \"minus\", \""+item_detail.id+"\")'></i>";
      html += "</div>";
      html += "</td>";
      html += "<td>RM "+item_detail.subtotal_text+"</td>";
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
        transaction_total = transaction_summary.real_total;

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

    if(parseFloat(received_cash) <= 0)
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Cannot submit as RM 0.00</strong>");
      return;
    }

    if(parseFloat(transaction_total) <= 0)
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Transaction total is RM 0.00</strong>");
      return;
    }

    if(parseFloat(received_cash) < parseFloat(transaction_total))
    {
      $("input[name='received_payment']").addClass("is-invalid");
      $("input[name='received_payment']").siblings(".invalid-feedback").html("<strong>Received cash is lesser than transaction price</strong>");
      return;
    }

    var transaction_id = $("#transaction_id").val();

    $.post("{{ route('submitTransaction') }}", {"_token" : "{{ csrf_token() }}", "received_cash" : received_cash, "transaction_id" : transaction_id, "payment_type" : "cash" }, function(result){

      if(result.error == 0)
      {
        $("#numpadModal").modal('hide');

        $("#completedTransactionModal").modal('show');
        
        $("#transaction_balance").html(result.balance);

        transactionCompleted(result.completed_transaction.transaction_no, result.completed_transaction.total, 1);

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

      if(transaction_id)
      {
        $.post("{{ route('clearTransaction') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id}, function(result){
          if(result.error == 0)
          {
            transaction_total = 0;

            $("#items-table tbody").html("");
            $("#price, #discount, #total, #round_off").html("0.00");
            $("#round_off_box").hide();
            $("#remove_voucher").hide();

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
      $("#price, #discount, #total, #round_off").html("0.00");
      $("#remove_voucher").hide();
      $("#round_off_box").hide();
    }
  }

  function prependCompletedTransaction(completed_transaction)
  {
    console.log(completed_transaction);

    var void_html = "<div class='void_column' transaction_id="+completed_transaction.id+">";
    void_html += "<button type='button' class='btn btn-danger' onclick='voidTransaction(\""+completed_transaction.id+"\")'>Void</button>";
    void_html += "</div>";

    var data = "<tr transaction_id="+completed_transaction.id+">";
    data += "<td>"+completed_transaction.transaction_no+"</td>";
    data += "<td>"+completed_transaction.payment_type+"</td>";
    data += "<td>";
    if(completed_transaction.payment_type != "cash")
    {
      data += "<p class='invoice_no'>"+completed_transaction.invoice_no+"</p>";
      data += "<a href='#' onclick='editInvoiceNo(\""+completed_transaction.id+"\", \""+completed_transaction.invoice_no+"\")'>Edit</a>";
    }
    data += "</td>";
    data += "<td>"+completed_transaction.total_text+"</td>";
    data += "<td>"+completed_transaction.payment_text+"</td>";
    data += "<td>"+completed_transaction.balance_text+"</td>";
    data += "<td>"+void_html+"</td>";
    data += "<td data-order='"+completed_transaction.transaction_date+"'>"+completed_transaction.transaction_date_text+"</td>";

    data += '<td><div class="btn btn-success print_receipt" transaction_no=\''+completed_transaction.transaction_no+'\' total=\''+completed_transaction.total_text+'\'>Print</div></td>';

    data += "</tr>";

    previous_receipt_table.row.add($(data)).node();

    previous_receipt_table.draw();
    // previous_receipt_table.responsive.recalc();

    $(".print_receipt").click(function(){
      var transaction_no = $(this).attr("transaction_no");
      var total_text = $(this).attr("total");

      printReceipt(transaction_no, total_text);
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

  function transactionCompleted(transaction_no, total, show_balance)
  {
    printReceipt(transaction_no, total);
    if(show_balance === 1)
    {
      $("#completed_balance").show();
    }
    else
    {
      $("#completed_balance").hide();
    }
  }

  function printReceipt(transaction_no, total)
  {
    $("#receipt_transaction_no").html(transaction_no);
    $("#receipt_total").html(total);

    var receiptPrint = document.getElementById('receipt');
    var newWin = window.open('','Print-Window');

    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()">'+receiptPrint.innerHTML+'</body></html>');
    newWin.document.close();

    setTimeout(function(){newWin.close();},10);
  }

  function showInvoiceInput()
  {
    $("#cardCheckoutModal").modal('show');
  }

  function submitCardPayment()
  {
    var transaction_id = $("#transaction_id").val();
    var invoice_no = $("input[name='invoice_no']").val();
    var payment_type = $("#payment_type").val();

    $.post("{{ route('submitTransaction') }}", {"_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id, "payment_type" : payment_type, "invoice_no" : invoice_no }, function(result){

      $("#cardCheckoutModal").modal('hide');
      if(result.error == 0)
      {
        $("#completedTransactionModal").modal('show');

        transactionCompleted(result.completed_transaction.transaction_no, result.completed_transaction.total, 0);
        $("input[name='invoice_no']").val("");

        submitClearTransaction(0);
        prependCompletedTransaction(result.completed_transaction);
      }
      else
      {
        alert("Error");
      }

    });
  }

  function editInvoiceNo(transaction_id, invoice_no)
  {
    $("input[name='edit_transaction_no']").val(transaction_id);
    $("input[name='edit_invoice_no']").val(invoice_no);

    $("#editInvoiceNoModal").modal('show');
  }

  function submitEditInvoiceNo()
  {
    var transaction_id = $("input[name='edit_transaction_no']").val();
    var edit_invoice_no = $("input[name='edit_invoice_no']").val();

    $.post("{{ route('editInvoiceNo') }}", { "_token" : "{{ csrf_token() }}", "transaction_id" : transaction_id, "invoice_no" : edit_invoice_no }, function(result){

      if(result.error == 0)
      {
        $("#editInvoiceNoModal").modal('hide');
        $("#previous_receipt_table tbody tr[transaction_id="+transaction_id+"]").find(".invoice_no").html(edit_invoice_no);
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
          $("#items-table tbody tr[item_id="+item_id+"] td.subtotal").html(result.subtotal);

          let transaction_summary = result.transaction_summary;
          transaction_total = transaction_summary.real_total;

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
        }
        else
        {
          $("#items-table tbody tr[item_id="+item_id+"]").remove();
        }
        
      }
    });
  }

  function showVoucher()
  {
    $("#voucherModal").modal('show');

    setTimeout(function(){
      $("input[name='voucher_code']").focus();
    }, 500);
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
    })
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
    });
  }

  function disablePosSystem()
  {
    $("#barcode, #voucherBtn, #cashCheckoutBtn, #paymentTypeBtn, #clearBtn, #previousReceiptBtn").attr("disabled", true);
  }

  function enablePosSystem()
  {
    $("#barcode, #voucherBtn, #cashCheckoutBtn, #paymentTypeBtn, #clearBtn, #previousReceiptBtn").attr("disabled", false);
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
          $("#closingBtn").attr("disabled", false);
        }
      });
    }
    else
    {
      $("input[name='cashier_opening_amount']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Opening amount cannot be empty.</strong>");
    }
  }

  function showClosing()
  {
    if(opening == 0)
    {
      logout();
    }
    else
    {
      $("input[name='daily_closing_amount']").removeClass("is-invalid");
      $("#closingModal").modal('show');
    }
  }

  function submitClosing()
  {
    var closing_amount = $("input[name='cashier_closing_amount']").val();

    if(closing_amount)
    {
      $.post("{{ route('submitClosing') }}", {"_token" : "{{ csrf_token() }}", "closing_amount" : closing_amount}, function(result){
        if(result.error == 0)
        {
          $("#closingModal").modal('hide');
          logout();
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
    $("#dailyClosingFeedback").hide();

    $("input[name='manager_email'], input[name='manager_password'], input[name='daily_closing_amount']").removeClass("is-invalid");

    var manager_email = $("input[name='manager_email']").val();
    var manager_password = $("input[name='manager_password']").val();
    var daily_closing_amount = $("input[name='daily_closing_amount']").val();

    var proceed = 1;

    if(!manager_email)
    {
      $("input[name='manager_email']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Email cannot be empty.</strong>");
      proceed = 0;
    }

    if(!manager_password)
    {
      $("input[name='manager_password']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Password cannot be empty.</strong>");
      proceed = 0;
    }

    if(!daily_closing_amount)
    {
      $("input[name='daily_closing_amount']").addClass("is-invalid").siblings(".invalid-feedback").html("<strong>Closing amount cannot be empty.</strong>");
      proceed = 0;
    }

    if(proceed == 0)
    {
      return;
    }

    $.post("{{ route('submitDailyClosing') }}", {"_token" : "{{ csrf_token() }}", "email" : manager_email, "password" : manager_password, "closing_amount" : daily_closing_amount}, function(result){
      if(result.error == 0)
      {
        $("#dailyClosingModal").modal('hide');
        disablePosSystem();

        $("#openingBtn").attr("disabled", false);
        $("#closingBtn").attr("disabled", true);

        $("#daily_closing_content").html("This cashier are now closed.");
        $("#daily_closing_toast").toast('show');

        opening = 0;
      }
      else
      {
        $("#dailyClosingFeedback").html("<strong>"+result.message+".</strong>").show();
      }
    });
  }

</script>


</html>
