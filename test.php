// Bind click event to lost in post button
$(document).on("click", "#lostInPostBtn", function() {
    $('#createOrderSpinner').hide();
    var firstLineRef = $('#currentLineWrap .active').attr('data-line-ref');
    var wmsWarehouseRef = $('#currentLineWrap .active').attr('data-warehouse-ref');
    $('#searchBoxWrapper #current-line').val(firstLineRef);
    $('#searchBoxWrapper #warehouse-ref').val(wmsWarehouseRef);
    order.initProductAutoComplete('#freeTextLostinPost');
    $('body').removeClass('product-swap');
    $('body').addClass('lost-in-post');
    $('.replace-window').slideDown();
    /*        $('#currentLineWrap .current-line-ref').*/
});

// Bind click event to refund button
$(document).on("click", "#refundBtn", function() {
    preRefundCheck();
    var orderLineTicks = $('.orderline-refund i');
    orderLineTicks.on('click', function(){
        $(this).removeClass('pulsing').addClass('selected');
        $('.refund-window #continue-refund').attr('disabled', false).addClass('btn-success');
    });
});

// Bind click event to replace order button
$(document).on("click", "#replaceBtn", function() {
    var firstLineRef = $('#currentLineWrap .active').attr('data-line-ref');
    $('#searchBoxWrapper #current-line').val(firstLineRef);

    initProductAutoComplete('#SwapFinder');
    $('body').removeClass('lost-in-post');
    $('body').addClass('product-swap');
    $('.swap-window').slideDown();
});

// Bind click event on the current product to swap
$(document).on('change', '.replace-window #currentLineWrap .current-line-ref', function(e){
    var line = $(this).prev();
    var lineRef = line.attr('data-line-ref');
    var newOrder = $('#newOrder').find('div[data-line-ref="' + lineRef + '"]');
    line.toggleClass('removed');
    newOrder.toggleClass('removed');
    var allLines = $('.current-line-ref');
    allLines.removeClass('active');
    newOrder.removeClass('active');

    if(line.hasClass('removed')){
        line.removeClass('active');
        newOrder.removeClass('active');
    }else{
        line.addClass('active');
        newOrder.addClass('active');
    }
});

// Bind click event on the current product to swap
$(document).on('click', '.replace-window .current-line-ref', function(e){
    var lineRef = $(this).attr('data-line-ref');
    $('#freeTextLostinPost').attr('disabled', false).val('');
    $('#searchBoxWrapper #current-line').val(lineRef);
    var newOrder = $('#newOrder').find('div[data-line-ref="' + lineRef + '"]');
    var allLines = $('.current-line-ref');

    allLines.not($(this)).removeClass('active');
    newOrder.not($(this)).removeClass('active');

    $(this).addClass('active');
    newOrder.toggleClass('active');
});

// Bind click event on the current product to swap
$(document).on('click', '.swap-window .current-line-ref', function(e){
    var lineRef = $(this).attr('data-line-ref');
    $('#freeTextLostinPost').attr('disabled', false);
    $('#SwapFinder').val('');
    $('#searchBoxWrapper #current-line').val(lineRef);
    var allLines = $('.current-line-ref');
    allLines.not($(this)).removeClass('active');
    $(this).addClass('active');
});

// Bind click event to the Lost in post btn
$(document).on("click", "#replaceProduct", function(e){
    e.preventDefault();
    var lineRef = $('#searchBoxWrapper #current-line').val();
    replaceProductInOrder(lineRef);
});

// Bind click event on the product swap to btn
$(document).on("click", "#swapToSelectedProduct", function(e){
    e.preventDefault();
    var lineRef = $('#searchBoxWrapper #current-line').val();
    swapProductInOrder(lineRef);
});

