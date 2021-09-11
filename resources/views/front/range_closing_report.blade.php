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

<body style="background: #eee;">
  
  <div class="row">
    <div class="col-12">
      <h4 style="padding: 10px; text-align: center; background: #fff;">Previous Closing Report</h4>
    </div>
  </div>
  <div style="padding: 10px">
    <div class="row">
      <div class="col-12">
        <form method="GET" action="{{ route('getRangeClosingReport') }}">
          <div class="row">
            <div class="col-6">
              <div class="form-group">
                <label>Date from</label>
                <input type="date" class="form-control" name="date_from" value="{{ $date_from }}" />
              </div>
            </div>
            <div class="col-6">
              <div class="form-group">
                <label>Date from</label>
                <input type="date" class="form-control" name="date_to" value="{{ $date_to }}" />
              </div>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-success" style="float: right;">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <hr style="border-color: #999;" />

    <div style="background: #fff; padding: 10px;">
      <table id="session_list_table" class="table table-bordered table-striped" cellspacing="0" width="100%" style="width: 100% !important;">
        <thead>
          <th>Opening date</th>
          <th>Closing date</th>
          <th>Detail</th>
        </thead>
        <tbody>
          @foreach($session_list as $session)
            <tr>
              <td>{{ date('Y M d h:i A', strtotime($session->opening_date_time)) }}</td>
              <td>{{ date('Y M d h:i A', strtotime($session->closing_date_time)) }}</td>
              <td>
                <a href="{{ route('getDailyReport', ['reprint' => 1, 'session_id' => $session->id]) }}" class="btn btn-primary">Detail</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</body>

<script>

  var session_list_table = $("#session_list_table").DataTable();

  $(document).ready(function(){

  });

</script>

</html>

