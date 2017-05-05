$(document).ready(function () {
    if (statusUrl && isRunning) {
        var intervalId = setInterval(function () {
            pull(intervalId)
        }, pullInterval * 1000)
    }

    $('.cluster .panel-heading span').click(function (element) {
        console.log(element);
    });
});

$(function () {
    $("#destination").autocomplete({
        source: destinations,
        minLength: 4,
        select: function (event, ui) {
            $('#country').val('');
            $('#region').val('');
            $('#place').val('');
            $('#' + ui.item.category).val(ui.item.value);
            $('#destination').val(ui.item.value);
            return false;
        },
        close: function (event, ui) {
            // If nothing selected
            if (!event.toElement) {
                $('#country').val('');
                $('#region').val('');
                $('#place').val('');
                $('#destination').val('');
            }
            return false;
        },
        response: function( event, ui ) {
            // If nothing selected
            window.somethingFound = ui.content.length > 0;
            return false;
        }
    })
        .autocomplete("instance")._renderItem = function (ul, item) {
        return $("<li>")
            .append(item.label)
            .appendTo(ul);
    };
    $("#destination").blur(function() {
        if (!window.somethingFound) {
            $('#country').val('');
            $('#region').val('');
            $('#place').val('');
            $('#destination').val('');
        }
    });
});

function pull(intervalId) {
    $.ajax({
        method: "GET",
        url: statusUrl,
    })
        .done(function (msg) {
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