// Bind click event on the submit new order
$(document).on("click", "#createNewOrder", function(e){
    e.preventDefault();
    $(this).attr("disabled","disabled");
    var type = "swap";
    if($('.product-check').css('display') == 'none'){
        var type = "lost";
    }
    $('#createOrderSpinner').fadeIn(600);
    createNewOrder(type);

});

$(document).on("click", "#swap-products", function(e){
    e.preventDefault();
    submitProductSwap();
});



// Bind click event to close swap window
$(document).on("click", "#cancel-swap", function(e) {
    e.preventDefault();
    $('body').removeClass('product-swap');
    location.reload();
});

// Bind click event to lost in post button
$(document).on("click", "#cancelReplace", function(e) {
    e.preventDefault();
    $('body').removeClass('lost-in-post');
    $('#order-details-refresh').trigger('click');
});


function replaceProductInOrder(lineRef){
    var originalProduct = $('.replace-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]');
    var newProduct = $('.replace-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]').clone();
    var productForSwap = $('.replace-window .selected-for-swap');
    var newProductCode = productForSwap.find('.product-code').val();
    var newProductTitle = productForSwap.find('.product-title').val();
    var newProductWarehouse = productForSwap.find('#warehouse-ref').val();
    var newProductStatus = originalProduct.attr('data-line-status');
    var newProductRrp = productForSwap.find('.product-rrp').val();
    var newProductStdCost = productForSwap.find('.product-std-cost').val();
    var newOrder = $('.replace-window #newOrder');
    newProduct.append('<input class="kondor_product_code" name="kondor_product_code[' + lineRef + ']" type="hidden" value="' + newProductCode + '" />');
    newProduct.append('<input class="customer_product_title" name="customer_product_title[' + lineRef + ']" type="hidden" value="' + newProductTitle + '" />');
    newProduct.append('<input class="wms_warehouse_ref" name="wms_warehouse_ref[' + lineRef + ']"' + ' type="hidden"' +
        ' value="' + newProductWarehouse + '" />');
    newProduct.append('<input class="rrp" name="rrp[' + lineRef + ']" type="hidden" value="' + newProductRrp + '" />');
    newProduct.append('<input class="stdCost" name="stdCost[' + lineRef + ']" type="hidden" value="' + newProductStdCost + '" />');
    newProduct.append('<input class="lineStatus" name="lineStatus[' + lineRef + ']" type="hidden" value="' + newProductStatus + '" />');
    newProduct.removeClass('active').attr('data-product-code', newProductCode).attr('data-original-product-code', originalProduct.attr('data-product-code'));
    newProduct.find('.product-code').html(newProductCode);
    newProduct.find('.product-title').html(newProductTitle);
    var swappedTitle = originalProduct.find('.product-code');
    $('.replace-window #freeTextLostinPost').val('');
    //~BR - lets draw the drop down - this is messy as, but without recoding the whole thing, I need to allow a Qty to be selected for the Line
    var Quantity = originalProduct.attr('data-line-quantity');
    var qtyDropdownHtml = '<br /><div class="col-sm-7 input-group input-group-sm pull-right">\n' +
        '            <span class="input-group-addon order-details-label">Swap Quantity</span>\n' +
        '        <select class="form-control quantity" name="quantity[' + lineRef + ']">';

    for(var qtyCounter = 1; qtyCounter <= Quantity; qtyCounter++){
        if(Number(qtyCounter) === Number(Quantity)){
            qtyDropdownHtml += '<option value="' + qtyCounter + '" selected>' + qtyCounter + '</option>';
        }else{
            qtyDropdownHtml += '<option value="' + qtyCounter + '">' + qtyCounter + '</option>';
        }
    }

    qtyDropdownHtml += '</select>' +
        '        </div>';

    newProduct.append(qtyDropdownHtml);
    newProduct.appendTo('.replaced-products');
    swappedTitle.html(swappedTitle.text() + '<i style="margin:0 0.5em;" class="fa fa-hand-o-right" aria-hidden="true"></i>' + newProductCode);
    $('.replace-window .swap-line #saveProductReplacementWrapper').show(500);
    $('.selected-for-swap').slideUp();
}

