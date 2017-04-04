$( document ).ready(function() {
    if (statusUrl) {
        setInterval(pull, pullInterval * 100)
    }
});

function pull() {
    $.ajax({
        method: "GET",
        url: statusUrl,
    })
        .done(function( msg ) {
            $("#results-container").html(msg);

            if (!done) {
                setInterval(pull, pullInterval * 100)
            }
        });
}