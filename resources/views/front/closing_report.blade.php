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

<style>
  th, tr, td { border: 1px solid #000; }
  .no_border { border: none; }
  .grey { background: #eee; }
  .grey2 { background: #ddd; }
  .align_right { text-align: right; padding-right: 10px; }
</style>

<body>

  @if($reprint == 1)
    <h4 style="text-align: center;">Reprint Daily Report</h4>
  @endif

  <table style="width: 85%; margin: auto;">
    <thead>
      <tr>
        <th colspan="11">
          <p style="font-weight: normal; font-size: 16px; margin: 0px; text-align: center;">Total Sales Report</p>
        </th>
      </tr>
      <tr>
        <th colspan="11" style="text-align: center;">Tarikh : {{ $now }}</th>
      </tr>
    </thead>
    <tbody>
      <tr style="text-align: center;">
        <td></td>
        <td>Kutipan<br>Tunai</td>
        <td>Credit /<br>debit Kad</td>
        <td>Touch<br>& Go</td>
        {{-- <td>Maybank<br>QR code</td> --}}
        <td>Grab<br>Pay</td>
        <td>Cheque</td>
        <td>Boost & Shopee & Other</td>
        <td>E-banking</td>
        <!-- <td>Cek /<br>Lain lain</td> -->
        <td>Panda Mart</td>
        <td>Grab Mart</td>
        <td>Jumlah<br>Jualan</td>
      </tr>
      @foreach($pos_cashier as $cashier)
        <tr>
          <td>{{ $cashier->cashier_name }}</td>
          <td class='align_right'>{{ $cashier->cash }}</td>
          <td class='align_right'>{{ $cashier->card }}</td>
          <td class='align_right'>{{ $cashier->tng }}</td>
          {{-- <td class='align_right'>{{ $cashier->maybank_qr }}</td> --}}
          <td class='align_right'>{{ $cashier->grab_pay }}</td>
          <td class='align_right'>{{ $cashier->cheque }}</td>
          <td class='align_right'>{{ $cashier->boost }}</td>
          <td class='align_right'>{{ $cashier->ebanking }}</td>
          <!-- <td class='align_right'>{{ $cashier->other }}</td> -->
          <td class='align_right'>{{ $cashier->pandamart }}</td>
          <td class='align_right'>{{ $cashier->grabmart }}</td>
          <td class='align_right'>{{ $cashier->total }}</td>
        </tr>
      @endforeach

      <tr>
        <td style="height: 27px;"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
      </tr>

      <tr>
        <td>Jumlah</td>
        @foreach($payment_type_result as $payment_type)
          <td class='align_right'>{{ $payment_type->total }}</td>
        @endforeach
        <td class='align_right'>{{ $total_sales_text }}</td>
      </tr>

      <tr class="no_border">
        <td colspan="4" style="text-align: center; font-weight: bold;">Tambah</td>
        <td colspan="3" class="no_border"></td>
        <td colspan="4" style="text-align: center; font-weight: bold;">Tolak</td>
      </tr>

      <tr class="no_border">
        <td colspan="3">Modal di kedai Sebelum Niaga :</td>
        <td class='align_right'>{{ $cash_float_result->opening }}</td>
        <td colspan="3" class="no_border"></td>
        <td colspan="3">Pembelanjaan :</td>
        <td class='align_right'>{{ $cash_float_result->float_out }}</td>
      </tr>

      <tr class="no_border">
        <td colspan="3">Penambahan Tunai :</td>
        <td class='align_right'>{{ $cash_float_result->float_in }}</td>
        <td colspan="3" class="no_border"></td>
        <td colspan="3">Baki Modal di kedai :</td>
        <td class='align_right'>{{ $cash_float_result->opening }}</td>
      </tr>

      <tr class="no_border">
        <td colspan="3">Jualan Tunai hari ini :</td>
        <td class='align_right'>{{ $cash_float_result->cash_sales }}</td>
        <td colspan="3" class="no_border"></td>
        <td colspan="3">Jumlah refund :</td>
        <td class='align_right'>{{ $cash_float_result->total_refund }}</td>
      </tr>

      @if($cash_float_result->diff != 0)
        <tr class="no_border">
          <td colspan="3">Berbeza Jumlah Closing</td>
          <td class='align_right'>{{ $cash_float_result->diff_text }}</td>
          
          <td colspan="3" class="no_border"></td>
          <td colspan="3"></td>
          <td class='align_right'></td>
        </tr>
      @endif

      <tr class="no_border">
        <td colspan="2">Jumlah Tunai :</td>
        <td></td>
        <td class='align_right'>{{ $cash_float_result->total_cash }}</td>
        <td colspan="3" class="no_border"></td>
        <td colspan="2">Jumlah :</td>
        <td></td>
        <td class='align_right'>{{ $cash_float_result->total_deduct }}</td>
      </tr>

      <tr class="no_border">
        <td class="no_border" colspan="10" style="height: 27px;"></td>
      </tr>

      <!-- @if($cash_float_result->total_boss > 0)
        <tr class="no_border" style="font-weight: bold;">
          <td class="no_border" colspan="5" style="text-align: right;">Jumlah telah bagi ke ketua : </td>
          <td class="no_border" style="text-align: right;">{{ $cash_float_result->total_boss_text }}</td>
          <td class="no_border" colspan="3"></td>
        </tr>
      @endif -->

      <tr class="no_border" style="font-weight: bold;">
        <td class="no_border" colspan="5" style="text-align: right;">Baki Tunai yang perlu serah ke syarikat : </td>
        <td colspan="3" class="no_border" style="text-align: right;">{{ $cash_float_result->cash_to_company }}</td>
        <td class="no_border" colspan="3"></td>
      </tr>
    </tbody>
  </table>

  <p style="page-break-before: always">

  <div class="row" style="width: calc(85% + 30px); margin: auto;">
    @foreach($pos_cashier_list as $pos_cashier_detail)
      <div class="col-6" style="clear: both; margin-bottom: 27px;">
        <table style="width: 100%;" class="print-friendly">
          @foreach($pos_cashier_detail->shift as $shift)
            <tr style="text-align: center;">
              <td colspan="4">{{ $pos_cashier_detail->cashier_name }}</td>
            </tr>
            <tr style="text-align: center;">
              <td colspan="4">Shift {{ $shift->shift_count }}</td>
            </tr>
            <tr style="text-align: center;">
              <td colspan="4">Opening by <b>{{ $shift->opening_by_name }}</b></td>
            </tr>
            <tr style="text-align: center;">
              <td colspan="4">{{ $shift->opening_date_time_text }}</td>
            </tr>
            <tr style="text-align: center;">
              <td colspan="2" style="font-weight: bold;">Shift {{ $shift->shift_count }}</td>
              <td>Amount</td>
              <td>Masa</td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Modal :</td>
              <td class='align_right'>{{ number_format($shift->opening_amount, 2) }}</td>
              <td class='align_right'>{{ date('h:i A', strtotime($shift->opening_date_time)) }}</td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Duit Masuk :</td>
              <td class='align_right'>{{ number_format($shift->float_in, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Duit Bayar :</td>
              <td class='align_right'>{{ number_format($shift->float_out, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Jumlah refund :</td>
              <td class='align_right'>{{ number_format($shift->refund, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Tunai - shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->cash_sales, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Jumlah Tunai di Drawer :</td>
              <td class='align_right'>{{ number_format($shift->drawer_cash, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Bagi Ke Ketua :</td>
              <td class='align_right'>{{ number_format($shift->boss_cash, 2) }}</td>
              <td></td>
            </tr>
            <tr>
              <td class='align_right' colspan="2">Baki di Drawer :</td>
              <td class='align_right'>{{ number_format($shift->remain, 2) }}</td>
              <td></td>
            </tr>
            @if($shift->diff != 0)
              <tr>
                <td class='align_right' colspan="2">Tutup Kaunter :</td>
                <td class='align_right'>{{ number_format($shift->closing_amount, 2) }}</td>
                <td></td>
              </tr>
              <tr>
                <td class='align_right' colspan="2">Berbeza :</td>
                <td class='align_right'>{{ number_format($shift->diff, 2) }}</td>
                <td></td>
              </tr>
            @endif

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Kredit Kad - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->card_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Touch & Go - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->tng_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Maybank QR - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->maybank_qr_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Grab Pay - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->grab_pay_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Cheque - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->cheque_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan Boost - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->boost_sales, 2) }}</td>
              <td></td>
            </tr>

            <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan E-banking - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->ebanking_sales, 2) }}</td>
              <td></td>
            </tr>

            <!-- <tr>
              <td class='align_right' colspan="2" style="font-weight: bold;">Jumlah Jualan lain-lain - Shift {{ $shift->shift_count }} :</td>
              <td class='align_right'>{{ number_format($shift->other_sales, 2) }}</td>
              <td></td>
            </tr> -->

            <tr>
              <td colspan="4" style="height: 27px;"></td>
            </tr>
          @endforeach
          <tr style="border: none;">
            <td style="border: none; height: 27px;" colspan="4"></td>
          </tr>
          <tr>
            <td colspan="4" style="padding: 0 10px;">
              <span>Tunai serah ke Ketua sebelum tutup kedai</span>
              <span style="float: right;">{{ number_format($pos_cashier_detail->final_remain, 2) }}</span>
            </td>
          </tr>
          <tr style="border: none;">
            <td colspan="4" style="height: 27px;border: none;"></td>
          </tr>
          @if(count($pos_cashier_detail->cash_float) > 0 || count($pos_cashier_detail->refund_list) > 0)
            <tr>
              <td class='align_right'>Jenis</td>
              <td class='align_right'>Remarks</td>
              <td class='align_right'>Amount</td>
              <td class='align_right'>Masa</td>
            </tr>
            @foreach($pos_cashier_detail->cash_float as $cash_float)
              <tr>
                <td class='align_right'>
                  @if($cash_float->type == 'in')
                    Float In <br>( by : {{ $cash_float->created_by }} )
                  @elseif($cash_float->type == 'out')
                    Float Out <br>( by : {{ $cash_float->created_by }} )
                  @elseif($cash_float->type == 'boss')
                    Bagi Ke Ketua <br>( by : {{ $cash_float->created_by }} )
                  @endif
                </td>
                <td class='align_right'>{{ $cash_float->remarks }}</td>
                <td class='align_right'>{{ $cash_float->amount }}</td>
                <td class='align_right'>{{ date('h:i A', strtotime($cash_float->created_at)) }}</td>
              </tr>
            @endforeach

            @foreach($pos_cashier_detail->refund_list as $refund)
              <tr>
                <td class='align_right'>
                  Refund <br>( by : {{ $refund->user_name }} )
                </td>
                <td class='align_right'>{{ $refund->transaction_no }}</td>
                <td class='align_right'>{{ $refund->total }}</td>
                <td class='align_right'>{{ date('h:i A', strtotime($refund->created_at)) }}</td>
              </tr>
            @endforeach
          @endif
        </table>
      </div>
    @endforeach
    
  </div>

</body>

<script>
  
  $(document).ready(function(){
    window.print();
  });

</script>

</html>