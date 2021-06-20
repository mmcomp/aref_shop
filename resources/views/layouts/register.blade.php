<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>
    @section('page_title')
          {{ env('APP_NAME') }}
    @show
  </title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="/dist/css/ionicons.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/dist/css/adminlte.min.css">
  <!-- Bootstrap 4 RTL -->
  <link rel="stylesheet" href="/dist/css/bootstrap.min.css">
  <!-- Custom style for RTL -->
  <link rel="stylesheet" href="/dist/css/custom.css">
</head>
<body class="hold-transition register-page" style="background-image: url('/dist/img/photo2.png');background-size: cover;" >
  <div class="register-box">
    <div class="register-logo">
      <a> {{ env('APP_NAME') }}</a>
    </div>
    @auth
      <div class="card p-3" >
        <div class="alert alert-danger">
          قبلا ثبت نام انجام شده است
        </div>
        <a href="/" class="btn btn-block btn-success" > بازگشت</a>
      </div>  
    @endauth
    @isset($error)
      <div class="alert alert-danger">
          <ul>
              <li>{{ $error }}</li>
          </ul>
      </div>
    @endisset
    @guest
      @if ($errors->any())
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
      @endif
      <div id="msg" ></div>
      @isset($first_step)
        <div class="register-box-body card p-3" >
          <p class="login-box-msg">ثبت نام نماینده</p>
          <form id="frm1" action="{{ route('sendsms') }}" method="post">
            @csrf
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-user"></span>
                </div>
              </div>
            <input value="{{ old('fname') }}" type="text" class="form-control" placeholder="نام" id="fname" name="fname" >
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
              <input value="{{ old('lname') }}" type="text" class="form-control" placeholder="نام خانوادگی" id="lname" name="lname" >
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <input value="{{ old('mobile') }}" type="text" class="form-control" placeholder="تلفن همراه" id="mobile" name="mobile" >
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-city"></span>
                </div>
              </div>
              <select name="province" id="province" class="form-control" >
                <option value="" >استان</option>
                @foreach ($provinces as $key => $value)
                  <option value="{{ $key }}" {{ old('province') == $key ? 'selected' : '' }} > 
                      {{ $value }} 
                  </option>
                @endforeach 
              </select>
            </div>
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-city"></span>
                </div>
              </div>
              <input value="{{ old('city') }}" type="text" class="form-control" placeholder="شهر" id="city" name="city" >
            </div>
            <div class="row" >
              <div class="col-md-4 col-sm-6 col-xs-12" >
                <button type="button" id="first_step" class="btn btn-primary btn-block btn-flat" value="1"> ادامه </button>
              </div>
              <div class="col-md-8 col-sm-6 col-xs-12 mt-2" >
                <a href="{{ route('login') }}" class="text-center">قبلا عضو شده ام</a>
              </div>
            </div>
          </form>
          
        </div><!-- /.form-box -->
      @endisset

      @isset($second_step)
        <div class="card p-3" >
          <div class="m-3" >
            {{ $smsMessage }}
          </div>
          <form id="frm2" action="{{ route('checksms') }}" method="post">
            @csrf
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-sms"></span>
                </div>
              </div>
              <input type="text" class="form-control" placeholder="کد پیامک شده را وارد کنید" id="sms_code" name="sms_code" >
              <input type="hidden" name="mobile" value="@if($mobile!='') {{ $mobile }} @else {{ old('mobile') }} @endif"  >
            </div>
            <div class="row" >
              <div class="col-md-4 col-sm-6 col-xs-12" >
                <button type="button" id="second_step" class="btn btn-primary btn-block btn-flat"> ادامه </button>
              </div>
            </div> 
          </form>
        </div>
      @endisset
      @isset($third_step)
        <div class="card p-3" >
          <div class="m-3" >
            {{ $smsMessage }}
          </div>
          <form id="frm3" action="{{ route('createuser') }}" method="post">
            @csrf
            <div class="input-group mb-3">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
              <input type="password" name="password" id="password"  >
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-eye-slash"></span>
                  <span class="fas fa-eye d-none" ></span>
                </div>
              </div>
              <input type="hidden" id="sms_code" name="sms_code" value="@if($sms_code!='') {{ $sms_code }} @else {{ old('sms_code') }} @endif" >
              <input type="hidden" name="mobile" value="@if($mobile!='') {{ $mobile }} @else {{ old('mobile') }} @endif" >
            </div>
            <div class="row" >
              <div class="col-md-6 col-sm-12 col-xs-12" >
                <button id="third_step" type="button" class="btn btn-primary btn-block btn-flat"> ذخیره و پایان </button>
              </div>
            </div> 
          </form>
        </div>
      @endisset
      @isset($final_step)
        <div class="alert alert-success">
          {{ $smsMessage }}
        </div>
      @endisset
    @endguest
  </div><!-- /.register-box -->
<!-- jQuery -->
<script src="/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

@isset($first_step)
  <script>
    $(document).ready(()=>{
      $("#first_step").on('click',() =>{
        let ok = true;
        $("#frm1 :input").each(function(){
          $(this).css("border","1px solid rgb(206, 212, 218)");
          if( $(this).prop('id') != "" ){
            if( $(this).val() == "" ){
              $(this).css("border","solid 1px red");
              ok = false;
            }
          }
        });
        if(!ok){
          $("#msg").html('لطفا کلیه موارد را وارد کنید');
          $("#msg").addClass("alert").addClass("alert-danger");
          return;
        }
        $("#frm1").submit();
      });
    }); 
  </script>
@endisset

@isset($second_step)
  <script>
    $(document).ready(()=>{
      $("#second_step").on('click',() =>{
        if($("#sms_code").val().length != 4 ){
          $("#msg").html('لطفا کد پیامک شده را صحیح وارد کنید');
          $("#msg").removeClass().addClass("alert").addClass("alert-danger");
          return;
        }
        $("#frm2").submit();
      });
    }); 
  </script>
@endisset
@isset($third_step)
  <script>
    $(document).ready(()=>{
      $(".fa-eye-slash").on('click',function(){
        $(".fa-eye-slash").addClass("d-none");
        $(".fa-eye").removeClass("d-none");
        $("#password").prop('type',"text");
      });
      $(".fa-eye").on('click',function(){
        $(".fa-eye").addClass("d-none");
        $(".fa-eye-slash").removeClass("d-none");
        $("#password").prop('type',"password");
      });
      $("#third_step").on('click',() =>{
        if($("#password").val().length <6 ){
          $("#msg").html('لطفا حداقل ۶ کاراکتر برای پسورد وارد کنید');
          $("#msg").removeClass().addClass("alert").addClass("alert-danger");
          return;
        }
        $("#frm3").submit();
      });
    }); 
  </script>
@endisset
@isset($final_step)
  <script>
    $(document).ready(()=>{
      setTimeout(()=>{
        window.location = "{{ route('login') }}";
      },4000);
    });
  </script>    
@endisset
</body>
</html>
