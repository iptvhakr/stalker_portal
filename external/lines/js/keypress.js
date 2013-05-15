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
    switch (code) {
        case keys.RIGHT:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.x + 1 <= gs.cells.x - 1) {
                gs.position.current.x++;
            } else {
                gs.position.current.x = gs.cells.x - 1;
            }
            cvDraw.item();
            break;
        case keys.LEFT:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.x - 1 >= 0) {
                gs.position.current.x--;
            } else {
                gs.position.current.x = 0;
            }
            cvDraw.item();
            break;
        case keys.DOWN:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.y + 1 <= gs.cells.y - 1) {
                gs.position.current.y++;
            } else {
                gs.position.current.y = gs.cells.y - 1;
            }
            cvDraw.item();
            break;
        case keys.UP:
            gs.position.old.y = gs.position.current.y;
            gs.position.old.x = gs.position.current.x;
            if (gs.position.current.y - 1 >= 0) {
                gs.position.current.y--;
            } else {
                gs.position.current.y = 0;
            }
            cvDraw.item();
            break;
        case keys.OK:
            if (keysBlock == true) {
                var new_loc = new String(window.location).substr(0, new String(window.location).indexOf('?'));
                window.location = new_loc + '?referrer=' + encodeURIComponent(pages.referrer);
                return;
            }
            cvDraw.PressOK();
            break;
        case keys.NUM1:
            cvDraw.jumpBallStop();
            break;
        case keys.RED:
            log(cvDraw.showArr());
            break;
        case keys.NUM9:
        case keys.REC:
            cvDraw.showBall();
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