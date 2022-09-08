<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/extend-level.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body>
    <img src="{{ asset('img/logo-medici.png') }}" alt="" width="150" style="margin-left: 130px; margin-bottom: 20px;">
    <div class="box box-info">
        <!-- /.box-header -->
        <div class="box-body">
            @if (!empty($dataList))
                <p class="parent" style="{{ $dataList['check_update_info_agency'] == 0 ? 'color:red;' : '' }}" >{{ !empty($dataList['code_agency']) ? $dataList['code_agency'].': ' : '' }}{{ !empty($dataList['name']) ? $dataList['name']: '' }}{{ !empty($dataList['level_code']) ? ' ('.$dataList['level_code'].')' : '' }}</p>
                <ul class="wtree">
                    @foreach ($dataList['childs'] as $lv2)
                        <li>
                        <span style="color: {{ $lv2['check_update_info_agency'] == 0 ? 'red' : '' }};font-weight: bold;">{{ !empty($lv2['code_agency']) ? $lv2['code_agency'].': ' : '' }}{{ !empty($lv2['name']) ? $lv2['name']: '' }}{{ !empty($lv2['level_code']) ? ' ('.$lv2['level_code'].')' : '' }}</span>
                        @if (count($lv2['childs']) > 0)
                            <ul>
                                 @foreach ($lv2['childs'] as $lv3)
                                    <li id="li-{{ $lv3['id'] }}" class="close-el">
                                        <span style="{{ $lv3['check_update_info_agency'] == 0 ? 'color:red;' : '' }}">
                                            @if ($lv3['count_child'] > 0)
                                                <i class="fa fa-plus-circle extend-level" aria-hidden="true" data-id="{{ $lv3['id'] }}" onclick="extendLevel({{ $lv3['id'] }})" id="i-{{ $lv3['id'] }}"></i>
                                            @endif
                                            {{ !empty($lv3['code_agency']) ? $lv3['code_agency'].': ' : '' }}{{ !empty($lv3['name']) ? $lv3['name']: '' }}{{ !empty($lv3['level_code']) ? ' ('.$lv3['level_code'].')' : '' }}</span>
                                    </li>
                                 @endforeach
                            </ul>
                        @endif
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="overlay hide">
        <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
    
    <!-- jQuery 3 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="{{ asset('js/main-admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>
</html>