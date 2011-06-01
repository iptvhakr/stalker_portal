var key = {
    "press":function(e) {
        var code = e.keyCode || e.which;
        if (stb && stb.key_lock === true && code != key.FRAME) {return;}
        if (e.shiftKey) {code += 1000;}
        if (e.altKey) {code += 2000;}
        if(VKBlock==true && code!=keys.OK && code!=keys.EXIT) {
            return;
        }
        switch(code) {
            case keys.RIGHT:
                gs.position.old.y = gs.position.current.y;
                gs.position.old.x = gs.position.current.x;
                if(gs.position.current.x + 1 <= gs.iItems.x - 1) {
                    gs.position.current.x++;
                } else {
                    gs.position.current.x = gs.iItems.x - 1;
                }
                cvDraw.item();
            break;
            case keys.LEFT:
                gs.position.old.y = gs.position.current.y;
                gs.position.old.x = gs.position.current.x;
                if(gs.position.current.x - 1 >= 0) {
                    gs.position.current.x--;
                } else {
                    gs.position.current.x = 0;
                }
                cvDraw.item();
            break;
            case keys.DOWN:
                gs.position.old.y = gs.position.current.y;
                gs.position.old.x = gs.position.current.x;
                if(gs.position.current.y + 1 <= gs.iItems.y - 1) {
                    gs.position.current.y++;
                } else {
                    gs.position.current.y = gs.iItems.y - 1;
                }
                cvDraw.item();
            break;
            case keys.UP:
                gs.position.old.y = gs.position.current.y;
                gs.position.old.x = gs.position.current.x;
                if(gs.position.current.y - 1 >=0) {
                    gs.position.current.y--;
                } else {
                    gs.position.current.y = 0;
                }
                cvDraw.item();
            break;
            case keys.OK:
                cvDraw.PressOK();
            break;
            case keys.REFRESH:
                window.location.reload(true);
            break;
            case keys.EXIT:if(document.referrer.length > 4) {window.location = document.referrer;} else {window.location = pages.back;}break;
        }
    }
}