@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.channel-prices.search') }}" method="post" id="admin-search">
                         <input type="hidden" name="page" id="page" value="1">
                        {{ csrf_field() }}

                        <div style="margin-bottom: 10px;">
                            <label for="channel">Channel</label>
                            <select name="channel_id" id="channel" class="form-control select2">
                                <option value="">--Select--</option>
                                @foreach($channels as $objChannel)
                                <option @if($channel->id == $objChannel->id) selected="selected" @endif value="{{ $objChannel->id }}">{{ $objChannel->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="country">Category </label>
                            <select name="category" id="category" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="country">Brand </label>
                            <select name="brand" id="brand" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="status">Status </label>
                            <select name="product_status" id="status" class="form-control">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="product_name">Product Name</label>
                            <input name="product_name" id="product_name" class="form-control">
                        </div>

                        <button style="margin-top:26px;" type="button" class="btn btn-primary Search">Search</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9 search-results">

    </div>

</section>
<!-- /.content -->
@endsection

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Edit Product</h4>
            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary UpdateChannel">Save changes</button>
            </div>
        </div>
    </div>
</div>


@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        loadPagination();
         
         $('.Search').on('click', function (e) {
            href = $('#admin-search').attr('action');
            $('.search-results').html('<img class="loader" src="{{url(' / images / loading.gif')}}" alt="Loading"/>');
            $('.Search').text('Loading...');
            $('.Search').prop('disabled', true);
            var formdata = $('#admin-search').serialize();
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    $('.Search').html('<i class="fa fa-search"></i> Search');
                    $('.Search').prop('disabled', false);
                    $('.search-results').html(response);
                }
            });
        });
        
       $('.Export').on('click', function (e) {
            href = $('#admin-search').attr('action');
            var formdata = $('#admin-search').serialize();
            
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                  exportCSVFile(response, 'products');
                }
            });
        });
        
        $('.Search').click();

        $(document).on('click', '.Edit', function (e) {

            e.preventDefault();

            var href = $(this).attr("href");

            $.ajax({
                type: "GET",
                url: href,
                success: function (response) {

                    $('#myModal').find('.modal-body').html(response);
                   $('#myModal').modal('show');
                }
            });
        });

        $(document).on('mouseenter', '.product-div', function () {

            $(this).find(".btn-group").show();
        }).on('mouseleave', '.product-div', function () {
            $(this).find(".btn-group").hide();
        });
    });
    
    function convertToCSV(JSONData, ReportTitle, ShowLabel) {     

        //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
        var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;

        var CSV = '';    

        //This condition will generate the Label/Header
        if (ShowLabel) {
            var row = "";

            //This loop will extract the label from 1st index of on array
            for (var index in arrData[0]) {
                //Now convert each value to string and comma-seprated
                row += index + ',';
            }
    
            row = row.slice(0, -1);
            //append Label row with line break
            CSV += row + '\r\n';
        }

        //1st loop is to extract each row
        for (var i = 0; i < arrData.length; i++) {
            var row = "";
    
            //2nd loop will extract each column and convert it in string comma-seprated
            for (var index in arrData[i]) {
                row += '"' + arrData[i][index] + '",';
            }
    
            row.slice(0, row.length - 1);
            //add a line break after each row
            CSV += row + '\r\n';
        }

        if (CSV == '') {        
            alert("Invalid data");
            return;
        }   
        }
        
        function exportCSVFile(items, fileTitle) {
        
            var csv = this.convertToCSV(jsonObject);

            var exportedFilenmae = fileTitle + '.csv' || 'export.csv';

            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    
            if (navigator.msSaveBlob) { // IE 10+
                navigator.msSaveBlob(blob, exportedFilenmae);
            } else {
                var link = document.createElement("a");
         
                if (link.download !== undefined) { // feature detection
                    // Browsers that support HTML5 download attribute
                    var url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", exportedFilenmae);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }
</script>
@endsection;
