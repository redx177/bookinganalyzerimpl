String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

$(document).ready(function () {
    if (typeof statusUrl !== 'undefined' && statusUrl != '' && isRunning) {
        var intervalId = setInterval(function () {
            pull(intervalId)
        }, pullInterval * 1000)
    }

    // Remove duplicate destinations.
    var uniqueDestinations = [];
    var indices = [];
    $.each(destinations, function(i, el){
        if($.inArray(el.label, indices) === -1) {
            indices.push(el.label)
            uniqueDestinations.push(el);
        }
    });
    destinations = uniqueDestinations;

    if ($('#place').val() != '') {
        $('#destination').val($('#country').val() + ' > ' + $('#region').val() + ' > ' + $('#place').val());
    } else if ($('#region').val() != '') {
        $('#destination').val($('#country').val() + ' > ' + $('#region').val());
    } else if ($('#country').val() != '') {
        $('#destination').val($('#country').val());
    }

});

$(function () {
    $('#destination').autocomplete({
        source: destinations,
        minLength: 4,
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
    })
        .autocomplete('instance')._renderItem = function (ul, item) {
        return $('<li>')
            .append(item.label)
            .appendTo(ul);
    };
    $('#destination').blur(function () {
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