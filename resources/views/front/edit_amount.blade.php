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
  
  .cancel_amount { color: red; }

</style>

<body style="background: #eee;">
  <div class="row">
    <div class="col-12">
      <h4 style="padding: 10px; text-align: center; background: #fff;">Previous Closing Report</h4>
    </div>
  </div>
  <div style="padding: 10px">
    <div class="row">
      <div class="col-12">
        <form method="POST" action="{{ route('updateEditAmount') }}">
          @csrf
          @foreach($cashier_list as $cashier)
            <div style="box-shadow: 0px 1px 5px 1px #ccc; background: #fff; padding: 10px; border-radius: 3px; margin-bottom: 10px;">
              <label> Counter name : 
                <b>
                  @if($cashier->cashier_name)
                    {{ $cashier->cashier_name }}
                  @elseif($cashier->ip)
                    {{ $cashier->ip }}
                  @endif
                </b>
                <br>
                Opening by : <b>{{ $cashier->opening_by_name }}</b>
                <br>
                Opening date : <b>{{ date('d M Y h:i A', strtotime($cashier->opening_date_time)) }}</b>
              </label>
              <table class="table" width="100%" style="width: 100% !important; margin: 0px; border-bottom: 1px solid #ccc;">
                <thead>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Remarks</th>
                  <th>Edit</th>
                </thead>
                <tbody>
                  <tr>
                    <td style="vertical-align: middle;">Opening</td>
                    <td style="vertical-align: middle;">
                      <span>{{ $cashier->opening_amount }}</span>
                      <input style="display: none;" class="form-control" type="number" value="{{ $cashier->opening_amount }}" name="opening_{{ $cashier->id }}" />
                    </td>
                    <td style="vertical-align: middle;"></td>
                    <td style="vertical-align: middle;">
                      <a href="#" class="edit_amount" type="opening">Edit</a>
                      <a href="#" class="cancel_amount" type="opening" style="display: none;">Cancel</a>

                      <input type="hidden" name="cashier_opening[]" value="{{ $cashier->id }}" />
                      <input type="hidden" name="opening_edit_{{ $cashier->id }}" value="0" />
                    </td>
                  </tr>

                  @foreach($cashier->cash_float as $cash_float)
                    <tr>
                      <td style="vertical-align: middle;">
                        @if($cash_float->type == "out")
                          Cash float ( Out )
                        @elseif($cash_float->type == "in")
                          Cash float ( In )
                        @elseif($cash_float->type == "boss")
                          Bagi ke ketua
                        @endif
                      </td>
                      <td style="vertical-align: middle;">
                        <span>{{ $cash_float->amount }}</span>
                        <input style="display: none;" class="form-control" type="number" value="{{ $cash_float->amount }}" name="amount_{{ $cash_float->id }}" />
                      </td>
                      <td style="vertical-align: middle;">
                        <span>{{ $cash_float->remarks }}</span>
                        <input style="display: none;" class="form-control" type="text" value="{{ $cash_float->remarks }}" name="remarks_{{ $cash_float->id }}" />
                      </td>
                      <td style="vertical-align: middle;">
                        <a href="#" class="edit_amount" type="cash_float">Edit</a>
                        <a href="#" class="cancel_amount" type="cash_float" style="display: none;">Cancel</a>
                        <input type="hidden" name="cash_float[]" value="{{ $cash_float->id }}" />
                        <input type="hidden" name="cash_float_edit_{{ $cash_float->id }}" value="0" />
                      </td>
                    </tr>
                  @endforeach

                  @if($cashier->closing == 1)
                    <tr>
                      <td style="vertical-align: middle;">Closing</td>
                      <td style="vertical-align: middle;">
                        <span>{{ $cashier->closing_amount }}</span>
                        <input style="display: none;" class="form-control" type="number" value="{{ $cashier->closing_amount }}" name="closing_{{ $cashier->id }}" />
                      </td>
                      <td style="vertical-align: middle;"></td>
                      <td style="vertical-align: middle;">
                        <a href="#" class="edit_amount" type="closing">Edit</a>
                        <a href="#" class="cancel_amount" type="closing" style="display: none;">Cancel</a>

                        <input type="hidden" name="cashier_closing[]" value="{{ $cashier->id }}" />
                        <input type="hidden" name="closing_edit_{{ $cashier->id }}" value="0" />
                      </td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          @endforeach

          <button class="btn btn-success" type="submit">Update</button>
        </form>
      </div>
    </div>
  </div>
</body>

<script>
  
  $(document).ready(function(){
    $(".edit_amount").click(function(){
      $(this).parents().eq(1).find("input").show();
      $(this).parents().eq(1).find("span").hide();

      $(this).hide();
      $(this).siblings(".cancel_amount").show();

      var type = $(this).attr("type");
      if(type == "opening")
      {
        var cashier_id = $(this).siblings("input[name='cashier_opening[]']").val();
        $("input[name='opening_edit_"+cashier_id+"']").val(1);
      }
      else if(type == "closing")
      {
        var cashier_id = $(this).siblings("input[name='cashier_closing[]']").val();
        $("input[name='closing_edit_"+cashier_id+"']").val(1);
      }
      else if(type == "cash_float")
      {
        var cashier_id = $(this).siblings("input[name='cash_float[]']").val();
        $("input[name='cash_float_edit_"+cashier_id+"']").val(1);
      }
    });

    $(".cancel_amount").click(function(){
      $(this).parents().eq(1).find("input").hide();
      $(this).parents().eq(1).find("span").show();

      $(this).hide();
      $(this).siblings(".edit_amount").show();

      var type = $(this).attr("type");
      if(type == "opening")
      {
        var cashier_id = $(this).siblings("input[name='cashier_opening[]']").val();
        $("input[name='opening_edit_"+cashier_id+"']").val(0);
      }
      else if(type == "closing")
      {
        var cashier_id = $(this).siblings("input[name='cashier_closing[]']").val();
        $("input[name='closing_edit_"+cashier_id+"']").val(0);
      }
      else if(type == "cash_float")
      {
        var cashier_id = $(this).siblings("input[name='cash_float[]']").val();
        $("input[name='cash_float_edit_"+cashier_id+"']").val(0);
      }
    });
  });

</script>

</html>