$( document ).ready(function() {
    if (statusUrl && isRunning) {
        var intervalId = setInterval(function() { pull(intervalId) }, pullInterval * 1000)
    }

    $('.cluster .panel-heading span').click(function (element) {
       console.log(element);
    });
});

function pull(intervalId) {
    $.ajax({
        method: "GET",
        url: statusUrl,
    })
        .done(function( msg ) {
            $("#results-container").html(msg);
            $(".abort").click(abort);

            if ($('#done').length > 0) {
                clearInterval(intervalId);
            }
        });
}

function abort() {
    $.ajax({
        method: "GET",
        url: "/Services/Apriori/apriori.php?abort=1",
    });
}