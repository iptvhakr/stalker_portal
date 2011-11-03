/**
 * Demo video module.
 */

(function(){

    if (!module.infoportal_sub){
        module.infoportal_sub = [];
    }

    module.infoportal_sub.push({
        "title" : get_word('demo_video_title'),
        "cmd"   : function(){

            if (!stb.profile["demo_video_url"]){
                stb.notice.show(get_word('coming_soon'));
                return;
            }

            var item = {
                "name" : get_word("demo_video"),
                "cmd"  : "ffmpeg " + stb.profile["demo_video_url"]
            };
            
            main_menu.hide();
            stb.player.prev_layer = main_menu;
            stb.player.play(item);
        }
    })

})();

loader.next();