function preRefundCheck(){
    $('.refund-window').slideDown();
    $('.refund-help').fadeIn();
    $('.orderline-refund').removeClass("hide-me");

    //var orderLineTicks = $('.orderline-refund i');
    //orderLineTicks.addClass('pulsing');
}

function swapProductInOrder(lineRef){
    var originalProduct = $('.swap-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]');
    var newProduct = $('.swap-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]').clone();
    var productForSwap = $('.swap-window .selected-for-swap');
    var newproductCode = productForSwap.find('.product-code').val();
    var newproductTitle = productForSwap.find('.product-title').val();

    newProduct.removeClass('active').attr('data-product-code', newproductCode).attr('data-line-ref', lineRef).attr('data-original-product-code', originalProduct.attr('data-product-code'));
    newProduct.find('.product-code').html(newproductCode);
    newProduct.find('.product-title').html(newproductTitle);
    newProduct.appendTo('.swapped-products');
    var swappedTitle = originalProduct.find('.product-code');
    swappedTitle.html(swappedTitle.text() + '<i style="margin:0 0.5em;" class="fa fa-hand-o-right" aria-hidden="true"></i>' + newproductCode);
    $('.swap-window .swap-line #saveProductReplacementWrapper').show(500);
    $('.selected-for-swap').slideUp();
}

