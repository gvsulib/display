$.ajaxSetup({ cache: false }); 

$( document ).ready(function() {
    var seconds = 10000;
    var minutes = seconds * 60;

    //updateComputerAvailability();
    
    
    getMessages();
    
    //setInterval(updateComputerAvailability, 10 * minutes);
    
    setInterval(getMessages, 5 * minutes);
   
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



