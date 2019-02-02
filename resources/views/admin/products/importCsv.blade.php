<form id="importForm" action="/admin/products/saveImport" method="post"enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="file" id="csv_file" name="csv_file">
</form>

<script>
    $('.SaveImport').off();
    $('.SaveImport').on('click', function () {

        var href = $('#importForm').attr('action');
        var formdata = new FormData($('#importForm')[0]);
        $('.saveImport').prop('disabled', true);
        $('.modal-body .alert-danger').remove();
        $('.modal-body .alert-success').remove();

        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.http_code == 400) {

                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");

                    $.each(response.arrErrors, function (lineId, arrLine) {

                        $.each(arrLine, function (field, message) {

                            $('.modal-body .alert-danger').append("<p> Line Id: " + lineId + " message: " + message + "</p>");
                        });
                    });
                } else {
                    $('.modal-body').prepend("<div class='alert alert-success'>Import was successful</div>");

                }

                $('.saveImport').prop('disabled', false);
            }
        });
    });
</script>

