

$.ajaxSetup({ cache: false }); 

$( document ).ready(function() {
    var seconds = 10000;
    var minutes = seconds * 60;

    //updateComputerAvailability();
    
    updateTime();
    getMessages();
    
      
    setTimeout(function(){
        updateTime();
        setTimeout(arguments.callee, 20 * seconds);
    }, 20 * seconds);
    
    setInterval(getMessages, 5 * minutes);
    

    document.addEventListener("contextmenu", function(e){
        e.preventDefault();
    }, false);

   
});

function getMessages(){
    jQuery.ajax({
        datatype: 'json',
        url: 'php/getMessages.php?cache=' + new Date().getTime(),
        method: 'GET',
        success: function(message){
            var container = jQuery('#messageContainer');
            message = JSON.parse(message);
            if (message != "none"){

                console.log("this is the message:" + message);
                
                container.fadeIn();
                
                container.find('#heading').text(message.heading);
                container.find('#msgtime').text('Alert: ' + moment(message.entrydate).format("h:mm A"));
                container.find('#msgbody').text(message.body);
            } else {
                container.fadeOut();
                
            }
        }
    });
}

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


