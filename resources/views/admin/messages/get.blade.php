<div class="modal-dialog">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i class="fa fa-laptop modal-icon"></i>
            <h4 class="modal-title">Messages</h4>

        </div>
        <div class="modal-body">

            <div class="existing-messages col-lg-12" style="margin-bottom: 12px; border: 1px solid #CCC;">

                @foreach($messages as $message)
                <div style="border-bottom: 1px #CCC dotted; padding:6px;" class="col-lg-12">

                    <div class="col-lg-4 pull-right">
                        {{$message->created_at}}
                    </div>

                    <div class="col-lg-8 pull-right">
                        {{$message->subject}}
                    </div>
                    {{$message->body}}<br>
                </div>



                @endforeach
            </div>



            <form id='backorderForm'>

                {{ csrf_field() }}

                <input type='hidden' id='order_id' name='order_id' value="{{$order->id}}" class='form-control'>
                <input type='hidden' id='message_type' name='message_type' value="1" class='form-control'>
                <input type="hidden" name="thread_id" value="<?= (isset($messages[0]) ? $messages[0]->thread_id : '') ?>">
                <input type="hidden" id='email_address' name="email_address" value="{{$order->customer->email}}">

                <div class="form-group">
                    <label>Subject</label> 
                    <input type='text' id='subject' name='subject' class='form-control'>
                </div>

                <div class="form-group">
                    <label>Comment</label> 
                    <textarea id='comment' name='message' class='form-control'></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary saveBackorderForm">Save changes</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        $('.saveBackorderForm').on('click', function () {
            var formdata = $('#backorderForm').serialize();


            $.ajax({
                type: "POST",
                url: '/admin/message/store',
                data: formdata,
                success: function (response) {
                    var response = JSON.parse(response);

                    if (response.http_code === 400) {

                        $('.modal-body').prepend("<div class='alert alert-danger'>Unable to save message</div>");


                    } else {
                        $('.modal-body').prepend("<div class='alert alert-success'>Message was saved successfully</div>");
                        openMessage($('#order_id').val());
                    }
                }
            });

        });
    });
</script>