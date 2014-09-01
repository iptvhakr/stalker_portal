/**
 * Redirection to pomogator module.
 */
(function(){

    main_menu.add('POMOGATOR', [], 'mm_ico_pomogator.png', function(){

        stb.setFrontPanel('.');

        var url = 'http://pomogator.od.ua/_stb/?referrer='+encodeURIComponent(window.location);

        _debug('url', url);
        window.location = url;
    }, {layer_name : "pomogator"});

    loader.next();
})();