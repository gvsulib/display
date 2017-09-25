var xPosition = 0;
var yPosition = 0;
var lastClicked;

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
    /*getRoomAvailability();*/
    updateComputerAvailability();
    selectFloor(floor);
    updateTime();
    getMessages();
    displayEmoji();

    setInterval(getTraffic, trafficDelay * minutes); // default 10 minutes
    /*setInterval(getRoomAvailability, roomsDelay * minutes); // default 3*/
    setInterval(updateComputerAvailability, 2 * minutes);
    setInterval(updateTime, 10 * seconds);
    setInterval(getMessages, 20 * seconds);
    $(document).on('idle.idleTimer',function(){selectFloor(floor)});

    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    }, false);

    //hide modals if you click outside of them
    jQuery(document).click(function(event) { 
        if(!jQuery(event.target).closest('.feedback').length) {
            jQuery('.modal').hide();
        }
    });
    jQuery('.logo-container').click(function(){
        window.location.reload(true);
    })
});


// Send emojii feedback to PHP script
function sendFeedback(showContactInfo){
    
    console.log('and emotion id: ' + lastClicked);
    jQuery.ajax({
        url: 'feedback/send.php',
        type: 'POST',
        dataType: 'json',
        data: {
            
            emotionId : lastClicked
        },
        success: function(data){
                console.log(data);
            if (data['success'] == true){
                console.log('AJAX call returned success');
                success(showContactInfo);
            }
        },
        error: function(data) {
            console.log(data);
        }
    });
}

function hideModals(s){
    setTimeout(function(){
        jQuery('.modal').hide();
    }, s * 1000);
}

function success(showContactInfo){
    console.log('Running Success function');
    console.log(showContactInfo);
    jQuery('.modal').hide();
    jQuery('.close').show();
    if (showContactInfo){
        jQuery('.modal2').show();
        hideModals(16);
    } else {
        jQuery('.modal3').show();
        hideModals(5);  
    }
}

function emojiClicked(emoji, e){
    var level = emoji.data('level');
    
    lastClicked = level;
    if (level < 4){
        
        
       	jQuery('.modal1').show();
       	jQuery('.close').show();
       	hideModals(16);
       
    } else {
        jQuery('.feedback .modal').hide();
        sendFeedback(false);
    }
}

