<html>
    <head>
        <title>Ajax Example</title>
        <meta name="csrf-token" content="<?php echo csrf_token() ?>" />    
        <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js">
        </script>

        <script type="text/javascript">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>

        <script>
            function getMessage() {
                $.ajax({
                    type: 'POST',
                    url: '/getmsg',
                    data: '',
                    success: function (data) {
                        $("#msg").html(data.msg);
                    }
                });
            }
        </script>
    </head>

    <body>


        <div id = 'msg'>This message will be replaced using Ajax. 
            Click the button to replace the message.</div>
        <?php
        echo Form::button('Replace Message', ['onClick' => 'getMessage()']);
        ?>
        </br>


    </body>

</html>