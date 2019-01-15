@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')

    <style>
        .panel-primary {
            border-color: #1ab394;
        }
        .panel-primary > .panel-heading {
            background-color: #1ab394;
            border-color: #1ab394;
        }
        .panel-success {
            border-color: #1c84c6;
        }
        .panel-success > .panel-heading {
            background-color: #1c84c6;
            border-color: #1c84c6;
            color: #ffffff;
        }
        .panel-info {
            border-color: #23c6c8;
        }
        .panel-info > .panel-heading {
            background-color: #23c6c8;
            border-color: #23c6c8;
            color: #ffffff;
        }
        .panel-warning {
            border-color: #f8ac59;
        }
        .panel-warning > .panel-heading {
            background-color: #f8ac59;
            border-color: #f8ac59;
            color: #ffffff;
        }

        /* PANELS */
        .panel-title > .small,
        .panel-title > .small > a,
        .panel-title > a,
        .panel-title > small,
        .panel-title > small > a {
            color: inherit;
        }
        .page-heading {
            border-top: 0;
            padding: 0 10px 20px 10px;
        }
        .panel-heading h1,
        .panel-heading h2 {
            margin-bottom: 5px;
        }
        .panel-body {
            padding: 15px;
        }
        /* Bootstrap 3.3.x panels */
        .panel {
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .panel-heading {
            color: white;
            padding: 10px 15px;
            border-bottom: 1px solid transparent;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .panel-footer {
            padding: 10px 15px;
            border-top: 1px solid #e7eaec;
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
        }
        .panel-default > .panel-heading {
            color: #333;
            background-color: #f5f5f5;
            border-color: #e7eaec;
        }
        .panel-default {
            border-color: #e7eaec;
        }
        .panel-group .panel + .panel {
            margin-top: 5px;
        }
        .panel-group .panel {
            margin-bottom: 0;
            border-radius: 4px;
        }

        /* PANELS */
        .panel-title > .small,
        .panel-title > .small > a,
        .panel-title > a,
        .panel-title > small,
        .panel-title > small > a {
            color: inherit;
        }
        .page-heading {
            border-top: 0;
            padding: 0 10px 20px 10px;
        }
        .panel-heading h1,
        .panel-heading h2 {
            margin-bottom: 5px;
        }
        .panel-body {
            padding: 15px;
        }
        /* Bootstrap 3.3.x panels */
        .panel {
            margin-bottom: 20px;
            background-color: #fff;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .panel-heading {
            color: white;
            padding: 10px 15px;
            border-bottom: 1px solid transparent;
            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
        }
        .panel-footer {
            padding: 10px 15px;
            border-top: 1px solid #e7eaec;
            border-bottom-right-radius: 3px;
            border-bottom-left-radius: 3px;
        }
        .panel-default > .panel-heading {
            color: #333;
            background-color: #f5f5f5;
            border-color: #e7eaec;
        }
        .panel-default {
            border-color: #e7eaec;
        }
        .panel-group .panel + .panel {
            margin-top: 5px;
        }
        .panel-group .panel {
            margin-bottom: 0;
            border-radius: 4px;
        }
        .main {
            min-height: 250px;
        }
    </style>

    <div class="col-lg-12">
        <div class="col-lg-4 main">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Pending
                </div>
                <div class="panel-body">
                    @foreach($pending as $key => $picklists):
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <a href="#" class="open-picklist" status="">
                                <h5 class='panel-title'>
                                    {{$key}}
                                </h5>
                            </a>

                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4 main">
            <div class="panel panel-success">
                <div class="panel-heading">
                    Picking
                </div>
                <div class="panel-body">
                    @foreach($picking as $key => $picklists):
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <a href="#" class="open-picklist" status="">
                                <h5 class='panel-title'>
                                    {{$key}}
                                </h5>
                            </a>

                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4 main">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-info-circle"></i> 
                    Packing
                </div>
                <div class="panel-body">
                    @foreach($packing as $key => $picklists):
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <a href="#" class="open-picklist" status="">
                                <h5 class='panel-title'>
                                    {{$key}}
                                </h5>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

        <div class="col-lg-4 main">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    Ready To Dispatch
                </div>
                <div class="panel-body">
                    @foreach($dispatch as $key => $picklists):
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            <h5 class='panel-title'>
                                {{$key}}
                            </h5>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    <div class="modal inmodal fade" id="myModal5" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Modal title</h4>
                    <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                </div>
                <div class="modal-body">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
        $('.open-picklist').on('click', function () {
            
            $.ajax({
                type: "GET",
                url: '/admin/warehouse/getPicklist',
                success: function (response) {
                    $('.modal-body').html(response);
                    $('#myModal5').modal('show');
                }
            });
            
           
        });

    });
</script>
@endsection;




