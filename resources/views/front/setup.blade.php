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

</style>

<body>

  <div class="container" style="margin-top: 20px;">
    <div class="row">
      <div class="col-12">
        <table class="table table-bordered" id="ip_table">
          <thead class="thead-dark">
            <tr>
              <th style="width: 10%;">ID</th>
              <th style="width: 20%;">Type</th>
              <th style="width: 20%;">IP</th>
              <th style="width: 30%;">Device Name</th>
              <th style="width: 20%;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pos_cashier as $cashier)
              <tr>
                <td>{{ $cashier->id }}</td>
                <td>
                  <p>
                    @if($cashier->type == 1)
                      Server
                    @elseif($cashier->type == 2)
                      Cashier
                    @endif
                  </p>
                  <select class="form-control hide" name="device_type">
                    <option value="2" {{ $cashier->type == 2 ? 'selected' : '' }}>Cashier</option>
                    <option value="1" {{ $cashier->type == 1 ? 'selected' : '' }}>Server</option>
                  </select>
                </td>
                <td>
                  <p>{{ $cashier->ip }}</p>
                  <input type='text' name='cashier_ip' class='form-control hide' value='{{ $cashier->ip }}' />
                </td>
                <td>
                  <p>{{ $cashier->cashier_name }}</p>
                  <input type='text' name='cashier_name' class='form-control hide' value='{{ $cashier->cashier_name }}' />
                </td>
                <td>
                  <button class="btn btn-primary" onclick="editCashier(this)" func_type='edit'>
                    <i class="fas fa-edit"></i>
                  </button>

                  <button class="btn btn-danger" onclick="deleteCashier(this, {{ $cashier->id }} )" func_type='delete'>
                    <i class="fas fa-trash-alt"></i>
                  </button>

                  <button class="btn btn-primary hide" onclick="saveCashier(this, {{ $cashier->id }} )" func_type='save'>
                    <i class='fas fa-check-circle'></i>
                  </button>

                  <button class="btn btn-secondary hide" onclick="cancelCashier(this)" func_type='cancel'>
                    <i class='fas fa-times-circle'></i>
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4">
                <button class="btn btn-success" id="add_ip">
                  <i class="fas fa-plus"></i>
                </button>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

</body>

