var observerService
var stbEvent=
{
    onEvent : function(data){},
    event : 0
}
var myObserver = 
{
    observe : function(subject, topic, data)  
        {
        if (topic == "TeletecSTB")
            {
                stbEvent.event = data
                stbEvent.onEvent(data)
            }
        }
}
function initEvents(){
    observerService = Components.classes["@mozilla.org/observer-service;1"].getService(Components.interfaces.nsIObserverService);
    observerService.addObserver(myObserver, "TeletecSTB", false);

}