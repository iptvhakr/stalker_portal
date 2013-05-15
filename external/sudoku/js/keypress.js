var key = {"press":function (e) {
    var code = e.keyCode || e.which;
    if (stb && stb.key_lock === true && code != key.FRAME) {
        return;
    }
    if (e.shiftKey) {
        code += 1000;
    }
    if (e.altKey) {
        code += 2000;
    }
    if (VKBlock == true && code != keys.OK && code != keys.EXIT && code != keys.UP && code != keys.DOWN) {
        return;
    }
    byID('errorCell').style.display = 'none';
    if (VKBlock == true) {
        switch (code) {
            case keys.DOWN:
                cvDraw.startCursor(-1);
                break;
            case keys.UP:
                cvDraw.startCursor(1);
                break;
            case keys.OK:
                cvDraw.PressOK();
                break;
            case keys.EXIT:
                if (pages.referrer.length > 4) {
                    window.location = pages.referrer;
                } else {
                    window.location = pages.back;
                }
                break;
        }
        return;
    }
    switch (code) {
        case keys.RIGHT:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.x + 1 <= gs.infoItems.x - 1) {
                gs.position.current.x++;
            } else {
                gs.position.current.x = 0;
            }
            cvDraw.item();
            break;
        case keys.LEFT:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.x - 1 >= 0) {
                gs.position.current.x--;
            } else {
                gs.position.current.x = gs.infoItems.x - 1;
            }
            cvDraw.item();
            break;
        case keys.DOWN:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.y + 1 <= gs.infoItems.y - 1) {
                gs.position.current.y++;
            } else {
                gs.position.current.y = 0;
            }
            cvDraw.item();
            break;
        case keys.UP:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.y - 1 >= 0) {
                gs.position.current.y--;
            } else {
                gs.position.current.y = gs.infoItems.y - 1;
            }
            cvDraw.item();
            break;
        case keys.NUM1:
        case keys.NUM2:
        case keys.NUM3:
        case keys.NUM4:
        case keys.NUM5:
        case keys.NUM6:
        case keys.NUM7:
        case keys.NUM8:
        case keys.NUM9:
            cvDraw.PressNUM(code);
            break;
        case keys.BACK:
            cvDraw.Erase();
            break;
        case keys.RED:
            cvDraw.Tips();
            break;
        case keys.BLUE:
            cvDraw.Candidats();
            break;
        case keys.OK:
            cvDraw.PressOK();
            break;
        case keys.REFRESH:
            var new_loc = new String(window.location).substr(0, new String(window.location).indexOf('?'));
            window.location = new_loc + '?referrer=' + encodeURIComponent(pages.referrer);
            break;
        case keys.EXIT:
            if (pages.referrer.length > 4) {
                window.location = pages.referrer;
            } else {
                window.location = pages.back;
            }
            break;
    }
}}