function displayEmoji(){
    emojione.imageType = 'svg';
    emojione.sprites = true;
    emojione.imagePathSVGSprites = 'img/emojione.sprites.svg'


    jQuery('.feedback ul.emojis li').each(function(){
        var emoji = jQuery(this);
        emoji.html(emojione.toImage(emoji.data('emoji')));
        emoji.click(function(e){
            emojiClicked(emoji,e);
        });
    });
    jQuery('.feedback .modal1 span').click(function(){
        	jQuery('.modal1').hide();
        	jQuery('.close').hide();
            sendFeedback(true);
            
    })

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

    console.log('Getting Traffic Data');
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
    
    //the multipurpose room is a bit tricky-if there's an event, we don't want to overwrite the color
    //until the event is over.
    $('#atrium_multipurpose_room').addClass(function(index, className) {
    	if (className != "event") {
    		
    		return getColor(data["Atrium: Multi-Purpose Room"])	
    	} else {
    		return "event";
    	}
    
    });
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

//code that actually updates the room reservation display.  
//gets it's input from the AJAX call below.


/*  
function updateRoomAvailability(data, status) {
	
  
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
    
    //check to see if the multipurpose room has an event in it as we loop through.  
    //If it doesn't, make sure the event class and identifying information is hidden.
	var multipurpose_event = false;
	
	
	//by default, all rooms show available.  We will change the colors as we loop through the bookings XML.
	for (var key in roomIds) {
		$('#' + roomIds[key]).removeClass('grey').removeClass('reserved').removeClass('reserved_soon').addClass("available");
        $('#' + roomIds[key]).find('.reserved-by').html("");
	
	}
	//now get the reservation data, and overwrite any rooms with reservation information.
	//the "if" clause should give priority to rooms that are reserved now, only showing 
	//rooms about to be occupied if they have no current reservations.
  	var bookings = data.getElementsByTagName("room");
  	//console.log(bookings);	
  	var code = "";
	var groupname = "";
	var status = "";	
  		
  	for (var i = 0; i < bookings.length; i++) { 
  			  
  		code = bookings[i].getElementsByTagName("roomcode")[0].innerHTML;
  		groupname = bookings[i].getElementsByTagName("groupname")[0].innerHTML;
  		status = bookings[i].getElementsByTagName("status")[0].innerHTML;		
  	
  		if (status == "reserved") {
            $('#' + code).removeClass('available').addClass(status);

            $('#' + code + ' .reserved-by').text(groupname);
        } else if (status == "reserved_soon") {
            $('#' + code).removeClass('available').addClass(status);

            $('#' + code + ' .reserved-by').text(groupname);
        }
        //if the multipurpose room is reserved, grab some additional data and pass it to a 
  		//function that will update the traffic display
        
        if (code == "7681") {
        	console.log("bookings object");
        	console.log(bookings[i]);
  			updateMultiPurposeEventInfo(true, bookings[i]);
  			multipurpose_event = 1;
  		}
        
  	}
  	
  	//if there's no event data for the multipurpose room, make sure the event data and color is removed.
  	if (!multipurpose_event) {
  		updateMultiPurposeEventInfo(false);
  	}
  	//update the "last updated" section of the display with the timestamp from the XML file
  	
  	var timestring = data.getElementsByTagName("timestamp")[0].innerHTML
  	$('#last-updated-rooms').text(timestring);
  	
  	
  	
  	//now show the room display.
  	$('.room-availability-floors').fadeIn();
    $('#room-traffic-legend').fadeIn();
  	
}

//if the request for room data fails, show greyed out rooms with no data
//This is called by the error section of the ajax request
function roomBookingsErrorDisplay() {
	$('.room-availability-floors').fadeIn();
    $('#room-traffic-legend').fadeIn();

}
 
//get the XML file with the room bookings data from the server.
//pass it to the display function if successful.  If not, display an 
//error message.
function getRoomAvailability() {
    

	console.log("attempting to get room data...");
  	$.ajax({
    type: "GET",
    cache: false,
    url: "RoomReservationData.xml",
    dataType: "xml", 
    success: function(data,status) {
    	console.log("room data retrieved: " + status);
    	updateRoomAvailability(data, status);	
    
    },
    error: function(object, status, erthrwn) {
    	console.log("error: " + status);
    	console.log("Error thrown: " + erthrwn);
    	roomBookingsErrorDisplay();
    	
    	}, 
    });
                
}		

//this function changes the multipurpose room purple if there's an event in there, or removes the purple
//if the event is over.                
function updateMultiPurposeEventInfo(event,data){
	
    console.log("Updating Multipurpose room display...");
    var $mpr = jQuery("#atrium_multipurpose_room");
    var $mpDetails = jQuery("#mp-event");
    
    if (event && data){
    	var xml = data;
    
    	var TimeStart = xml.getElementsByTagName("timestart")[0].innerHTML;
    	var TimeEnd = xml.getElementsByTagName("timeend")[0].innerHTML;
    	var GroupName = xml.getElementsByTagName("groupname")[0].innerHTML;
    	var EventName = xml.getElementsByTagName("eventname")[0].innerHTML;
    
    	//Get a name for the event.
    	var DisplayName = "";
    	if (GroupName) {
    		DisplayName = GroupName;
    	} else if (EventName) {
    		DisplayName = EventName;
    
    	}
    
    
    	
        var $mpName = jQuery("#mp-event-name"), $mpTimes = jQuery("#mp-event-times");
        var inFormat = "HH:mm:ss";
        var outFormat = "h:mm A";
        var start = moment(TimeStart,inFormat);
        var end = moment(TimeEnd,inFormat);
        console.log(DisplayName);
        $mpName.html(DisplayName);
        $mpTimes.html(start.format(outFormat) + " - " + end.format(outFormat));
        $mpr.addClass(function(index, className) {
        	if (className != "event") {
        		$mpr.removeClass(className);
        		return "event";
        	}
        
        });
        $mpDetails.show();
        console.log("Multipurpose Event data posted to display");
    } else {
        $mpr.removeClass('event');
        $mpDetails.hide();
        console.log("Event data removed from multipurpose room");
    }
}
*/ 


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


