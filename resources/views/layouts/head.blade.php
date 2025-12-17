<head>
    <base href="{{asset('')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="Endline QC System (Hệ thống QC Endline)">
    <meta name="author" content="Łukasz Holeczek">
    <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
    <title>Mian Sameri Việt Nam</title>
    <link rel="icon" type="image/png" sizes="192x192" href="assets/brand/logo-slogan-vertical.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/brand/logo-slogan-vertical.png">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/brand/logo-slogan-vertical.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/brand/logo-slogan-vertical.png">
    <link rel="manifest" href="assets/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- Vendors styles-->
    <link rel="stylesheet" href="vendors/simplebar/css/simplebar.css">
    <link rel="stylesheet" href="css/vendors/simplebar.css">
    <!-- Main styles for this application-->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!-- We use those styles to show code examples, you should remove them in your application.-->
    <link href="css/examples.css" rel="stylesheet">
    <script src="js/config.js"></script>
    <!-- <script src="js/color-modes.js"></script> -->
    <link href="vendors/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .choices__inner {
            padding: 3.5px 7.5px 0px !important;
            border-radius: 9.5px;
            font-size: 14px;
            min-height: 39px !important;
            background-color: #fff;
        }

        .choices__input {
            background-color: #fff;
        }

        .choices {
            margin: 0;
        }

        .choices__list--multiple .choices__item.is-highlighted {
            background-color: #C2661A;
            border: 1px solid #C2661A;
        }

        .choices__list--multiple .choices__item {

            background-color: #C2661A;
            border: 1px solid #C2661A;
        }

        .choices[data-type*=select-multiple] .choices__button,
        .choices[data-type*=text] .choices__button {
            border-left: 1px solid #fff;
        }
    </style>
    @stack('styles')


</head>