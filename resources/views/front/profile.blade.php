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
  
  .hide { display: none; }
  input.error { border: 1px solid red; }

</style>

<body>
  
  <div class="container" style="margin-top: 20px;">
    <div class="row">
      <div class="col-12">
        <form method="POST" action="{{ route('updateProfile') }}">
          @csrf
          <div class="form-group">
            <label>Invoice Sequence</label>
            <input type="text" class="form-control" name="branch_code" value="{{ $branch_code }}" />
          </div>

          <div class="form-group">
            <label>Branch Name</label>
            <input type="text" class="form-control" name="branch_name" value="{{ $branch_name }}" />
          </div>

          <div class="form-group">
            <label>Branch Address</label>
            <textarea class="form-control" name="branch_address" rows=5>{{ $branch_address }}</textarea>
          </div>

          <button type="submit" class="btn btn-success">Save</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>