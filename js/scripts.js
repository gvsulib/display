

$( document ).ready(function() {

    $('.areas-container').hide();
    $('.traffic-legend').hide();

    resetButtons();
    getFloor();
    getTraffic();
});

$(".atrium-floor-button").click(function() {
    selectAtrium();
});
$(".first-floor-button").click(function() {
    selectFirstFloor();
});
$(".second-floor-button").click(function() {
    selectSecondFloor();
});
$(".third-floor-button").click(function() {
    selectThirdFloor();
});
$(".fourth-floor-button").click(function() {
    selectFourthFloor();
});

function selectAtrium() {
    resetButtons();
    $( ".atrium-floor" ).show();
    $( ".atrium-floor-button" ).addClass("selected");
}
function selectFirstFloor() {
    resetButtons();
    $( ".first-floor" ).show();
    $( ".first-floor-button" ).addClass("selected");
}
function selectSecondFloor() {
    resetButtons();
    $( ".second-floor" ).show();
    $( ".second-floor-button" ).addClass("selected");
}
function selectThirdFloor() {
    resetButtons();
    $( ".third-floor" ).show();
    $( ".third-floor-button" ).addClass("selected");
}
function selectFourthFloor() {
    resetButtons();
    $( ".fourth-floor" ).show();
    $( ".fourth-floor-button" ).addClass("selected");
}

function resetButtons() {
    $( ".room-big-container" ).children().hide();
    $(".floors").find('li').removeClass("selected");

    $( ".areas-container" ).children().hide();
}

function getFloor() {
    var hash = location.hash.replace("#","");
    hash = parseInt(hash);
    switch(hash) {
        case 0:
            selectAtrium();
            break;
        case 1:
            selectFirstFloor();
            break;
        case 2:
            selectSecondFloor();
            break;
        case 3:
            selectThirdFloor();
            break;
        case 4:
            selectFourthFloor();
            break;
        default:
            selectThirdFloor();
    }
}

function checkTime(i) {
if (i < 10) {
    i = "0" + i;
}
return i;
}

function startTime() {
    var today = new Date();

    var month = today.getMonth();
    var day = today.getDate();

    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    var ampm = 'AM';

    if (h > 12) {
        ampm = 'PM';
        h = h - 12;
    }

    // add a zero in front of numbers<10
    m = checkTime(m);
    s = checkTime(s);

    document.getElementById('time').innerHTML = h + ":" + m + " " + ampm;
    t = setTimeout(function () {
        startTime()
    }, 500);
}
startDay();


function startDay() {
    var today = new Date();
    var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

    document.getElementById('day').innerHTML = monthNames[today.getMonth()] + " " + today.getDate();
    t = setTimeout(function () {
        startTime()
    }, 500);
}
startDay();


getWeather(); //Get the initial weather.
setInterval(getWeather, 600000); //Update the weather every 10 minutes.

function getWeather() {
    $.simpleWeather({
        location: 'Allendale, MI',
        woeid: '',
        unit: 'f',
        success: function(weather) {
            html = '<i class="icon-'+weather.code+'"></i>'+weather.temp+'<span>&deg;'+weather.units.temp+'</span>';

            $("#weather").html(html);
        },
        error: function(error) {
            $("#weather").html('--<span>&deg;F</span>');
        }
    });
}

function getTraffic() {

    console.log('traffic getJSON ...');
    $.getJSON( "getTraffic.php", function( data ) {

        $('.spinner').hide();

        console.log(data);

        $('#atrium_exhibition_room').removeClass().addClass(getColor(data["Atrium: Exhibition Room"]));
        $('#atrium_living_room').removeClass().addClass(getColor(data["Atrium: Living Room"]));
        $('#atrium_multipurpose_room').removeClass().addClass(getColor(data["Atrium: Multi-Purpose Room"]));
        $('#atrium_seating_area').removeClass().addClass(getColor(data["Atrium: Seating Outside 001 and 002"]));
        $('#atrium_under_stairs').removeClass().addClass(getColor(data["Atrium: Tables under Stairs"]));

        $('#first_knowledge_market').removeClass().addClass(getColor(data["1st Floor: Knowledge Market"]));
        $('#first_cafe_seating').removeClass().addClass(getColor(data["1st Floor: Cafe Seating"]));

        $('#second_collaboration_space').removeClass().addClass(getColor(data["2nd Floor: West Wing (Collaborative Space)"]));
        $('#second_quiet_space').removeClass().addClass(getColor(data["2nd Floor: East Wing (Quiet Space)"]));

        $('#third_innovation_zone').removeClass().addClass(getColor(data["3rd Floor: Innovation Zone"]));
        $('#third_collaboration_space').removeClass().addClass(getColor(data["3rd Floor: West Wing (Collaborative Space)"]));
        $('#third_reading_room').removeClass().addClass(getColor(data["3rd Floor: Reading Room"]));
        $('#third_quiet_space').removeClass().addClass(getColor(data["3rd Floor: East Wing (Quiet Space)"]));

        $('#fourth_collaboration_space').removeClass().addClass(getColor(data["4th Floor: West Wing (Collaborative Space)"]));
        $('#fourth_reading_room').removeClass().addClass(getColor(data["4th Floor: Reading Room"]));
        $('#fourth_quiet_space').removeClass().addClass(getColor(data["4th Floor: East Wing (Quiet Space)"]));

    })
    .done(function() { console.log('traffic getJSON request succeeded!'); $('.areas-container').fadeIn(); $('.traffic-legend').fadeIn();})
    .fail(function(jqXHR, textStatus, errorThrown) { console.log('traffic getJSON request failed! ' + textStatus); console.log('traffic getJSON begin another attempt ...'); getTraffic(); })
    .always(function() { console.log('traffic getJSON request ended!'); });
}

function getColor(traffic) {
    switch (traffic) {
        case 'Empty':
            return 'green';
        case 'A few students':
            return 'green';
        case 'Half full':
            return 'yellow';
        case 'Mostly full':
            return 'red';
        case 'Totally full':
            return 'red';
        default:
            return 'grey';
    }
}



