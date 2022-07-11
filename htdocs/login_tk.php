<script>
    $(document).ready(function () {
        $.ajax({
            url: 'http://ticketglv.fast-data.it/scp/login_unificato.php?username=tecnico1&password=iniziale',
            dataType: 'jsonp',
            success: function (dataWeGotViaJsonp) {
                var text = '';


                $('#login_ticket').html(text);
            }
        });
    })
</script>

