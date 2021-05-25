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
        <form method="POST" action="{{ route('saveShortcutKey') }}" id="saveShortcutKeyForm">
          @csrf
          <table class="table table-bordered" id="key_table">
            <thead class="thead-dark">
              <tr>
                <th style="width: 60%;">Function name</th>
                <th style="width: 10%;">Code</th>
                <th style="width: 10%;">Character</th>
                <th style="width: 20%;">Edit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($front_function_list as $front)
                <tr>
                  <td>{{ $front['function_name'] }}</td>
                  <td>
                    <p>{{ $front['code'] }}</p>
                    <input type="text" input_type="code" name="{{ $front['function'] }}_code" value="{{ $front['code'] }}" class="form-control hide" original_value="{{ $front['code'] }}" readonly /> 
                  </td>
                  <td>
                    <p>{{ $front['character'] }}</p>
                    <input type="text" input_type="char" name="{{ $front['function'] }}_char" value="{{ $front['character'] }}" class="form-control hide" original_value="{{ $front['character'] }}" /> 
                  </td>
                  <td>
                    <button type='button' class="btn btn-primary edit" onclick="editFunction(this)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button type='button' class="btn btn-secondary hide cancel" onclick="cancelFunction(this)">
                      <i class='fas fa-times-circle'></i>
                    </button>
                    <input type='hidden' name="function[]" value="{{ $front['function'] }}" />
                    <input type='hidden' name="function_name[]" value="{{ $front['function_name'] }}" />
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <button type="button" id="saveShortcutKey" class="btn btn-success">Save</button>
        </form>
      </div>
    </div>
  </div>

</body>

<script>

  $(document).ready(function(){
    $(".form-control[input_type='char']").on('keyup', function(e){
      var parent = $(this).parent().parent();
      parent.find("input[input_type='code']").val(e.which);
      parent.find("input[input_type='char']").val(e.key);
    });

    $("#saveShortcutKey").click(function(){
      var char_array = [];
      var duplicated = 0;
      var banned = 0;
      $("input[input_type='char']").removeClass('error');
      $("input[input_type='char']").each(function(){
        var input_val = $(this).val().toLowerCase();

        if(char_array.includes(input_val) && input_val != "")
        {
          duplicated = 1;
          $(this).addClass("error");
        }
        else
        {
          char_array.push(input_val);
        }

        var keycode = $(this).parent().parent().find("input[input_type='code']").val();
        if(keycode == 38 || keycode == 40)
        {
          banned = 1;
        }
      });

      if(duplicated == 1)
      {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Shortcut key cannot be same.',
        });
      }
      else if(banned == 1)
      {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Shortcut key cannot use ArrowUp and ArrowDown.',
        });
      }
      else
      {
        $("#saveShortcutKeyForm").submit();
      }
    });
  });
  
  function editFunction(_this)
  {
    $(_this).siblings(".cancel").show();
    $(_this).hide();

    $(_this).parent().parent().find("input").removeClass('hide');
    $(_this).parent().parent().find("p").addClass('hide');
  }

  function cancelFunction(_this)
  {
    $(_this).siblings(".edit").show();
    $(_this).hide();

    var code = $(_this).parent().parent().find("input[input_type='code']");
    var code_original_value = code.attr("original_value");
    code.val(code_original_value);
    code.addClass('hide');

    var character = $(_this).parent().parent().find("input[input_type='char']");
    var char_original_value = character.attr("original_value");
    character.val(char_original_value);
    character.addClass('hide');

    $(_this).parent().parent().find("p").removeClass('hide');
  }

</script>

</html>