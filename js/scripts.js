var xPosition = 0;
var yPosition = 0;
var lastClicked;

$.ajaxSetup({ cache: false }); 

$( document ).ready(function() {
    var seconds = 10000;
    var minutes = seconds * 60;

    //updateComputerAvailability();
    
    updateTime();
    getMessages();
    getWeather(); //Get the initial weather.
      
    //setInterval(updateComputerAvailability, 10 * minutes);
    setInterval(updateTime, 30 * seconds);
    setInterval(getMessages, 5 * minutes);
    setInterval(getWeather, 30 * minutes); //Update the weather every 30 minutes.

    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    }, false);

   
});


function updateComputerAvailability(){
    console.log('refreshing iframe');
    jQuery('#cpumap').get(0).contentWindow.location = jQuery('#cpumap').attr('src');
}

function updateTime(){
    var now = moment();
    var container= jQuery('#date-time-container');
    $day = container.find("#day");
    $time = container.find("#time");
    $day.html(now.format("MMMM D"));
    $time.html(now.format("h:mm A"));
}




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
            var container = jQuery('#messageContainer');
            
            if (message != null && message != 'null'){
                message = JSON.parse(message);
                container.fadeIn();
                
                container.find('#heading').text(message.heading);
                container.find('#time').text('Alert: ' + moment(message.entrydate).format("h:mm A"));
                container.find('#body').text(message.body);
            } else {
                container.fadeOut();
                
            }
        }
    });
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


