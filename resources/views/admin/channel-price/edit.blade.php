
@include('layouts.errors-and-messages')
<div class="box">
    <form action="{{ route('admin.channel-prices.update', $channelPrice->id) }}" method="post" id="channelPriceForm" class="form" enctype="multipart/form-data">
        <div class="box-body">
            {{ csrf_field() }}
            <input type="hidden" name="_method" value="put">

            <div class="form-group">
                <label for="alias">Price <span class="text-danger">*</span></label>
                <input type="text" name="price" id="alias" placeholder="Price" class="form-control" value="{{ empty($channelPrice->price) || $channelPrice->price <= 0 ? $product->price : $channelPrice->price }}">
            </div>

            <div class="form-group">
                <label for="alias">Description <span class="text-danger">*</span></label>
                <textarea name="description" id="alias" placeholder="Description" class="form-control"><?= (!empty($channelPrice->description) ? strip_tags($channelPrice->description) : strip_tags($product->description)) ?></textarea>
            </div>
        </div>
    </form>
</div>

<ul class="list-group clear-list">
    @foreach($attributes as $attribute)

    <li class="list-group-item fist-item">
        <span class="float-right">{{$attribute->price}} </span>
        Please contact me
    </li>
    
    @endforeach;

</ul>

<!-- /.box -->

<!-- /.content -->

<script>

    $('.UpdateChannel').on('click', function (e) {

        e.preventDefault();

        $('.modal-body .alert-danger').remove();

        var formdata = $('#channelPriceForm').serialize();
        var href = $('#channelPriceForm').attr('action');

        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            success: function (response) {
                var obj = jQuery.parseJSON(response);

                if (obj.http_code == 400) {

                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");

                    $.each(obj.errors, function (key, value) {

                        $('.modal-body .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.modal-body').prepend("<div class='alert alert-success'>Product has been updated successfully</div>");
                }

            }
        });
    });
</script>
