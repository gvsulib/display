$.ajaxSetup({ cache: false });
$( document ).ready(function() {
    var seconds = 1000;
    var minutes = seconds * 60;

    $(document).idleTimer(30 * seconds);
    $('.areas-container').hide();
    $('#area-traffic-legend').hide();

    $('.room-availability-floors').hide();
    $('#room-traffic-legend').hide();

    resetButtons();
    getTraffic();
    getRoomAvailability();
    updateComputerAvailability();
    selectFloor(floor);
    updateTime();
    getMessages();
    displayEmoji();

    setInterval(getTraffic, trafficDelay * minutes); // default 10 minutes
    setInterval(getRoomAvailability, roomsDelay * minutes); // default 3
    setInterval(updateComputerAvailability, 2 * minutes);
    setInterval(updateTime, 10 * seconds);
    setInterval(getMessages, 20 * seconds);
    $(document).on('idle.idleTimer',function(){selectFloor(floor)});

    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    }, false);

});

function displayEmoji(){
    emojione.imageType = 'svg';
    emojione.sprites = true;
    emojione.imagePathSVGSprites = 'img/emojione.sprites.svg'


    jQuery('.feedback ul li').each(function(){
        jQuery(this).html(emojione.toImage(jQuery(this).data('emoji')));
    });
}

function updateComputerAvailability(){
    console.log('refreshing iframe');
    jQuery('#cpumap').get(0).contentWindow.location = jQuery('#cpumap').attr('src');
}

function updateTime(){
    var now = moment();
    $day = $("#day");
    $time = $("#time");
    $day.html(now.format("MMMM D"));
    $time.html(now.format("h:mm A"));
}

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

function selectFloor(floor){
    switch (floor) {
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
    }
}

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

function getMessages(){
    jQuery.ajax({
        datatype: 'json',
        url: 'getMessages.php?cache=' + new Date().getTime(),
        method: 'GET',
        success: function(message){
            var container = jQuery('#message');
            var iframe = jQuery('#notifications');
            if (message != null && message != 'null'){
                message = JSON.parse(message);
                container.fadeIn();
                iframe.fadeOut();
                container.find('.message-heading').text(message.heading);
                container.find('.message-post-time').text('Alert: ' + moment(message.entrydate).format("h:mm A"));
                container.find('p').text(message.body);
            } else {
                container.fadeOut();
                iframe.fadeIn();
            }
        }
    });
}

function getTraffic() {

    console.log('traffic getJSON ...');
    $.ajax({
        dataType: 'json',
        url: 'getTraffic.php?cache=' + new Date().getTime(),
        method: "GET",
        success: parseData
    }).done(function () {
        console.log('traffic getJSON request succeeded!');
        $('.spinner').hide();
        $('.areas-container').fadeIn();
        $('.traffic-legend').fadeIn();
    })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.log('traffic getJSON request failed ' + textStatus);
            console.log('traffic getJSON begin another attempt...');
        })
        .always(function () {
            console.log('traffic getJSON request ended');
        });
}


function parseData(data){
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

    $('#last-updated').html(data.updated);
}

function getColor(traffic) {
    switch (traffic) {
        case '4':
	    case '-1':
            return 'red';
        case '3':
            return 'orange';
        case '2':
            return 'yellow';
        case '1':
        case '0':
            return 'green';
        default:
            return 'grey';
    }
}


function getRoomAvailability() {
    console.log('getting room availability data... ');

    /*
    7678 / 003 - Media Prep Room
    7679 / 004 - Media Prep Room
    7680 / 005 - Media Prep Room
    7686 / 133 - Playback
    7687 / 134 - Presentation Practice
    7688 / 135 - Presentation Practice
    7689 / 202 - Conference Style
    7690 / 203 - Conference Style
    7801 / 204 - Lounge Style
    7691 / 205 - Conference Style
    7692 / 216 - Seminar Room
    7693 / 302 - Conference Style
    7694 / 303 - Lounge Style
    7695 / 304 - Conference Style
    7696 / 305 - Conference Style
    7698 / 404 - Conference Style
    7699 / 405 - Conference Style
    7681 / 030 - Multi-Purpose Room
    */

    var roomIds = {
        r003:"7678",
        r004:"7679",
        r005:"7680",
        r133:"7686",
        r134:"7687",
        r135:'7688',
        r202:'7689',
        r203:'7690',
        r204:'7801',
        r205:'7691',
        r216:'7692',
        r302:'7693',
        r303:'7694',
        r304:'7695',
        r305:'7696',
        r404:'7698',
        r405:'7699',
        r030:'7681'
    };

    for (var key in roomIds) {
        if (roomIds.hasOwnProperty(key)) {
            getRoomData(roomIds[key]);
        }
    }

    $('.room-availability-floors').fadeIn();
    $('#room-traffic-legend').fadeIn();

    function getRoomData(roomId) {
        $('#' + roomId).removeClass().addClass('grey').addClass('room-container');
        $.ajax({
        type: "GET",
        url: "getRoomAvailability.php",
        data: {
            roomId : roomId,
            cacheKey: new Date().getTime()
        },
        dataType: "json",
        success: function(data) {
            /* handle data here */
            if (roomId == '7681'){
                updateMultiPurposeEventInfo(true,data);
                return;
            }
            //console.log(data);

            $('#' + roomId).removeClass('grey').addClass("available");

            if (data["Status"] == "reserved") {
                $('#' + roomId).removeClass('available').addClass(data["Status"]);

                $('#' + roomId + ' .reserved-by').text(data["EventName"]);
            } else if (data["Status"] == "reserved_soon") {
                $('#' + roomId).removeClass('available').addClass(data["Status"]);

                $('#' + roomId + ' .reserved-by').text(data["EventName"]);
            }

        },
        error: function(xhr, status) {
            if (roomId == '7681'){
                updateMultiPurposeEventInfo(false);
                return;
            }
            /* handle error here */
            //console.log("ajax failed: " + status);

            $('#' + roomId).removeClass('grey').addClass("available");
            $('#' + roomId).find('.reserved-by').html("");
        }
    });
    
    }


}

function updateMultiPurposeEventInfo(event,data){
    console.log(data);
    var $mpr = jQuery("#atrium_multipurpose_room");
    var $mpDetails = jQuery("#mp-event");
    if (event && data){
        var $mpName = jQuery("#mp-event-name"), $mpTimes = jQuery("#mp-event-times");
        $mpr.addClass('event');
        $mpDetails.show();
        var inFormat = "HH:mm:ss";
        var outFormat = "h:mm A";
        var start = moment(data.TimeStart,inFormat);
        var end = moment(data.TimeEnd,inFormat);
        $mpName.html(data.GroupName);
        $mpTimes.html(start.format(outFormat) + " - " + end.format(outFormat));
    } else {
        $mpr.removeClass('event');
        $mpDetails.hide();
    }
}



var code = "4231";
var codeSoFar = "";

jQuery("h2").click(function(){
    var num = jQuery(this).data("refresh");
    codeSoFar += num;
    if (!codeSoFar == code.substring(0,codeSoFar.length)){
        codeSoFar = "";
    };
    if (codeSoFar == code){
        location.reload();
    }
});


