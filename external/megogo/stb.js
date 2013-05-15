var stb = new Object();

stb.rdir = "MAG250";
stb.volume = 1;
stb.mute = 1;
stb.serviceButton = false;
stb.VKButton = false;
stb.env = {"language":'ru'};

stb.RDir = function(par){

	par1 = par.replace('getenv ', '');
	if(par1 != par)	{
	    return stb.env[par1];
	}
	return stb.rdir;
}

function getEnvironmentValue(name){
    var value;
    value = stb.RDir('getenv ' + name);
    return value;
}

stb.GetVolume = function(){
	return stb.volume;
}
stb.GetMute = function(){
	return stb.mute;
}

stb.EnableServiceButton = function(state){

	stb.serviceButton = state;
};

stb.EnableVKButton = function(state){

	stb.EnableVKButton = state;
};


stb.InitPlayer = function(){
};

stb.SetTopWin = function(winNum){
};

stb.SetPIG = function(state, scale, x, y){
};

stb.Debug = function(debugString){
};

stb.LoadUserData = function(fname){


	var pass = utf8_to_b64( "111333111" );
	return '';
};

stb.EnableServiceButton = function(state){

	stb.serviceButton = state;
};

stb.ShowVirtualKeyboard = function(state){

	stb.serviceButton = state;
};

stb.SaveUserData= function(state){


};