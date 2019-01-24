
@include('layouts.errors-and-messages')
<div class="box">
    <form action="{{ route('admin.channel-prices.update', $channelPrice->id) }}" method="post" id="channelPriceForm" class="form" enctype="multipart/form-data">
        <div class="box-body">

            <div class='productCode'></div>

            {{ csrf_field() }}
            <input type="hidden" name="_method" value="put">
            <input type="hidden" name="added" id="added" value='0'>
            <input type="hidden" name="attribute_id" id="attribute_id">

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
        $class = in_array($attribute->id, $assignedAttributes) ? 'added' : '';
        ?>

        <li {{$class}} price='{{$attribute->price}}' class="list-group-item fist-item">
            <span class="float-right">{{$attribute->price}} </span>

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
        var attributeId = $(this).parent().attr('attributeid');
    });

    $('.variationList > li').on('click', function () {

        $('#variationWrapper').slideUp();
        $('#channelPriceForm').slideDown();
        $('#added').val(($(this).hasClass('added') ? 1 : 0));
        $('#price').val($(this).attr('price'));
        $('.productCode').html($(this).attr('name'));


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
                var obj = jQuery.parseJSON(response);

                if (obj.http_code == 400) {

                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");

                    $.each(obj.errors, function (key, value) {

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
