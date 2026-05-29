<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">
<head>
    @include('partials.head')
</head>
  <style>
    .card-body {
        overflow-x: hidden;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table.dataTable {
        width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    @media (max-width: 768px) {
        .page-content {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .card-body {
            padding: 10px !important;
        }

        table.dataTable {
            min-width: 850px;
        }
    }
</style>


<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('partials.header')

        @include('partials.menu')

        <div class="vertical-overlay"></div>

        <div class="main-content">

            <!-- Page-content -->
            @yield('content')
            <!-- End Page-content -->

            @include('partials.footer')
        </div>

    </div>

    @include('partials.top')

    @include('partials.js')

    @stack('scripts')
    <script>
    $(document).ready(function () {
        if ($.fn.DataTable) {
            $.extend(true, $.fn.dataTable.defaults, {
                scrollX: true,
                autoWidth: false,
                responsive: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        }
    });
</script>
</body>
</html>
