
@include('layouts.errors-and-messages')
<div class="box">
    <form action="{{ route('admin.channel-prices.update', $channelPrice->id) }}" method="post" id="channelPriceForm" class="form" enctype="multipart/form-data">
        <div class="box-body">

            <div class='productCode'></div>

            {{ csrf_field() }}
            <input type="hidden" name="_method" value="put">
            <input type="hidden" name="added" id="added" value='0'>
            <input type="hidden" name="attribute_id" id="attribute_id">
            <input type="hidden" name="product_id" id="product_id" value="{{ $channelPrice->product_id }}">
            <input type="hidden" name="channel_id" id="channel_id" value="{{ $channelPrice->channel_id }}">

            <div class="form-group">
                <label for="alias">Price <span class="text-danger">*</span></label>
                <input type="text" name="price" id="price" placeholder="Price" class="form-control" value="{{ empty($channelPrice->price) || $channelPrice->price <= 0 ? $product->price : $channelPrice->price }}">
            </div>

            <div class="form-group">
                <label for="alias">Description <span class="text-danger">*</span></label>
                <textarea name="description" id="alias" placeholder="Description" class="form-control"><?= (!empty($channelPrice->description) ? strip_tags($channelPrice->description) : strip_tags($product->description)) ?></textarea>
            </div>
        </div>

        <button class='cancelChanges' type='button'>cancel</button>
    </form>
</div>

<div id='variationWrapper'>
    <ul class="list-group clear-list variationList">
        @foreach($attributes as $attribute)

        <?php
        $price = isset($channel_varaitions[$attribute->id]) ? $channel_varaitions[$attribute->id]->price : $attribute->price;
        ?>

        <li attribute-id="{{$attribute->id}}" price='{{$price}}' class="list-group-item fist-item @if(in_array($attribute->id, $assignedAttributes)) added @endif">
            <span class="float-right">{{$price}} </span>

            @foreach($attribute->attributesValues as $value)
            {{ $value->attribute->name }} : {{ ucwords($value->value) }}
            @endforeach

            @if(in_array($attribute->id, $assignedAttributes))
            <a href='#' class='removeVariation'>x</a>
            <img src=''>
            @endif

        </li>

        @endforeach;

    </ul>
</div>

<!-- /.box -->

<!-- /.content -->

<script>

    $('.cancelChanges').on('click', function () {

        $('#channelPriceForm').slideUp();
        $('#variationWrapper').slideDown();


    });

    $('.removeVariation').on('click', function () {
        var attributeId = $(this).parent().attr('attribute-id');

        var $this = $(this);

        $.ajax({
            type: 'DELETE',
            url: '/admin/channel-prices/deleteAttribute/' + attributeId,
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            data: {channel_id: $('#channel_id').val(), "_token": "{{ csrf_token() }}"},

            success: function (data) {
                $this.parent().remove();
            },
            error: function (data) {
                alert(data);
            }
        });
        return false;
    });

    $('.variationList > li').on('click', function () {

        if ($(this).hasClass('added')) {
            added = 0;
        } else {
            added = 1;
        }

        $('#variationWrapper').slideUp();
        $('#channelPriceForm').slideDown();
        $('#added').val(added);
        $('#price').val($(this).attr('price'));
        $('.productCode').html($(this).attr('name'));
        $('#attribute_id').val($(this).attr('attribute-id'));


    });

    $('.UpdateChannel').on('click', function (e) {

        e.preventDefault();

        $('.modal-body .alert-danger').remove();

        $('.variationList > li[attributeid="' + $('#attribute_id').val() + '"]').addClass('added');
        $('.variationList > li[attributeid="' + $('#attribute_id').val() + '"]').append('a href="#" class="removeVariation">');

        var formdata = $('#channelPriceForm').serialize();
        var href = $('#channelPriceForm').attr('action');

        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            success: function (response) {

                if (response.http_code == 400) {

                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");

                    $.each(response.errors, function (key, value) {

                        $('.modal-body .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.modal-body').prepend("<div class='alert alert-success'>Product has been updated successfully</div>");

                    if ($('.variationList > li').length > 0) {
                        $('#channelPriceForm').slideUp();
                        $('#variationWrapper').slideDown();
                    }

                }

            }
        });
    });
</script>
