
<div class="list-group">
    <h3>Sort By</h3>
    <select id="order_by" class="form-control">
        <option value="products.created_at DESC" selected="selected">Latest Releases</option>
        <option value="channel_product.price ASC">Price, Low to High</option>
        <option value="channel_product.price DESC">Price, High to Low</option>
        <!--        <option value="pbs.qty_sold DESC">Most Popular</option>-->
        <option value="products.quantity DESC">Available Stock, High to Low</option>
        <option value="products.name ASC">A-Z</option>
        <option value="products.name DESC">Z-A</option>    
    </select>
</div>

<div class="list-group">
    <h3>Price</h3>

    <input type="hidden" id="hidden_minimum_price" value="{{$min}}" />
    <input type="hidden" id="hidden_maximum_price" value="{{$max}}" />
    <p id="price_show">{{$min}} - {{$max}}</p>
    <div id="price_range"></div>
</div>    

<div class="list-group">
    <h3>In Stock Only</h3>

    <input type="checkbox" id="stock" value="1" />
</div> 

<div class="list-group">
    <h3>Brand</h3>
    <div style="height: 180px; overflow-y: auto; overflow-x: hidden;">

        @foreach($brands as $brand)
        <div class="list-group-item checkbox">
            <label><input type="checkbox" class="common_selector brand" value="{{$brand->id}}">{{$brand->name}}</label>
        </div>
        @endforeach;
        <?php
        // }
        ?>
    </div>
</div>   

<script>
    
    var page = 1;
    
    $(window).on('hashchange', function() {
        if (window.location.hash) {
            var page = window.location.hash.replace('#', '');
            if (page == Number.NaN || page <= 0) {
                return false;
            } else {
                getPosts(page);
            }
        }
    });

    $(document).ready(function () {
    filter_data();
    
    $('body').on('click', '.pagination a', function(e) {
        e.preventDefault();

        $('#load a').css('color', '#dfecf6');
        $('#load').append('<img style="position: absolute; left: 0; top: 0; z-index: 100000;" src="/images/loading.gif" />');

        page = $(this).attr('href').split('page=')[1];
        
        filter_data();
    });
    
    $('#stock').change(function() {
    filter_data();
    });
    $('#order_by').on('change', function () {
    filter_data();
    });
//    $('.categories-list > li').on('click', function(e) {
//    if ($(this).hasClass('active')) {
//
//    return false;
//    }
//
//    $('.categories-list > li').removeClass('active');
//    $(this).addClass('active');
//    
//    reloadPage($(this));
//    e.preventDefault();
//    });
    function reloadPage(element) {
    var category = $('.categories-list > li.active').attr('category-id');
    var href = element.find('a').attr('href');
    
    $.ajax({
    url: href,
            method:"GET",
            success:function(data){
            $('.container').html(data);
            }
    });
    }

    $('.common_selector').click(function () {

    filter_data();
    });
    $('#price_range').slider({
    range: true,
            min: {{$min}},
            max: {{$max}},
            values: [{{$min}}, {{$max}}],
            step: 1,
            stop: function (event, ui)
            {
            $('#price_show').html(ui.values[0] + ' - ' + ui.values[1]);
            $('#hidden_minimum_price').val(ui.values[0]);
            $('#hidden_maximum_price').val(ui.values[1]);
            filter_data();
            }
    });
    });
    function filter_data()
    {
    $('.filter_data').html('<div id="loading" style="" ></div>');
    var action = 'fetch_data';
    var minimum_price = $('#hidden_minimum_price').val();
    var order_by = $('#order_by').val();
    var maximum_price = $('#hidden_maximum_price').val();
    var in_stock = $('#stock').is(':checked');
    var brand = get_filter('brand');
    var category = $('.categories-list > li.active').attr('category-id');
    //var ram = get_filter('ram');
    //var storage = get_filter('storage');

    $.ajax({
    url:"{{ route('filter.product') }}",
            method:"POST",
            data:{
            order_by:order_by,
                    action:action,
                    page:page,
                    category: category,
                    minimum_price:minimum_price,
                    maximum_price:maximum_price,
                    brand:brand,
                    in_stock:in_stock,
                    "_token": "{{ csrf_token() }}"
            },
            success:function(data){
            $('.filter_data').html(data);
              location.hash = page;
            }
     }).fail(function () {
            alert('Posts could not be loaded.');
        });
    }

    function get_filter(class_name)
    {
    var filter = [];
    $('.' + class_name + ':checked').each(function () {
    filter.push($(this).val());
    });
    return filter;
    }
</script>