$( document ).ready(function() {
    if (statusUrl) {
        var intervalId = setInterval(function() { pull(intervalId) }, pullInterval * 1000)
    }
});

function pull(intervalId) {
    $.ajax({
        method: "GET",
        url: statusUrl,
    })
        .done(function( msg ) {
            $("#results-container").html(msg);

            if (done) {
                clearInterval(intervalId);
            }
        });
}