String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

$(document).ready(function () {
    if (typeof statusUrl !== 'undefined' && statusUrl != '' && isRunning) {
        var intervalId = setInterval(function () {
            pull(intervalId)
        }, pullInterval * 1000)
    }

    if ($('#place').val() != '') {
        $('#destination').val($('#country').val() + ' > ' + $('#region').val() + ' > ' + $('#place').val());
    } else if ($('#region').val() != '') {
        $('#destination').val($('#country').val() + ' > ' + $('#region').val());
    } else if ($('#country').val() != '') {
        $('#destination').val($('#country').val());
    }
    if ($('#CUORT').val() != '') {
        $('#customerDestination').val($('#CUCNTRY').val() + ' > ' + $('#CUORT').val());
    } else if ($('#CUCNTRY').val() != '') {
        $('#customerDestination').val($('#CUCNTRY').val());
    }

});

$(function () {
    $('#destination').autocomplete({
        source: 'destinations.php',
        minLength: 4,
        dataType: 'json',
        select: function (event, ui) {
            $('#country').val(ui.item.country);
            $('#region').val(ui.item.region);
            $('#place').val(ui.item.place);
            $('#destination').val(ui.item.label.replaceAll('&gt;', '>'));
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
        response: function (event, ui) {
            // If nothing selected
            window.somethingFound = ui.content.length > 0;
            return false;
        }
    });
    $('#destination').blur(function () {
        if (!window.somethingFound) {
            $('#country').val('');
            $('#region').val('');
            $('#place').val('');
            $('#destination').val('');
        }
    });
});

$(function () {
    $('#customerDestination').autocomplete({
        source: 'customerDestinations.php',
        minLength: 2,
        select: function (event, ui) {
            $('#CUCNTRY').val(ui.item.CUCNTRY);
            $('#CUORT').val(ui.item.CUORT);
            $('#customerDestination').val(ui.item.label.replaceAll('&gt;', '>'));
            return false;
        },
        close: function (event, ui) {
            // If nothing selected
            if (!event.toElement) {
                $('#CUCNTRY').val('');
                $('#CUORT').val('');
                $('#customerDestination').val('');
            }
            return false;
        },
        response: function (event, ui) {
            // If nothing selected
            window.somethingFound = ui.content.length > 0;
            return false;
        }
    });
    $('#customerDestination').blur(function () {
        if (!window.somethingFound) {
            $('#CUCNTRY').val('');
            $('#CUORT').val('');
            $('#customerDestination').val('');
        }
    });
});

function pull(intervalId) {
    $.ajax({
        method: 'GET',
        url: statusUrl,
    })
        .done(function (msg) {
            $('#results-container').html(msg);
            $('.abort').click(abort);

            $('.cluster .panel-heading').click(function (element) {
                element = $(element.currentTarget);
                element.siblings('.panel-body').toggle();
                element.find('.glyphicon-eye-open').toggle();
                element.find('.glyphicon-eye-close').toggle();
            });

            if ($('#done').length > 0) {
                clearInterval(intervalId);
            }
        });
}

function abort() {
    $.ajax({
        method: 'GET',
        url: '/Services/Apriori/apriori.php?abort=1',
    });
}