function createNewOrder(type){

    var strUrl = "/orders/replaceOrder";
    var newOrder = $('#newOrder');
    $.each(newOrder.children(), function(ind, val){
        var value = $(val);
        if(value.hasClass('removed')){
            newOrder.children().eq(ind).remove();
        }
    });

    newOrder = newOrder.serializeArray();
    var customerRef = $('#order-details-content .customer-ref').text();
    var orderRef = $('#order-details-content .order-details').attr('data-order-ref');
    var dbID = $('#order-details-content .order-details').attr('data-dbid');
    var lastUpdated = encodeURI($('#order-details-content .order-details').attr('data-last-updated'));
    var delivery = $('#onlyRMADeliveryDropDown2').val();
    var channelCode = $('.replace-window #searchBoxWrapper #channel').val();

    var objXhr = $.ajax({
        type: "POST",
        url: strUrl,
        data: {
            order : newOrder,
            orderRef: orderRef,
            customerRef: customerRef,
            dbID: dbID,
            channelCode: channelCode,
            lastUpdated:lastUpdated,
            delivery:delivery,
            type:type
        }
    });

    var objPromise = Promise.resolve(objXhr).then(function(response) {
        return response;
    });

    objPromise.abort = function() {
        objXhr.abort();
    };

    let getOrderResponse = promiseTimeout(Timeout_OrderSearch, objPromise);

    //Successful response
    getOrderResponse.then(response => {

        $('#createOrderSpinner').fadeOut(600);

    var response = JSON.parse(response);
    var strOut = "<div class='alert alert-success'>";
    $.each(response.body[0], function(ind, val){
        if(ind === 'text' || ind === 'title' || ind === 'msg'){
            strOut += "<p>" + val + "</p>";
        }
    });
    strOut += '</div>';
    $('.replace-window .response').html(strOut).addClass('active');

    $.each(response.koms[0].details, function(responseType, val){

        $.each(val, function(dbId, detail){

            if(responseType === 'SUCCESS') {
                $('.replace-window .response').append("<div class='alert alert-success'>" + detail + "</div>");
            }else{

                $.each(detail, function(key, value){

                    if((key === 'generic_errors' && typeof(value) === 'object') || (key === 'extra_info' && typeof(value) === 'object')) {
                        $('.replace-window .response').append("<div class='alert alert-danger'></div>");
                        $.each(value, function(errorType, message){
                            $('.replace-window .response .alert-danger').append("<p>" + message + "</p>");
                        });
                    }
                });
            }
        });
    });

});

    function  submitProductSwap(){
        var productsForSwap = $('.swapped-products');
        var arrData = [];
        var strUrl = "/product/inOrderSwap";

        $('.swap-window #newOrder .fa-refresh').show();

        // Format data for update
        $.each(productsForSwap.children(), function(ind, value){
            var newProductCode = $(value).attr('data-product-code');
            var lineRef = $(value).attr('data-line-ref');
            var lines = $('#order-details-line-container');
            var updateLine = lines.find('div[data-line-ref="' + lineRef + '"]');
            updateLine.find('.update-kondor-product-code').val(newProductCode);
            updateLine.find('.update-customer-product-code').val("");
            updateLine.find('[name="' + lineRef + '-line_status"]').append('<option value="2">Waiting Import</option>').val("2");
        });

        // Send update
        $.ajax({
            type: "POST",
            url: strUrl,
            data: {
                dbid: $('.order-details').attr('data-dbid')
            },
            success: function (response) {
                $('.swap-window #newOrder .fa-refresh').hide();
                var response = JSON.parse(response);

                if(response.http_code === 201 || response.http_code === 200){
                    $.each(response.details, function(responseType, val){

                        $.each(val, function(dbId, detail){

                            if(responseType === 'SUCCESS') {
                                $('.swap-window .response').append("<div class='alert alert-success'>" + detail + "</div>");
                            }else{

                                $.each(detail, function(key, value){

                                    if((key === 'generic_errors' && typeof(value) === 'object') || (key === 'extra_info' && typeof(value) === 'object')) {
                                        $('.swap-window .response').append("<div class='alert alert-danger'></div>");
                                        $.each(value, function(errorType, message){
                                            $('.swap-window .response .alert-danger').append("<p>" + message + "</p>");
                                        });
                                    }
                                });
                            }
                        });
                    });
                }
                $('.swap-window .response').slideDown();
                $('.swap-window #swap-products').hide();
                $('.swap-window #cancel-swap').html('Close');
                $('#SaveButtonContainer #SaveOrder').attr('disabled', true);
            }
        });
    }

function initProductAutoComplete(selector){
    var $ele = $(selector);
    var channelCode = $ele.attr('data-channel');

    // Init autocomplete swap product finder
    $ele.autocomplete({
        minLength:0,
        // Get and format data for other products on the same channel
        source: function(request, response) {

            var pattern = new RegExp(/^[a-zA-Z0-9\-_]+/);
            var arrData = {
                product_code: $ele.val().toUpperCase(),
                channelCode: channelCode
            };
            var strUrl = "/product/getProductAutoComplete";

            if($ele.val().match(pattern)) {

                var data = [];
                $.ajax({
                    type: "POST",
                    url: strUrl,
                    data: arrData,
                    success: function(search) {
                        var search = search;
                        if(search == false){
                            //$('#order-details-update-error').html(handleAccessDenied('message')).show().delay(5000).fadeOut();
                            $('.swap-window').slideUp();
                            return false;
                        }
                        search = $.parseJSON(search);
                        if(search.results.length > 0){

                            $.each(search.results, function(ind, val){

                                data.push({
                                    label: val.sku + " - " + val.description + " - "
                                    + val.warehouse,
                                    value: val.sku,
                                    product: {
                                        product_code: val.sku,
                                        product_title: val.description,
                                        product_id: val.id,
                                        rrp: val.price,
                                        freestock: val.quantity,
                                        warehouse: val.warehouse,
                                        image: val.url
                                    }
                                });
                            });
                            response(data);
                            $(".no-products").html('');
                        }else{
                            $(".no-products").html('');
                            $(".no-products").append('<h4 class="title">There are no products Found for this search</h4>');
                        }
                    }
                });
            } else {
                $(".no-products").html('');
            }
        },
        //Handle the click event on the autocomplete selection
        select: function(event, ui){
            $(".no-products").html('');
            $('.selected-for-swap .selected-product-code').html(ui.item.sku);
            $('.selected-for-swap .selected-product-title').html(ui.item.description);
            $('.selected-for-swap .selected-stock-lvl').html("Stock Level: " + ui.item.quantity);
            // hidden inputs
            $('.selected-for-swap .product-code').val(ui.item.product.sku);
            $('.selected-for-swap .product-title').val(ui.item.description);
            $('.selected-for-swap .freestock').val(ui.item.quantity);
            $('.selected-for-swap #warehouse-ref').val(ui.item.warehouse);
            $('.selected-for-swap .product-rrp').val(ui.item.price);
            $('.selected-for-swap .selected-image').html(
                "<img src='" + ui.item.product.image + "' alt='" + ui.item.description + "' />"
            );
            $('.selected-for-swap').slideDown();
        },

        open: function() {
            $('.ui-autocomplete').css({'position': 'fixed', 'border': 'none', 'display': 'block', 'z-index': 1000000});
            $('.ui-autocomplete li').css({'margin-bottom':'1px', 'font-size': '0.8em', 'line-height' : '1.4em', 'border-raduis' : 'none', 'background' : '#ddd', 'padding':'2px'});
        },
        close: function() {},
        focus: function(event,ui) {

        }
    });
}


<div class="row order-details-row-pad">
        <div class="col-lg-12 col-md-10 col-sm-6 col-xs-4 refund-window">

        <div class="panel panel-default">

        <div class="panel-heading block_title">
        <h3>Refund Order Lines</h3>
    </div>

    <div class="panel-body">
        <div class="col-lg-12 col-md-8 refund-help">
        <p class="message">Please select the order lines you wish to refund by clicking the tick box on the right hand side of the order line.</p>
    <button type="button" disabled="" class="btn btn-primary koms-submit-button" id="continue-refund">
        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Continue
        </button>
        <button type="button" class="btn btn-danger koms-cancel-button" id="cancelRefundBtn">
        <span class="glyphicon glyphicon-cross" aria-hidden="true"></span> Cancel
        </button>
        </div>
        </div>
        <div class="response"></div>
        </div>
        </div>

        <div class="col-lg-12 col-md-10 col-sm-6 col-xs-4 replace-window">

        <div class="panel panel-default">

        <div class="panel-heading block_title">
        <h3 class="lost-inpost-title">Create RMA Order</h3>
    </div>

    <div class="panel-body swap-line">
        <div class="col-lg-12 col-md-8 response"></div>
        <div id="currentLineWrap" class="col-lg-3 col-md-2">
        <h3>Current Products</h3>
    </div>

    <div id="searchBoxWrapper" class="col-lg-4 col-md-2">
        <label for="freeTextLostinPost">Replace to...</label>
    <input type="text" placeholder="Start typing to find a swappable product" class="form-control" data-channel="EEA/3/14" name="freeTextLostinPost" id="freeTextLostinPost">
        <p class="no-products"></p>
        <h4 class="title">Notice: Product codes may ONLY contain "a-z 0-9 - _"</h4>
    <input type="hidden" name="channel" id="channel" value="EEA/3/14">
        <input type="hidden" name="current-line" id="current-line" value="">
        <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
        </div>

        <div class="selected-for-swap col-lg-2 col-md-2">
        <div class="h4 selected-product-code"></div>
        <div class="selected-product-title"><p></p></div>
        <div class="selected-stock-lvl"></div>
        <div class="selected-image"></div>
        <input type="hidden" name="product-title" class="product-title" value="">
        <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
        <input type="hidden" name="line-status" class="line-status" value="">
        <input type="hidden" name="product-code" class="product-code" value="">
        <input type="hidden" name="product-image" class="product-image" value="">
        <input type="hidden" name="freestock" class="freestock" value="">
        <input type="hidden" name="product-rrp" class="product-rrp" value="">
        <input type="hidden" name="product-std-cost" class="product-std-cost" value="">
        <button id="replaceProduct" class="btn btn-primary koms-submit-button">Swap To This</button>
    </div>

    <div id="saveProductReplacementWrapper" class="col-lg-3 col-md-2">

        <div class="swapped-product">
        <h3>New order content</h3>
    </div>

    <form id="newOrder">
        <div class="replaced-products"></div>
        </form>
        <div id="rma-delivery-select">
        <div class="col-sm-11 input-group input-group-sm pull-right">
        <span class="input-group-addon order-details-label">Select Delivery Code:</span>
    <select class="form-control" name="delivery" id="onlyRMADeliveryDropDown2">

        <option value="RMA_INT_B4_3PM_UPG" selected="">RMA_INT_B4_3PM_UPG</option>
        </select>
        </div>
        </div>
        <button id="createNewOrder" class="btn btn-primary koms-submit-button pull-right" action="CreateOrder">Create Order</button>
    <button id="cancelReplace" class="btn btn-danger koms-cancel-button pull-right">Cancel</button>
        <i id="createOrderSpinner" class="fa fa-circle-o-notch fa-spin pull-right icon-btn bulk-download-spinner" hidden=""></i>
        </div>
        </div>
        </div>
        </div>
        <script>
        function openOrder(customerRef)
        {
            window.open("http://koms.kondor.tes/sales/ordersearch/?customer_ref=" + customerRef);
        }
        </script>

        <div class="col-lg-12 col-md-10 col-sm-6 col-xs-4 swap-window">

        <div class="panel panel-default">

        <div class="panel-heading block_title">
        <h3 class="product-swap-title">Swap Products</h3>
    </div>

    <div class="panel-body swap-line">
        <div class="col-lg-12 col-md-8 response"></div>
        <div id="currentLineWrap" class="col-lg-3 col-md-2">
        <h3>Current Products</h3>
    </div>

    <div id="searchBoxWrapper" class="col-lg-4 col-md-2">
        <h3>Replace to...</h3>
    <input type="text" placeholder="Start typing to find a swappable product" class="form-control" data-channel="EEA/3/14" name="freeTextLostinPost" id="SwapFinder">
        <input type="hidden" name="channel" id="channel" value="EEA/3/14">
        <input type="hidden" name="current-line" id="current-line" value="">
        <p class="no-products"></p>
        <h4 class="title">Notice: Product codes may ONLY contain "a-z 0-9 - _"</h4>
    </div>

    <div class="selected-for-swap col-lg-2 col-md-2">
        <div class="h4 selected-product-code"></div>
        <div class="selected-product-title"><p></p></div>
        <div class="selected-stock-lvl"></div>
        <div class="selected-image"></div>
        <input type="hidden" name="product-title" class="product-title" value="">
        <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
        <input type="hidden" name="product-code" class="product-code" value="">
        <input type="hidden" name="freestock" class="freestock" value="">
        <input type="hidden" name="product-image" class="product-image" value="">
        <input type="hidden" name="product-rrp" class="product-rrp" value="">
        <input type="hidden" name="product-std-cost" class="product-std-cost" value="">
        <button id="swapToSelectedProduct" class="btn btn-primary koms-submit-button">Swap To This</button>
    </div>

    <div id="saveProductReplacementWrapper" class="col-lg-3 col-md-2">

        <div class="swapped-product">
        <h3>Products to be swapped</h3>
    </div>

    <form id="newOrder">
        <div class="swapped-products"></div>
        <i style="display:none;" class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
        <button id="swap-products" class="btn btn-primary koms-submit-button pull-right">Swap Products</button>
    <button id="cancel-swap" class="btn btn-danger koms-cancel-button pull-right">Cancel</button>
        </form>
        </div>
        </div>
        </div>
        </div>        </div>
