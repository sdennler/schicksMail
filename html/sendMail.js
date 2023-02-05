$(function() {
    var form = $('#sendMail');
    var mesg = $('#sendMailResponse');

    $(form).submit(function(event) {
        $(mesg).removeClass('error');
        $(mesg).removeClass('success');
        $(mesg).html('Sending your message ✉ ✈ 🙌');
        event.preventDefault();

        $.ajax({
            type: 'POST',
            url: $(form).attr('action'),
            data: $(form).serialize()
        })

        .done(function(response) {
            $(mesg).addClass('success');
            $(mesg).html(response.replace(/\n/g, '<br>'));
            $(form)[0].reset();
        })

        .fail(function(data) {
            $(mesg).addClass('error');
            if (data.responseText !== '') {
                $(mesg).html(data.responseText.replace(/\n/g, '<br>'));
            } else {
                $(mesg).html('Oh no! Some thing went wrong! 😕');
            }
        });

    });
});
