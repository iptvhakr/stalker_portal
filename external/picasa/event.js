var observerService;
var stbEvent=
{
    onEvent : function(data){},
    event : 0
}

//!!!
// Для проекта на базе WebKit можно исключить все НИЖЕ следующее
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
  try
  {
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
    observerService = Components.classes["@mozilla.org/observer-service;1"].getService(Components.interfaces.nsIObserverService);
    observerService.addObserver(myObserver, "TeletecSTB", false);
  }catch(e)
  {
  }

}