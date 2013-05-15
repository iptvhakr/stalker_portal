window.onload = init;
window.onkeydown = key.press;
function init() {
    win = {"width":screen.width, "height":screen.height};
    gs.layers.context = document.getElementById('game').getContext('2d');
    if (!gs.layers.context || !gs.layers.context.drawImage) {
        return;
    }
    var loc = new String(window.location);
    if (loc.indexOf('?') >= 0) {
        var parts = loc.substr(loc.indexOf('?') + 1).split('&'), _GET = new Object();
        for (var key in parts) {
            _GET[parts[key].substr(0, parts[key].indexOf('='))] = parts[key].substr(parts[key].indexOf('=') + 1);
        }
        pages.referrer = decodeURIComponent(_GET['referrer']);
    } else {
        pages.referrer = document.referrer;
    }
    log('\n\npages.referrer: ' + pages.referrer + '\nwindow.location: ' + window.location + '\n\n');
    var graphicres_mode = "720";
    gs.actualSize = 576;
    switch (win.height) {
        case 480:
            gs.actualSize = 480;
            graphicres_mode = "720";
            break;
        case 576:
            gs.actualSize = 576;
            graphicres_mode = "720";
            break;
        case 720:
            gs.actualSize = 720;
            graphicres_mode = "1280";
            break;
        case 1080:
            gs.actualSize = 1080;
            graphicres_mode = "1920";
            break;
    }
    byID('game').width = gs.size[gs.actualSize].scr.w;
    byID('game').height = gs.size[gs.actualSize].scr.h;
    byID('cursorBox').style.width = gs.size[gs.actualSize].scr.w + 'px';
    byID('cursorBox').style.height = gs.size[gs.actualSize].scr.h + 'px';
    window.resizeTo(win.width, win.height);
    window.moveTo(0, 0);
    try {
        stb = gSTB;
        stb.ExecAction("graphicres " + graphicres_mode);
        stb.EnableServiceButton(true);
    } catch (e) {
    }
    try {
        modes.emulate = false;
        stb = gSTB;
    } catch (e) {
        modes.emulate = true;
        stb = egSTB;
    }
    var fileref = document.createElement("link");
    fileref.setAttribute("rel", "stylesheet");
    fileref.setAttribute("type", "text/css");
    fileref.setAttribute("href", 'css/screen_' + gs.actualSize + '.css');
    document.getElementsByTagName("head")[0].appendChild(fileref);
    log('CSS file imported: "css/screen_' + gs.actualSize + '.css"');
    gs.balls = new Array();
    for (var i = 0; i < gs.cells.y; i++) {
        gs.balls[i] = new Array();
        for (var j = 0; j < gs.cells.x; j++) {
            gs.balls[i][j] = null;
        }
    }
    cvDraw.generateNextBalls();
    cvDraw.showBall();
    cvDraw.item();
}
var cvDraw = {"sizeScr":"", "ball":null, "bgImg_1":"", "bgImg_2":"", "stopAddBalls":false, "isEndGame":false, "item":function () {
    byID('cursor').style.marginLeft = gs.position.current.x * gs.size[gs.actualSize].cll.w + 'px';
    byID('cursor').style.marginTop = gs.position.current.y * gs.size[gs.actualSize].cll.h + 'px';
}, "createBall":function (n_color, n_x, n_y) {
    var ball = new Image(), clolor = !n_color ? gs.colors[random(gs.colors.length - 1)] : n_color, self = this;
    ball.src = 'img/' + gs.actualSize + '/' + clolor + '.png';
    ball.addEventListener('load', function () {
        var coords = self.findeFreePlace(), x = !n_color || n_x == -1 ? coords[0] : n_x, y = !n_color || n_y == -1 ? coords[1] : n_y;
        if (coords == false) {
            cvDraw.showfinish();
            return;
        }
        var img = this;
        gs.balls[y][x] = {"x":x, "y":y, "color":clolor, "scale":0.1, 'img':img, "siObj":null, "_f":function (x, y) {
            if (this.scale < 1) {
                this.scale += 0.2;
                gs.layers.context.globalCompositeOperation = "source-over";
                gs.layers.context.drawImage(this.img, x * gs.size[gs.actualSize].cll.w + gs.size[gs.actualSize].cll.w / 2 - gs.size[gs.actualSize].cll.w * gs.balls[y][x].scale / 2, y * gs.size[gs.actualSize].cll.h + gs.size[gs.actualSize].cll.h / 2 - gs.size[gs.actualSize].cll.h * gs.balls[y][x].scale / 2, gs.size[gs.actualSize].cll.w * this.scale, gs.size[gs.actualSize].cll.h * this.scale);
            } else {
                clearInterval(this.siObj);
                this.siObj = null;
            }
        }};
        gs.balls[y][x].scale = 0.4;
        gs.layers.context.drawImage(this, x * gs.size[gs.actualSize].cll.w + gs.size[gs.actualSize].cll.w / 2 - gs.size[gs.actualSize].cll.w * gs.balls[y][x].scale / 2, y * gs.size[gs.actualSize].cll.h + gs.size[gs.actualSize].cll.h / 2 - gs.size[gs.actualSize].cll.h * gs.balls[y][x].scale / 2, gs.size[gs.actualSize].cll.w * gs.balls[y][x].scale, gs.size[gs.actualSize].cll.h * gs.balls[y][x].scale);
        gs.balls[y][x].siObj = setInterval(function () {
            gs.balls[y][x]._f(x, y);
        }, 25);
    }, false);
}, "showfinish":function () {
    keysBlock = true;
    if (gs.account < 50) {
        byID('errorFinish').style.display = 'block';
    } else {
        byID('errorFinish2').style.display = 'block';
    }
}, "showBall":function () {
    if (this.isEndGame == false) {
        for (var i = 0; i < gs.nextBalls.length; i++) {
            this.createBall(gs.nextBalls[i], -1, -1);
        }
        var counter = 0;
        for (var i = 0; i < gs.balls.length; i++) {
            for (var j = 0; j < gs.balls[i].length; j++) {
                if (gs.balls[i][j] != null) {
                    counter++;
                }
            }
        }
        if (counter + 3 >= gs.balls.length * gs.balls[0].length) {
            this.showfinish();
            return;
        }
        this.generateNextBalls();
        var self = this;
        setTimeout(function () {
            self.checkLines(false);
        }, 250)
    } else {
        this.stopAddBalls = false;
    }
}, "jumpBallTimer":null, "jumpBallScale":0.6, "jumpBallScaleD":0.1, "jumpBall":function (v) {
    var self = this;
    if (v == 9) {
        this.jumpBallTimer = setInterval(function () {
            self.jumpBallStartJumping();
        }, 60);
    }
}, "jumpBallStartJumping":function () {
    if (this.jumpBallScale >= 0.75) {
        this.jumpBallScaleD = -0.05;
    }
    if (this.jumpBallScale <= 0.55) {
        this.jumpBallScaleD = 0.05;
    }
    this.jumpBallScale += this.jumpBallScaleD;
    gs.layers.context.clearRect(gs.move.start.x * gs.size[gs.actualSize].cll.w, gs.move.start.y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
    gs.layers.context.drawImage(gs.balls[gs.move.start.y][gs.move.start.x].img, gs.move.start.x * gs.size[gs.actualSize].cll.w, gs.move.start.y * gs.size[gs.actualSize].cll.h + gs.size[gs.actualSize].cll.h / 3 - gs.size[gs.actualSize].cll.h * this.jumpBallScale / 2, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
}, "findeFreePlace":function () {
    try {
        var x = random(gs.cells.x - 1), y = random(gs.cells.y - 1);
        if (gs.balls[y][x] != null) {
            return this.findeFreePlace();
        } else {
            return[x, y];
        }
    } catch (e) {
        this.isEndGame = true;
        this.showfinish();
        return false;
    }
}, "jumpBallStop":function () {
    clearInterval(this.jumpBallTimer);
    gs.move.selected = false;
}, "ballMoveStart":function () {
    if (gs.balls[gs.position.current.y][gs.position.current.x] != null) {
        gs.move.readyToCheck = false;
        gs.move.selected = true;
        gs.move.start.x = gs.position.current.x;
        gs.move.start.y = gs.position.current.y;
        var self = this;
        this.jumpBallTimer = setInterval(function () {
            self.jumpBallStartJumping();
        }, 60);
    }
}, "PressOK":function () {
    if (gs.move.selected == true) {
        if (gs.move.start.x == gs.position.current.x && gs.move.start.y == gs.position.current.y) {
            cvDraw.jumpBallStop();
            gs.move.start.x = -1;
            gs.move.start.y = -1;
            log('disable select');
        } else {
            if (gs.balls[gs.position.current.y][gs.position.current.x] != null) {
                cvDraw.jumpBallStop();
                gs.move.readyToCheck = false;
                gs.move.selected = true;
                gs.move.start.x = gs.position.current.x;
                gs.move.start.y = gs.position.current.y;
                this.ballMoveStart();
                log('enable select');
            }
            gs.move.finish.x = gs.position.current.x;
            gs.move.finish.y = gs.position.current.y;
            this.checkWay();
        }
    } else {
        if (gs.move.start.x == -1 && gs.move.start.y == -1 && gs.balls[gs.position.current.y][gs.position.current.x] != null) {
            gs.move.readyToCheck = false;
            gs.move.selected = true;
            gs.move.start.x = gs.position.current.x;
            gs.move.start.y = gs.position.current.y;
            this.ballMoveStart();
            log('enable select');
        } else {
        }
    }
}, "arr":new Array(), "checkWay":function () {
    if (gs.balls[gs.move.finish.y][gs.move.finish.x] != null) {
        return;
    }
    this.currentNum = 0;
    this.arr = new Array();
    for (var i = 0; i < gs.cells.y; i++) {
        this.arr[i] = new Array();
        for (var j = 0; j < gs.cells.x; j++) {
            if (gs.balls[i][j] != null) {
                this.arr[i][j] = '#';
            } else {
                this.arr[i][j] = '_';
            }
        }
    }
    this.arr[gs.move.start.y][gs.move.start.x] = "S";
    this.arr[gs.move.finish.y][gs.move.finish.x] = "F";
    var way = this.findPath(gs.move.start.x, gs.move.start.y);
    if (way != false) {
        this.drawWay(way);
    } else {
        byID('errorBadPath').style.display = 'block';
        setTimeout(function () {
            byID('errorBadPath').style.display = 'none';
        }, 1500);
    }
}, "showArr":function () {
    var str = '\n';
    for (var i = 0; i < gs.cells.x; i++) {
        var line = '';
        for (var j = 0; j < gs.cells.x; j++) {
            line += this.arr[i][j] + ' ';
        }
        str += line + '\n';
    }
    return str;
}, "currentNum":0, "findPath":function (x, y) {
    if (x < 0 || x > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) {
        return false;
    }
    if (this.arr[y][x] == 'F') {
        return true;
    }
    if (this.arr[y][x] != '_' && this.arr[y][x] != "S") {
        return false;
    }
    this.arr[y][x] = this.currentNum++;
    for (var k = 0; k < this.arr.length * this.arr[0].length; k++) {
        for (var i = 0; i < this.arr.length; i++) {
            for (var j = 0; j < this.arr[i].length; j++) {
                if (this.arr[i][j] == k) {
                    this.setNums(j, i, k + 1);
                }
            }
        }
    }
    return this.getWay();
}, "setNums":function (x, y, num) {
    if (!(x < 0 || x > this.arr.length - 1 || y - 1 < 0 || y - 1 > this.arr[0].length - 1) && this.arr[y - 1][x] == '_') {
        this.arr[y - 1][x] = num;
    }
    if (!(x < 0 || x > this.arr.length - 1 || y + 1 < 0 || y + 1 > this.arr[0].length - 1) && this.arr[y + 1][x] == '_') {
        this.arr[y + 1][x] = num;
    }
    if (!(x - 1 < 0 || x - 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x - 1] == '_') {
        this.arr[y][x - 1] = num;
    }
    if (!(x + 1 < 0 || x + 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x + 1] == '_') {
        this.arr[y][x + 1] = num;
    }
}, "getMinFrom":function (x, y) {
    var tmp = new Array();
    var tmp_obj = new Array();
    if (!(x < 0 || x > this.arr.length - 1 || y - 1 < 0 || y - 1 > this.arr[0].length - 1) && this.arr[y - 1][x] != '_' && this.arr[y - 1][x] != '#') {
        tmp[tmp.length] = this.arr[y - 1][x];
        tmp_obj[tmp_obj.length] = {"x":x, "y":y - 1};
    }
    if (!(x < 0 || x > this.arr.length - 1 || y + 1 < 0 || y + 1 > this.arr[0].length - 1) && this.arr[y + 1][x] != '_' && this.arr[y + 1][x] != '#') {
        tmp[tmp.length] = this.arr[y + 1][x];
        tmp_obj[tmp_obj.length] = {"x":x, "y":y + 1};
    }
    if (!(x - 1 < 0 || x - 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x - 1] != '_' && this.arr[y][x - 1] != '#') {
        tmp[tmp.length] = this.arr[y][x - 1];
        tmp_obj[tmp_obj.length] = {"x":x - 1, "y":y};
    }
    if (!(x + 1 < 0 || x + 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x + 1] != '_' && this.arr[y][x + 1] != '#') {
        tmp[tmp.length] = this.arr[y][x + 1];
        tmp_obj[tmp_obj.length] = {"x":x + 1, "y":y};
    }
    var minValue = 999;
    var minIndex = 0;
    for (var i = 0; i < tmp.length; i++) {
        if (minValue > tmp[i]) {
            minValue = tmp[i];
            minIndex = i;
        }
    }
    return!empty(tmp_obj) ? {"obj":tmp_obj[minIndex], "num":tmp[minIndex]} : false;
}, "getMinAtThe":function (x, y, num) {
    var tmp = new Array();
    var tmp_obj = new Array();
    if (!(x < 0 || x > this.arr.length - 1 || y - 1 < 0 || y - 1 > this.arr[0].length - 1) && this.arr[y - 1][x] == num) {
        tmp_obj[tmp_obj.length] = {"x":x, "y":y - 1};
    }
    if (!(x < 0 || x > this.arr.length - 1 || y + 1 < 0 || y + 1 > this.arr[0].length - 1) && this.arr[y + 1][x] == num) {
        tmp[tmp.length] = this.arr[y + 1][x];
        tmp_obj[tmp_obj.length] = {"x":x, "y":y + 1};
    }
    if (!(x - 1 < 0 || x - 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x - 1] == num) {
        tmp[tmp.length] = this.arr[y][x - 1];
        tmp_obj[tmp_obj.length] = {"x":x - 1, "y":y};
    }
    if (!(x + 1 < 0 || x + 1 > this.arr.length - 1 || y < 0 || y > this.arr[0].length - 1) && this.arr[y][x + 1] == num) {
        tmp[tmp.length] = this.arr[y][x + 1];
        tmp_obj[tmp_obj.length] = {"x":x + 1, "y":y};
    }
    return tmp_obj[0];
}, "getWay":function () {
    var start = this.getMinFrom(gs.move.finish.x, gs.move.finish.y);
    if (start == false) {
        return false;
    }
    var curNum = start.num;
    var coords = [start.obj];
    for (var i = 0; i < this.arr.length * this.arr[0].length; i++) {
        try {
            coords.unshift(this.getMinAtThe(coords[0].x, coords[0].y, --curNum));
        } catch (e) {
        }
        try {
            if (coords[0].x == gs.move.start.x && coords[0].y == gs.move.start.y) {
                break;
            }
        } catch (e) {
        }
    }
    coords.shift();
    return coords;
}, "drawWay":function (way) {
    if (way.length == 1 && gs.move.start.x == way[0].x && gs.move.start.y == way[0].y) {
        way = new Array();
    }
    for (var i = 0; i < way.length; i++) {
        gs.layers.context.drawImage(gs.balls[gs.move.start.y][gs.move.start.x].img, way[i].x * gs.size[gs.actualSize].cll.w + gs.size[gs.actualSize].cll.w / 2 - gs.size[gs.actualSize].cll.w * 0.4 / 2, way[i].y * gs.size[gs.actualSize].cll.h + gs.size[gs.actualSize].cll.h / 2 - gs.size[gs.actualSize].cll.h * 0.4 / 2, gs.size[gs.actualSize].cll.w * 0.4, gs.size[gs.actualSize].cll.h * 0.4);
    }
    this.createBall(gs.balls[gs.move.start.y][gs.move.start.x].color, gs.move.finish.x, gs.move.finish.y);
    var self = this;
    setTimeout(function () {
        gs.layers.context.clearRect(gs.move.start.x * gs.size[gs.actualSize].cll.w, gs.move.start.y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        gs.balls[gs.move.start.y][gs.move.start.x] = null;
        for (var i = 0; i < way.length; i++) {
            gs.layers.context.clearRect(way[i].x * gs.size[gs.actualSize].cll.w, way[i].y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
        gs.move.start.x = -1;
        gs.move.start.y = -1;
        gs.move.finish.x = -1;
        gs.move.finish.y = -1;
        self.checkLines(true);
        self.jumpBallStop();
    }, 500);
}, "checkLines":function (setballs) {
    var isLineClear = false;
    var lines = new Array();
    for (var y = 0; y < gs.balls.length; y++) {
        lines[y] = new Array();
        for (var x = 0; x < gs.balls[0].length; x++) {
            if (lines[y].length == 0 && gs.balls[y][x] != null) {
                lines[y][lines[y].length] = {"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y, "del":false};
            } else {
                if (gs.balls[y][x] != null && lines[y][lines[y].length - 1].color == gs.balls[y][x].color) {
                    lines[y][lines[y].length] = {"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y};
                } else {
                    if (lines[y].length < 5) {
                        lines[y] = gs.balls[y][x] != null ? new Array({"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y}) : new Array();
                    }
                }
            }
        }
        if (lines[y].length >= 5) {
            log('\n\n\n\n');
            var new_line = new Array();
            var start_x = lines[y][0].x;
            for (var oo = 0; oo < lines[y].length; oo++) {
                log('lines[' + y + '][' + oo + '].x: ' + (lines[y][oo].x).toString() + '-|-start_x: ' + start_x);
                if (lines[y][oo].x == start_x || lines[y][oo].x - 1 == start_x) {
                    new_line.push(lines[y][oo]);
                    log(new_line[new_line.length - 1].y + '---x: ' + new_line[new_line.length - 1].x);
                } else {
                    log('break,new_line.length: ' + new_line.length);
                    if (new_line.length < 5) {
                        new_line = new Array();
                        log('new Array()');
                    } else {
                        break;
                    }
                }
                start_x = lines[y][oo].x;
            }
            lines[y] = new_line;
            log('\n\n\n\n');
        }
    }
    for (var i = 0; i < lines.length; i++) {
        if (lines[i].length < 5) {
            lines[i] = new Array();
        } else {
            for (var j = 0; j < lines[i].length; j++) {
                if (gs.balls[lines[i][j].y][lines[i][j].x] == null) {
                    lines[i] = new Array();
                }
            }
            if (lines[i].length < 5) {
                break;
            }
            for (var j = 0; j < lines[i].length; j++) {
                log('horizontal lines||y: ' + lines[i][j].y + '---x: ' + lines[i][j].x);
                gs.balls[lines[i][j].y][lines[i][j].x] = null;
                gs.layers.context.clearRect(lines[i][j].x * gs.size[gs.actualSize].cll.w, lines[i][j].y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
            }
            gs.account += lines[i].length * gs.coeff;
            isLineClear = true;
        }
    }
    lines = new Array();
    for (var x = 0; x < gs.balls[0].length; x++) {
        lines[x] = new Array();
        for (var y = 0; y < gs.balls.length; y++) {
            if (lines[x].length == 0 && gs.balls[y][x] != null) {
                lines[x][lines[x].length] = {"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y};
            } else {
                if (gs.balls[y][x] != null && lines[x][lines[x].length - 1].color == gs.balls[y][x].color) {
                    lines[x][lines[x].length] = {"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y};
                } else {
                    if (lines[x].length < 5) {
                        lines[x] = gs.balls[y][x] != null ? new Array({"color":gs.balls[y][x].color, "x":gs.balls[y][x].x, "y":gs.balls[y][x].y}) : new Array();
                    }
                }
            }
        }
        if (lines[x].length >= 5) {
            log('\n\n\n\n');
            var new_line = new Array();
            var start_y = lines[x][0].y | 0;
            for (var oo = 0; oo < lines[x].length; oo++) {
                log('lines[' + x + '][' + oo + '].y: ' + (lines[x][oo].y).toString() + '-|-start_y ' + start_y);
                if (lines[x][oo].y == start_y || lines[x][oo].y - 1 == start_y) {
                    new_line.push(lines[x][oo]);
                    log(new_line[new_line.length - 1].y + '---x: ' + new_line[new_line.length - 1].x);
                } else {
                    log('break,new_line.length: ' + new_line.length);
                    if (new_line.length < 5) {
                        new_line = new Array();
                        log('new Array()');
                    } else {
                        break;
                    }
                }
                start_y = lines[x][oo].y;
            }
            lines[x] = new_line;
            log('\n\n\n\n');
        }
    }
    for (var i = 0; i < lines.length; i++) {
        if (lines[i].length < 5) {
            lines[i] = new Array();
        } else {
            for (var j = 0; j < lines[i].length; j++) {
                if (gs.balls[lines[i][j].y][lines[i][j].x] == null) {
                    lines[i] = new Array();
                }
            }
            if (lines[i].length < 5) {
                break;
            }
            for (var j = 0; j < lines[i].length; j++) {
                log('vertical lines||y: ' + lines[i][j].y + '---x: ' + lines[i][j].x);
                gs.balls[lines[i][j].y][lines[i][j].x] = null;
                gs.layers.context.clearRect(lines[i][j].x * gs.size[gs.actualSize].cll.w, lines[i][j].y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
            }
            gs.account += lines[i].length * gs.coeff;
            isLineClear = true;
        }
    }
    var counter = 0;
    lines = new Array();
    for (var x = 0; x < gs.balls[0].length; x++) {
        var y = 0;
        lines[counter] = new Array();
        for (var d = 0; d < gs.balls.length; d++) {
            if (x + d < gs.balls[0].length && y + d < gs.balls.length) {
                if (lines[counter].length == 0 && gs.balls[y + d][x + d] != null) {
                    lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y};
                } else {
                    if (gs.balls[y + d][x + d] != null && lines[counter][lines[counter].length - 1].color == gs.balls[y + d][x + d].color) {
                        lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y};
                    } else {
                        if (lines[counter].length < 5) {
                            if (gs.balls[y + d][x + d] == null) {
                                lines[counter] = new Array();
                            } else {
                                lines[counter] = new Array({"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y});
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        counter++;
    }
    for (var y = 1; y < gs.balls.length; y++) {
        var x = 0;
        lines[counter] = new Array();
        for (var d = 0; d < gs.balls.length; d++) {
            if (x + d < gs.balls[0].length && y + d < gs.balls.length) {
                if (lines[counter].length == 0 && gs.balls[y + d][x + d] != null) {
                    lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y};
                } else {
                    if (gs.balls[y + d][x + d] != null && lines[counter][lines[counter].length - 1].color == gs.balls[y + d][x + d].color) {
                        lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y};
                    } else {
                        if (lines[counter].length < 5) {
                            if (gs.balls[y + d][x + d] == null) {
                                lines[counter] = new Array();
                            } else {
                                lines[counter] = new Array({"color":gs.balls[y + d][x + d].color, "x":gs.balls[y + d][x + d].x, "y":gs.balls[y + d][x + d].y});
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        if (lines[counter].length < 5) {
            lines[counter] = new Array();
        }
        counter++;
    }
    for (var i = 0; i < lines.length; i++) {
        if (lines[i].length < 5) {
            continue;
        }
        for (var j = 0; j < lines[i].length; j++) {
            gs.balls[lines[i][j].y][lines[i][j].x] = null;
            gs.layers.context.clearRect(lines[i][j].x * gs.size[gs.actualSize].cll.w, lines[i][j].y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
        gs.account += lines[i].length * gs.coeff;
        isLineClear = true;
    }
    var counter = 0;
    lines = new Array();
    for (var x = gs.balls[0].length - 1; x >= 0; x--) {
        var y = 0;
        lines[counter] = new Array();
        for (var d = 0; d < gs.balls.length; d++) {
            if (x - d >= 0 && y + d < gs.balls.length) {
                if (lines[counter].length == 0 && gs.balls[y + d][x - d] != null) {
                    lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y};
                } else {
                    if (gs.balls[y + d][x - d] != null && lines[counter][lines[counter].length - 1].color == gs.balls[y + d][x - d].color) {
                        lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y};
                    } else {
                        if (lines[counter].length < 5) {
                            if (gs.balls[y + d][x - d] == null) {
                                lines[counter] = new Array();
                            } else {
                                lines[counter] = new Array({"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y});
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        if (lines[counter].length < 5) {
            lines[counter] = new Array();
        }
        counter++;
    }
    for (var y = 1; y < gs.balls.length; y++) {
        var x = gs.balls[0].length - 1;
        lines[counter] = new Array();
        for (var d = 0; d < gs.balls.length; d++) {
            if (x - d < gs.balls[0].length && y + d < gs.balls.length) {
                if (lines[counter].length == 0 && gs.balls[y + d][x - d] != null) {
                    lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y};
                } else {
                    if (gs.balls[y + d][x - d] != null && lines[counter][lines[counter].length - 1].color == gs.balls[y + d][x - d].color) {
                        lines[counter][lines[counter].length] = {"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y};
                    } else {
                        if (lines[counter].length < 5) {
                            if (gs.balls[y + d][x - d] == null) {
                                lines[counter] = new Array();
                            } else {
                                lines[counter] = new Array({"color":gs.balls[y + d][x - d].color, "x":gs.balls[y + d][x - d].x, "y":gs.balls[y + d][x - d].y});
                            }
                        } else {
                            continue;
                        }
                    }
                }
            }
        }
        if (lines[counter].length < 5) {
            lines[counter] = new Array();
        }
        counter++;
    }
    for (var i = 0; i < lines.length; i++) {
        if (lines[i].length < 5) {
            continue;
        }
        for (var j = 0; j < lines[i].length; j++) {
            gs.balls[lines[i][j].y][lines[i][j].x] = null;
            gs.layers.context.clearRect(lines[i][j].x * gs.size[gs.actualSize].cll.w, lines[i][j].y * gs.size[gs.actualSize].cll.h, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
        gs.account += lines[i].length * gs.coeff;
        isLineClear = true;
    }
    var lis = byID('account').getElementsByTagName('li');
    lis[0].innerHTML = '0';
    lis[1].innerHTML = '0';
    lis[2].innerHTML = '0';
    lis[3].innerHTML = '0';
    switch (gs.account.toString().length) {
        case 4:
            lis[0].innerHTML = gs.account.toString().substr(0, 1);
            lis[1].innerHTML = gs.account.toString().substr(1, 1);
            lis[2].innerHTML = gs.account.toString().substr(2, 1);
            lis[3].innerHTML = gs.account.toString().substr(3, 1);
            break;
        case 3:
            lis[1].innerHTML = gs.account.toString().substr(0, 1);
            lis[2].innerHTML = gs.account.toString().substr(1, 1);
            lis[3].innerHTML = gs.account.toString().substr(2, 1);
            break;
        case 2:
            lis[2].innerHTML = gs.account.toString().substr(0, 1);
            lis[3].innerHTML = gs.account.toString().substr(1, 1);
            break;
        case 1:
            lis[3].innerHTML = gs.account.toString().substr(0, 1);
            break;
    }
    if (isLineClear == true) {
        this.stopAddBalls = true;
    } else {
        if (setballs == true) {
            this.showBall();
        }
    }
}, "generateNextBalls":function () {
    gs.nextBalls = new Array(gs.colors[random(gs.colors.length - 1)], gs.colors[random(gs.colors.length - 1)], gs.colors[random(gs.colors.length - 1)]);
    var li = byID('nextBalls').getElementsByTagName('li');
    for (var i = 0; i < gs.nextBalls.length; i++) {
        li[i].style.backgroundImage = 'url(img/' + gs.actualSize + '/' + gs.nextBalls[i] + '.png)';
    }
}}