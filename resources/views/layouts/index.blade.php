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
    <!-- Tempusdominus Bbootstrap 4 -->
    <link rel="stylesheet" href="/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="/dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="/plugins/summernote/summernote-bs4.css">
    <!-- Bootstrap 4 RTL -->
    <link rel="stylesheet" href="/dist/css/bootstrap.min.css">
    <!-- Custom style for RTL -->
    <link rel="stylesheet" href="/dist/css/custom.css">
    <!-- PersianCalender -->
    <link href="/plugins/persiancalender/jquery.md.bootstrap.datetimepicker.style.css" rel="stylesheet"/>
    @yield('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <!--<li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>-->
            </ul>


            <!-- SEARCH FORM -->
            <!--
    <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>
    -->

            <!-- Right navbar links -->
            <ul class="navbar-nav mr-auto-navbav">
                <!-- Messages Dropdown Menu -->

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">{{ count($usermessages) }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        @foreach ($usermessages as $item)
                        <a href="{{ route('messages') }}" class="dropdown-item">
                            <div class="media">
                                @if($item->themessage->user->image_path)
                                <img src="/uploads/{{ $item->themessage->user->image_path }}" alt="User Avatar"
                                    class="img-size-50 mr-3 img-circle">
                                @else
                                <img src="/dist/img/user1-128x128.jpg" alt="User Avatar"
                                    class="img-size-50 mr-3 img-circle">
                                @endif
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        {{ $item->themessage->user->first_name }}
                                        {{ $item->themessage->user->last_name }}
                                        <!--<span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>-->
                                    </h3>
                                    <p class="text-sm">{{ $item->themessage->message }}</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i>
                                        {{ jdate(strtotime($item->created_at))->format("Y/m/d H:i:s") }} </p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>

                        @endforeach
                        <a href="{{ route('messages') }}" class="dropdown-item dropdown-footer">صندوق پیام</a>
                    </div>
                </li>

                <!-- Notifications Dropdown Menu -->

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge">{{ count($usercircular) }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ count($usercircular) }} بخشنامه</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('circulars') }}" class="dropdown-item dropdown-footer">مشاهده همه</a>
                    </div>
                </li>

                <!--
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
      -->
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/" class="brand-link">
                <img src="/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">{{ env('APP_NAME') }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        @if(Auth::user()->image_path)
                        <img src="/uploads/{{ Auth::user()->image_path }}" class="img-circle elevation-2" alt="User Image">
                        @else
                        <img src="/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                        @endif
                    </div>
                    <div class="info">
                        <a href="/login" class="d-block">
                            {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}

                            <i class="fa fa-window-close"></i>
                        </a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'dashboard')===0)
                            <a href="/" class="nav-link active">
                                @else
                                <a href="/" class="nav-link">
                                    @endif
                                    <!-- <i class="far fa-circle nav-icon"></i> -->
                                    <p>داشبورد</p>
                                </a>
                        </li>
                        @if (Gate::allows('parameters'))
                        <!-- <li class="nav-header">تعاریف پایه</li> -->
                        @if(strpos(\Request::route()->getName(), 'tag')===0 || strpos(\Request::route()->getName(),
                        'need_tag')===0 || strpos(\Request::route()->getName(), 'parent_tag')===0 ||
                        strpos(\Request::route()->getName(), 'need_parent_tag')===0 ||
                        strpos(\Request::route()->getName(), 'temperature')===0 ||
                        strpos(\Request::route()->getName(), 'school')===0 ||
                        strpos(\Request::route()->getName(), 'collection')===0 ||
                        strpos(\Request::route()->getName(), 'product')===0 ||
                        strpos(\Request::route()->getName(), 'source')===0 ||
                        strpos(\Request::route()->getName(), 'user_all')===0 ||
                        strpos(\Request::route()->getName(), 'call_result')===0 ||
                        strpos(\Request::route()->getName(), 'notice')===0 ||
                        strpos(\Request::route()->getName(), 'province')===0 ||
                        strpos(\Request::route()->getName(), 'class_room')===0 ||
                        strpos(\Request::route()->getName(), 'cit')===0 ||
                        strpos(\Request::route()->getName(), 'lesson')===0 ||
                        strpos(\Request::route()->getName(), 'exam')===0)
                        <li class="nav-item has-treeview menu-open">
                            @else
                        <li class="nav-item has-treeview">
                            @endif
                            <a href="#" class="nav-link">
                                <!-- <i class="nav-icon fas fa-bookmark"></i> -->
                                <p>
                                    تعاریف پایه
                                    <i class="fas fa-angle-left right"></i>
                                    <!--<span class="badge badge-info right">6</span>-->
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'need_parent_tag_one')===0)
                                    <a href="{{ route('need_parent_tag_ones') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('need_parent_tag_ones') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب اصلی نیازسنجی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'need_parent_tag_two')===0)
                                    <a href="{{ route('need_parent_tag_twos') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('need_parent_tag_twos') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 1 نیازسنجی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'need_parent_tag_three')===0)
                                    <a href="{{ route('need_parent_tag_threes') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('need_parent_tag_threes') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 2 نیازسنجی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'need_parent_tag_four')===0)
                                    <a href="{{ route('need_parent_tag_fours') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('need_parent_tag_fours') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 3 نیازسنجی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'need_tag')===0)
                                    <a href="{{ route('need_tags') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('need_tags') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>نیازسنجی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'parent_tag_one')===0)
                                    <a href="{{ route('parent_tag_ones') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('parent_tag_ones') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب اصلی اخلاقی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'parent_tag_two')===0)
                                    <a href="{{ route('parent_tag_twos') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('parent_tag_twos') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 1 اخلاقی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'parent_tag_three')===0)
                                    <a href="{{ route('parent_tag_threes') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('parent_tag_threes') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 2 اخلاقی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'parent_tag_four')===0)
                                    <a href="{{ route('parent_tag_fours') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('parent_tag_fours') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>برچسب فرعی 3 اخلاقی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'tag')===0)
                                    <a href="{{ route('tags') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('tags') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>اخلاقی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'school')===0)
                                    <a href="{{ route('schools') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('schools') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>تعریف مدرسه</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'temperature')===0)
                                    <a href="{{ route('temperatures') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('temperatures') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>داغ/سرد</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'collection')===0)
                                    <a href="{{ route('collections') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('collections') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>دسته محصولات</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'product')===0)
                                    <a href="{{ route('products') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('products') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>محصول</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'source')===0)
                                    <a href="{{ route('sources') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('sources') }}" class="nav-link">
                                    @endif
                                            <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>منبع</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'call_result')===0)
                                    <a href="{{ route('call_results') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('call_results') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>نتایج تماس</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'notice')===0)
                                    <a href="{{ route('notices') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('notices') }}" class="nav-link">
                                    @endif
                                        <!-- <i class="far fa-circle nav-icon"></i> -->
                                        -
                                        <p>اطلاع رسانی</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'user_all')===0)
                                    <a href="{{ route('user_alls') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('user_alls') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف پشتیبان</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'sale_suggestion')===0)
                                    <a href="{{ route('sale_suggestions') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('sale_suggestions') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف شروط</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'province')===0)
                                    <a href="{{ route('provinces') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('provinces') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف استان</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'cit')===0)
                                    <a href="{{ route('cities') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('cities') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف شهر</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'class_room')===0)
                                    <a href="{{ route('class_rooms') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('class_rooms') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف کلاس</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'lesson')===0)
                                    <a href="{{ route('lessons') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('lessons') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف درس</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    @if(strpos(\Request::route()->getName(), 'exam')===0)
                                    <a href="{{ route('exams') }}" class="nav-link active">
                                    @else
                                    <a href="{{ route('exams') }}" class="nav-link">
                                    @endif
                                        -
                                        <p>تعریف آزمون</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif
                        @if (Gate::allows('students'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'students')===0)
                            <a href="{{ route('students') }}" class="nav-link active">
                            @else
                            <a href="{{ route('students') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>ورودی و تقسیم دانش آموز</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'student_csv')===0)
                            <a href="{{ route('student_csv') }}" class="nav-link active">
                            @else
                            <a href="{{ route('student_csv') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>ثبت دانش آموز از اکسل</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'student_all')===0)
                            <a href="{{ route('student_all') }}" class="nav-link active">
                            @else
                            <a href="{{ route('student_all') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>فهرست دانش آموزان</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'merge_students_index')===0)
                            <a href="{{ route('merge_students_index') }}" class="nav-link active">
                            @else
                            <a href="{{ route('merge_students_index') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>همگام سازی دانش آموزان</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'assign_students_index')===0)
                            <a href="{{ route('assign_students_index') }}" class="nav-link active">
                            @else
                            <a href="{{ route('assign_students_index') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>اختصاص گروهی دانش آموزان</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'student_banned')===0)
                            <a href="{{ route('student_banned') }}" class="nav-link active">
                            @else
                            <a href="{{ route('student_banned') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>فهرست سیاه دانش آموزان</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'student_archived')===0)
                            <a href="{{ route('student_archived') }}" class="nav-link active">
                            @else
                            <a href="{{ route('student_archived') }}" class="nav-link">
                            @endif
                                <!-- <i class="far fa-circle nav-icon"></i> -->
                                <p>فهرست آرشیو دانش آموزان</p>
                            </a>
                        </li>
                        @endif
                        @if (Gate::allows('users'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'user_supporters')===0)
                            <a href="{{ route('user_supporters') }}" class="nav-link active">
                            @else
                            <a href="{{ route('user_supporters') }}" class="nav-link">
                            @endif
                                <p>لیست کامل پشتیبان ها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'user_supporter_call')===0)
                            <a href="{{ route('user_supporter_calls') }}" class="nav-link active">
                            @else
                            <a href="{{ route('user_supporter_calls') }}" class="nav-link">
                            @endif
                                <p>لیست تماس پشتیبان ها</p>
                            </a>
                        </li>

                        @endif
                        @if (Gate::allows('sale_suggestions'))
                        <!--
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'sale_suggestion')===0)
                            <a href="{{ route('sale_suggestions') }}" class="nav-link active">
                                @else
                                <a href="{{ route('sale_suggestions') }}" class="nav-link">
                                    @endif
                                    <p> پیشنهاد فروش برای دانش آموز</p>
                                </a>
                        </li>
                        -->
                        @endif
                        @if (Gate::allows('purchases'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'purchase')===0)
                            <a href="{{ route('purchases') }}" class="nav-link active">
                                @else
                                <a href="{{ route('purchases') }}" class="nav-link">
                                    @endif
                                    <p>ثبت خرید های حضوری</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'supporter_student_purchases')===0)
                            <a href="{{ route('supporter_student_purchases') }}" class="nav-link active">
                                @else
                                <a href="{{ route('supporter_student_purchases') }}" class="nav-link">
                                    @endif
                                    <p>گزارش خرید ها</p>
                                </a>
                        </li>
                        @endif
                        @if(Gate::allows('parameters'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'help')===0)
                            <a href="{{ route('helps') }}" class="nav-link active">
                            @else
                            <a href="{{ route('helps') }}" class="nav-link">
                            @endif
                                <p>مدیریت آموزش و راهنما</p>
                            </a>
                        </li>
                        @endif
                        @if (Gate::allows('marketers'))
                        <li class="nav-header">نمایندگان</li>
                        </li>
                        @if(strpos(\Request::route()->getName(), 'marketer')===0)
                        <li class="nav-item has-treeview menu-open">
                            @else
                        <li class="nav-item has-treeview">
                            @endif
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerdashboard')===0)
                            <a href="{{ route('marketerdashboard') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerdashboard') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-palette nav-icon"></i>
                                    <p>داشبورد</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketermystudents')===0)
                            <a href="{{ route('marketermystudents') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketermystudents') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-street-view nav-icon"></i>
                                    <p>دانش آموزان من</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerstudents')===0)
                            <a href="{{ route('marketerstudents') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerstudents') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-address-book nav-icon"></i>
                                    <p>لیست مالی دانش آموزان</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerpayments')===0)
                            <a href="{{ route('marketerpayments') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerpayments') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-donate nav-icon"></i>
                                    <p>وصولی های من</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketercirculars')===0)
                            <a href="{{ route('marketercirculars') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketercirculars') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-book nav-icon"></i>
                                    <p>آموزش و راهنما </p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketermails')===0)
                            <a href="{{ route('marketermails') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketermails') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-bell nav-icon"></i>
                                    <p>دریافت بخش نامه </p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerdiscounts')===0)
                            <a href="{{ route('marketerdiscounts') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerdiscounts') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-chart-pie nav-icon"></i>
                                    <p>ایجاد کد تخفیف</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerproducts')===0)
                            <a href="{{ route('marketerproducts') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerproducts') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-project-diagram nav-icon"></i>
                                    <p>فهرست محصولات</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketerprofile')===0)
                            <a href="{{ route('marketerprofile') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketerprofile') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-address-card nav-icon"></i>
                                    <p>مشخصات من</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'marketercode')===0)
                            <a href="{{ route('marketercode') }}" class="nav-link active">
                                @else
                                <a href="{{ route('marketercode') }}" class="nav-link">
                                    @endif
                                    <i class="fa fa-link nav-icon"></i>
                                    <p>لینک معرفی و کد شناسایی</p>
                                </a>
                        </li>
                        </li>
                        @endif
                        @if(Gate::allows('supporters'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'supporter_students')===0)
                            <a href="{{ route('supporter_students') }}" class="nav-link active">
                                @else
                                <a href="{{ route('supporter_students') }}" class="nav-link">
                                    @endif
                                    <p>فهرست دانش آموزان/ تماس</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'supporter_student_new')===0)
                            <a href="{{ route('supporter_student_new') }}" class="nav-link active">
                                @else
                                <a href="{{ route('supporter_student_new') }}" class="nav-link">
                                    @endif
                                    <p>ورودی جدید</p>
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'supporter_student_purchases')===0)
                            <a href="{{ route('supporter_student_purchases') }}" class="nav-link active">
                            @else
                            <a href="{{ route('supporter_student_purchases') }}" class="nav-link">
                                @endif
                                <p>خریدهای قطعی</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'reminder')===0)
                            <a href="{{ route('reminders') }}" class="nav-link active">
                            @else
                            <a href="{{ route('reminders') }}" class="nav-link">
                                @endif
                                <p>یادآورها</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'user_a_supporter_call')===0)
                            <a href="{{ route('user_a_supporter_calls') }}" class="nav-link active">
                            @else
                            <a href="{{ route('user_a_supporter_calls') }}" class="nav-link">
                            @endif
                                <p>لیست تماس</p>
                            </a>
                        </li>
                        @endif
                        @if (!Gate::allows('marketers'))
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'circular')===0)
                            <a href="{{ route('circulars') }}" class="nav-link active">
                                @else
                                <a href="{{ route('circulars') }}" class="nav-link">
                                    @endif
                                    @if(Gate::allows('parameters'))
                                    <p>ارسال بخشنامه برای پشتیبان ها</p>
                                    @else
                                    <p>دریافت  بخشنامه ها</p>
                                    @endif
                                </a>
                        </li>
                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'message')===0)
                            <a href="{{ route('messages') }}" class="nav-link active">
                                @else
                                <a href="{{ route('messages') }}" class="nav-link">
                                    @endif
                                    <p>ارسال و دریافت پیام</p>
                                </a>
                        </li>

                        <li class="nav-item">
                            @if(strpos(\Request::route()->getName(), 'grid')===0)
                            <a href="{{ route('grid') }}" class="nav-link active">
                            @else
                            <a href="{{ route('grid') }}" class="nav-link">
                            @endif
                                <p>آموزش و راهنما</p>
                            </a>
                        </li>
                        @endif
                        <!--
          <li class="nav-item has-treeview menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.html" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v1</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index2.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v2</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index3.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v3</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="pages/widgets.html" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Widgets
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                Layout Options
                <i class="fas fa-angle-left right"></i>
                <span class="badge badge-info right">6</span>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/layout/top-nav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Top Navigation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/boxed.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Boxed</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Sidebar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-topnav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Navbar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-footer.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Footer</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/collapsed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Collapsed Sidebar</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Charts
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/charts/chartjs.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>ChartJS</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/flot.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Flot</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/inline.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inline</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tree"></i>
              <p>
                UI Elements
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/UI/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>General</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/icons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Icons</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/buttons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Buttons</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/sliders.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sliders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/modals.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Modals & Alerts</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/navbar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Navbar & Tabs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/timeline.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Timeline</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/ribbons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Ribbons</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-edit"></i>
              <p>
                Forms
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/forms/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>General Elements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/forms/advanced.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Advanced Elements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/forms/editors.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Editors</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Tables
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/tables/simple.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Simple Tables</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/tables/data.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>DataTables</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/tables/jsgrid.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>jsGrid</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-header">EXAMPLES</li>
          <li class="nav-item">
            <a href="pages/calendar.html" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i>
              <p>
                Calendar
                <span class="badge badge-info right">2</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pages/gallery.html" class="nav-link">
              <i class="nav-icon far fa-image"></i>
              <p>
                Gallery
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-envelope"></i>
              <p>
                Mailbox
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/mailbox/mailbox.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inbox</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/compose.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Compose</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/read-mail.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Read</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Pages
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/examples/invoice.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Invoice</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/profile.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Profile</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/e_commerce.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>E-commerce</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/projects.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Projects</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_add.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Add</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_edit.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Edit</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_detail.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Detail</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/contacts.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Contacts</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-plus-square"></i>
              <p>
                Extras
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/examples/login.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Login</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/register.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Register</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/lockscreen.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Lockscreen</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/legacy-user-menu.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Legacy User Menu</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/language-menu.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Language Menu</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/404.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Error 404</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/500.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Error 500</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/blank.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Blank Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="starter.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Starter Page</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-header">MISCELLANEOUS</li>
          <li class="nav-item">
            <a href="https://adminlte.io/docs/3.0" class="nav-link">
              <i class="nav-icon fas fa-file"></i>
              <p>Documentation</p>
            </a>
          </li>
          <li class="nav-header">LABELS</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-danger"></i>
              <p class="text">Important</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-warning"></i>
              <p>Warning</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-info"></i>
              <p>Informational</p>
            </a>
          </li>
          -->
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
            @if (isset($msg_success))
            <div class="card card-success" style="width: 400px;position: fixed;left: 10px;bottom: 10px;">
                <div class="card-header">
                    <h3 class="card-title">موفقیت</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    {{ $msg_success }}
                </div>
                <!-- /.card-body -->
            </div>
            @endif
            @if (isset($msg_error))
            <div class="card card-danger" style="width: 400px;position: fixed;left: 10px;bottom: 10px;">
                <div class="card-header">
                    <h3 class="card-title">خطا</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    {{ $msg_error }}
                </div>
                <!-- /.card-body -->
            </div>
            @endif
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <!--
    <strong>Copyright &copy; 2014-2019 <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.0.0-rc.1
    </div>
    -->
            کلیه حقوق متعلق به
            <strong>
                <a href="https://aref-group.ir/">
                    خانه کنکور عارف
                </a>
                &copy;
                1390-1399
            </strong>
            است
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->




    <!-- jQuery -->
    <script src="/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="/plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)

    </script>
    <!-- Bootstrap 4 rtl -->
    <script src="/dist/js/bootstrap.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- ChartJS -->
    <script src="/plugins/chart.js/Chart.min.js"></script>
    <!-- Sparkline -->
    <script src="/plugins/sparklines/sparkline.js"></script>
    <!-- JQVMap -->
    <script src="/plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="/plugins/jqvmap/maps/jquery.vmap.world.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="/plugins/jquery-knob/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="/plugins/moment/moment.min.js"></script>
    <script src="/plugins/daterangepicker/daterangepicker.js"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Summernote -->
    <script src="/plugins/summernote/summernote-bs4.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="/dist/js/adminlte.js"></script>
    <!-- PersianCalender -->
    <script src="/plugins/persiancalender/jquery.md.bootstrap.datetimepicker.js"></script>

    @yield('js')

    <script>
        $(document).ready(function(){
            $("input.pdate").each(function(id, field){
                $(field).MdPersianDateTimePicker({
                    targetTextSelector: '#' + field.id
                });
            });
        });
    </script>

</body>

</html>
