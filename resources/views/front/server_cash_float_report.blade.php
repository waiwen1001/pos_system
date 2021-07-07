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
  .align_right { text-align: right; }
</style>

<body>

  <div style="padding: 0 30px;">
    <table style="width: 100%;border-collapse: collapse;">
      <thead>
        <tr class="no_border">
          <th colspan="4" style="text-align: center;" class="no_border">Report generate at : {{ $now }}</th>
        </tr>
        <tr>
          <th></th>
          <th>Amount</th>
          <th>Date Time</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pos_cashier_list as $pos_cashier)
          <tr class="grey2">
            <td colspan="4">{{ $pos_cashier->name }}</td>
          </tr>
          @foreach($pos_cashier->report as $report)
            <tr class="grey">
              <td colspan="4">Opening by {{ $report->opening_name }}</td>
            </tr>
            <tr>
              <td>Opening</td>
              <td class="align_right">{{ $report->opening_amount_text }}</td>
              <td class="align_right">{{ $report->opening_time }}</td>
              <td></td>
            </tr>
            @foreach($report->cash_float as $cash_float)
              <tr>
                <td>
                  @if($cash_float->type == "in")
                    Float In
                  @elseif($cash_float->type == "out")
                    Float Out
                  @endif
                </td>
                <td class="align_right">{{ $cash_float->amount_text }}</td>
                <td class="align_right">{{ $cash_float->created_time_text }}</td>
                <td>{{ $cash_float->remarks }}</td>
              </tr>
            @endforeach

            <tr>
              <td>Cash Sales</td>
              <td class="align_right">{{ $report->total_cash }}</td>
              <td></td>
              <td></td>
            </tr>

            <tr>
              <td colspan="4"><br></td>
            </tr>

            <tr>
              <td>Total Cash Flow</td>
              <td class="align_right">{{ $report->cash_flow }}</td>
              <td></td>
              <td></td>
            </tr>

            @if($report->closing == 1)
              <tr>
                <td>Closing</td>
                <td class="align_right">{{ $report->closing_amount_text }}</td>
                <td class="align_right">{{ $report->closing_time }}</td>
                <td></td>
              </tr>

              <tr>
                <td>Different</td>
                <td class="align_right">{{ $report->diff_amount_text }}</td>
                <td></td>
                <td></td>
              </tr>
            @endif
            <tr>
              <td colspan="4"><br></td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>

</body>

</html>