<script>
  
  var my_ip = "{{ $ip }}";
  $(document).ready(function(){

    $("#add_ip").click(function(){

      var html = "";
      html += "<tr>";
      html += "<td>#</td>";
      html += "<td>";
      html += '<select class="form-control" name="device_type">';
      html += '<option value="2">Cashier</option>';
      html += '<option value="1">Server</option>';
      html += '</select>';
      html += "</td>";
      html += "<td><input type='text' name='cashier_ip' class='form-control' value="+my_ip+" /></td>";
      html += "<td><input type='text' name='cashier_name' class='form-control' /></td>";
      html += "<td>";
      html += "<button class='btn btn-success' onclick='createCashier(this)'><i class='fas fa-check-circle'></i></button>";
      html += "<button class='btn btn-danger' onclick='removeRow(this)' style='margin-left:5px;'><i class='fas fa-times-circle'></i></button>";
      html += "</td>";
      html += "</tr>";

      $("#ip_table tbody").append(html);
    });

  });

  function createCashier(_this)
  {
    var parent = $(_this).parent().parent();
    var type = parent.find("select[name='device_type']").val();
    var ip = parent.find("input[name='cashier_ip']").val();
    var name = parent.find("input[name='cashier_name']").val();

    parent.find("select[name='device_type']").removeClass("is-invalid");
    parent.find("input[name='cashier_ip']").removeClass("is-invalid");
    parent.find("input[name='cashier_name']").removeClass("is-invalid");

    var check = true;
    if(!ip)
    {
      check = false;
      parent.find("input[name='cashier_ip']").addClass("is-invalid");
    }

    if(!name)
    {
      check = false;
      parent.find("input[name='cashier_name']").addClass("is-invalid");
    }

    if(!type)
    {
      check = false;
      parent.find("select[name='device_type']").addClass("is-invalid");
    }

    if(!check)
    {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Type, IP and name cannot be empty.',
      });

      return;
    }

    $.post("{{ route('createCashier') }}", { "_token" : "{{ csrf_token() }}", "type" : type, "ip" : ip, "name" : name }, function(result){
      if(result.error == 0)
      {
        var pos_cashier = result.pos_cashier;
        var device_type_text = "";
        var device_type_html = "";

        device_type_html += "<select class='form-control hide' name='device_type'>";
        if(pos_cashier.type == 1)
        {
          device_type_text = "Server";
          device_type_html += "<option value='2'>Cashier</option><option value='1' selected>Server</option>";
        }
        else if(pos_cashier.type == 2)
        {
          device_type_text = "Cashier";
          device_type_html += "<option value='2' selected>Cashier</option><option value='1'>Server</option>";
        }

        device_type_html += "</select>";

        parent.find("td:nth-child(1)").html(pos_cashier.id);
        parent.find("td:nth-child(2)").html("<p>"+device_type_text+"</p>"+device_type_html);
        parent.find("td:nth-child(3)").html("<p>"+pos_cashier.ip+"</p><input type='text' name='cashier_ip' class='form-control hide' value='"+pos_cashier.ip+"' />");
        parent.find("td:nth-child(4)").html("<p>"+pos_cashier.cashier_name+"</p><input type='text' name='cashier_name' class='form-control hide' value='"+pos_cashier.cashier_name+"' />");

        var html = ""
        html += '<button class="btn btn-primary" onclick="editCashier(this)" func_type="edit">';
        html += '<i class="fas fa-edit"></i>';
        html += '</button>';

        html += '<button class="btn btn-danger" onclick="deleteCashier(this, '+pos_cashier.id+' )" func_type="delete" style="margin-left:5px;">';
        html += '<i class="fas fa-trash-alt"></i>';
        html += '</button>';

        html += '<button class="btn btn-primary hide" onclick="saveCashier(this, '+pos_cashier.id+' )" func_type="save">';
        html += '<i class="fas fa-check-circle"></i>';
        html += '</button>';

        html += '<button class="btn btn-secondary hide" onclick="cancelCashier(this)" func_type="cancel" style="margin-left:5px;">';
        html += '<i class="fas fa-times-circle"></i>';
        html += '</button>';

        parent.find("td:nth-child(5)").html(html);

        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Cashier was created.',
        });
      }
      else
      {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message,
        });

        parent.find("input[name='cashier_ip']").addClass("is-invalid");
        return;
      }
    })
  }

  function removeRow(_this)
  {
    $(_this).parent().parent().remove();
  }

  function deleteCashier(_this, id)
  {
    Swal.fire({
      title: 'Do you want to delete this cashier?',
      showDenyButton: true,
      showCancelButton: false,
      confirmButtonText: 'Delete',
      denyButtonText: 'Cancel',
      denyButtonColor: "#3085d6",
      confirmButtonColor: "#dd6b55"
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        $.post("{{ route('deleteCashier') }}", {"_token" : "{{ csrf_token() }}", 'id' : id }, function(result){
          if(result.error == 0)
          {
            $(_this).parent().parent().remove();
            Swal.fire('Cashier is deleted!', '', 'success') 
          }
        });
        
      }
    })
  }

  function editCashier(_this)
  {
    var parent = $(_this).parent().parent();

    parent.find("td:nth-child(2) p").addClass("hide");
    parent.find("td:nth-child(3) p").addClass("hide");
    parent.find("td:nth-child(4) p").addClass("hide");
    parent.find("td:nth-child(5) button[func_type='edit']").addClass("hide");
    parent.find("td:nth-child(6) button[func_type='delete']").addClass("hide");

    parent.find("td:nth-child(2) select").removeClass("hide");
    parent.find("td:nth-child(3) input").removeClass("hide");
    parent.find("td:nth-child(4) input").removeClass("hide");
    parent.find("td:nth-child(5) button[func_type='save']").removeClass("hide");
    parent.find("td:nth-child(6) button[func_type='cancel']").removeClass("hide");
  }

  function cancelCashier(_this)
  {
    var parent = $(_this).parent().parent();

    parent.find("td:nth-child(2) p").removeClass("hide");
    parent.find("td:nth-child(3) p").removeClass("hide");
    parent.find("td:nth-child(4) p").removeClass("hide");
    parent.find("td:nth-child(5) button[func_type='edit']").removeClass("hide");
    parent.find("td:nth-child(6) button[func_type='delete']").removeClass("hide");

    parent.find("td:nth-child(2) select").addClass("hide");
    parent.find("td:nth-child(3) input").addClass("hide");
    parent.find("td:nth-child(4) input").addClass("hide");
    parent.find("td:nth-child(5) button[func_type='save']").addClass("hide");
    parent.find("td:nth-child(6) button[func_type='cancel']").addClass("hide");
  }

  function saveCashier(_this, id)
  {
    var parent = $(_this).parent().parent();

    parent.find("select[name='device_type']").removeClass("is-invalid");
    parent.find("input[name='cashier_ip']").removeClass("is-invalid");
    parent.find("input[name='cashier_name']").removeClass("is-invalid");

    var type = parent.find("select[name='device_type']").val();
    var ip = parent.find("input[name='cashier_ip']").val();
    var name = parent.find("input[name='cashier_name']").val();

    var check = true;
    if(!ip)
    {
      check = false;
      parent.find("input[name='cashier_ip']").addClass("is-invalid");
    }

    if(!name)
    {
      check = false;
      parent.find("input[name='cashier_name']").addClass("is-invalid");
    }

    if(!type)
    {
      check = false;
      parent.find("select[name='device_type']").addClass("is-invalid");
    }

    if(!check)
    {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Type, IP and name cannot be empty.',
      });

      return;
    }

    $.post("{{ route('editCashier') }}", { "_token" : "{{ csrf_token() }}", 'id' : id, 'ip' : ip, 'type' : type, 'name' : name }, function(result){
      if(result.error == 0)
      {
        var pos_cashier = result.pos_cashier;
        var device_type_text = "";
        if(pos_cashier.type == 1)
        {
          device_type_text = "Server";
        }
        else if(pos_cashier.type == 2)
        {
          device_type_text = "Cashier";
        }

        parent.find("td:nth-child(2) p").removeClass("hide").html(device_type_text);
        parent.find("td:nth-child(3) p").removeClass("hide").html(pos_cashier.ip);
        parent.find("td:nth-child(4) p").removeClass("hide").html(pos_cashier.cashier_name);
        parent.find("td:nth-child(5) button[func_type='edit']").removeClass("hide");
        parent.find("td:nth-child(6) button[func_type='delete']").removeClass("hide");

        parent.find("td:nth-child(2) select").addClass("hide").val(pos_cashier.type);
        parent.find("td:nth-child(3) input").addClass("hide").val(pos_cashier.ip);
        parent.find("td:nth-child(4) input").addClass("hide").val(pos_cashier.cashier_name);
        parent.find("td:nth-child(5) button[func_type='save']").addClass("hide");
        parent.find("td:nth-child(6) button[func_type='cancel']").addClass("hide");

        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Cashier was updated.',
        });
      }
      else if(result.error == 1)
      {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: result.message,
        });

        parent.find("input[name='cashier_ip']").addClass("is-invalid");
        return;
      }
    });
  }

</script>

</html>