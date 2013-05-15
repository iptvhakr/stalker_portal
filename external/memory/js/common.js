window.onload = init;
window.onkeydown = key.press;
function init() {
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
    VKBlock = true;
    win = {"width":screen.width, "height":screen.height};
    gs.layers.game = document.getElementById('game').getContext('2d');
    if (!gs.layers.game || !gs.layers.game.drawImage) {
        return;
    }
    var loc = new String(window.location);
    if (loc.indexOf('?') >= 0) {
        var parts = loc.substr(loc.indexOf('?') + 1).split('&'), _GET = new Object();
        for (var key = 0; key < parts.length; key++) {
            var part = parts[key];
            _GET[part.substr(0, part.indexOf('='))] = part.substr(part.indexOf('=') + 1);
        }
        pages.referrer = decodeURIComponent(_GET['referrer']);
    } else {
        pages.referrer = document.referrer;
    }
    switch (win.height) {
        case 480:
            cvDraw.mode = gs.actualSize = 480;
            graphicres_mode = "720";
            break;
        case 576:
            cvDraw.mode = gs.actualSize = 576;
            graphicres_mode = "720";
            break;
        case 720:
            cvDraw.mode = gs.actualSize = 720;
            graphicres_mode = "1280";
            break;
        case 1080:
            cvDraw.mode = gs.actualSize = 1080;
            graphicres_mode = "1920";
            break;
    }
    byID('game').width = gs.size[gs.actualSize].scr.w;
    byID('game').height = gs.size[gs.actualSize].scr.h;
    window.resizeTo(win.width, win.height);
    window.moveTo(0, 0);
    cvDraw.vars.model = stb.RDir("Model");
    log('\'' + cvDraw.vars.model + '\'');
    var fileref = document.createElement("link");
    fileref.setAttribute("rel", "stylesheet");
    fileref.setAttribute("type", "text/css");
    fileref.setAttribute("href", 'css/screen_' + win.height + '.css');
    document.getElementsByTagName("head")[0].appendChild(fileref);
    log('CSS file imported: "css/screen_' + win.height + '.css"');
    gs.items = new Array();
    for (var y = 0; y < gs.iItems.y; y++) {
        gs.items[y] = new Array();
        for (var x = 0; x < gs.iItems.x; x++) {
            gs.items[y][x] = null;
        }
    }
    for (var i = 0, j = 0; i < gs.imgs.length; i++, j = j + 2) {
        gs.imgs[i].img.src = 'img/' + cvDraw.mode + '/' + gs.imgs[i].url;
        gs.arr[j] = gs.imgs[i].num;
        gs.arr[j + 1] = gs.imgs[i].num;
    }
    gs.arr = gs.arr.shuffle();
    cvDraw.bg = new Image();
    cvDraw.bg.src = 'img/' + cvDraw.mode + '/back.png';
    cvDraw.bg.onload = function () {
        cvDraw.dBg();
    }
}
var cvDraw = {"vars":{"model":"MAG200", "started":false, "selected":false, "selectedObj":{"x":0, "y":0, "num":0}, "gameTime":0, "timer":null, "openOne":false, "counterSteps":0, "counterGoodSteps":0}, "mode":null, "bg":new Object(), "dBg":function () {
    for (var y = 0; y < gs.iItems.y; y++) {
        for (var x = 0; x < gs.iItems.x; x++) {
            gs.layers.game.clearRect(gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
            gs.layers.game.drawImage(this.bg, gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
    }
}, "start":function () {
    var cnr = 0;
    for (var y = 0; y < gs.iItems.y; y++) {
        for (var x = 0; x < gs.iItems.x; x++) {
            var self = this;
            gs.items[y][x] = {"x":x, "y":y, "status":"free", "selected":false, "img":gs.arr[cnr++], "timer":{"obj":null, "interval":50, "_scale":1, "cond":"stop", "func":function (x, y, back) {
                var img;
                if (!back) {
                    if (this.cond == "from") {
                        img = gs.imgs[gs.items[y][x].img].img;
                    }
                    if (this.cond == "to") {
                        img = self.bg;
                    }
                } else {
                    if (this.cond == "from") {
                        img = self.bg;
                    }
                    if (this.cond == "to") {
                        img = gs.imgs[gs.items[y][x].img].img;
                    }
                }
                if (this.cond == "from") {
                    this._scale = this._scale - 0.2;
                    if (this._scale <= 0.2) {
                        this.cond = "to";
                    }
                }
                if (this.cond == "to") {
                    if (this._scale < 0.2) {
                        this._scale = 0.2;
                    } else {
                        this._scale = this._scale + 0.2;
                        if (this._scale >= 0.8) {
                            this._scale = 1;
                            clearInterval(this.obj);
                            this.obj = null;
                            this.cond = "stop";
                        }
                    }
                }
                gs.layers.game.clearRect(gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
                gs.layers.game.drawImage(img, gs.size[gs.actualSize].cll.w * x + (1 - this._scale) * gs.size[gs.actualSize].cll.w / 2, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w * this._scale, gs.size[gs.actualSize].cll.h);
            }}};
            gs.layers.game.clearRect(gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
            gs.layers.game.drawImage(gs.imgs[gs.arr[cnr - 1]].img, gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
    }
    var self = this;
    setTimeout(function () {
        self.dBg();
        self.item();
        log('\n\n\n\n\n\n*******************START\n\n\n\n\n\n');
        self.vars.started = true;
        VKBlock = false;
        if (self.vars.timer != null) {
            clearInterval(self.vars.timer);
        }
        self.vars.timer = null;
        self.vars.timer = setInterval(function () {
            self.vars.gameTime++;
            var mins = Math.floor(self.vars.gameTime / 60), secs = (self.vars.gameTime - mins * 60);
            byID('counter_time').getElementsByClassName('cover')[0].innerHTML = (mins < 10 ? '0' + mins : mins) + ' ' + (secs < 10 ? '0' + secs : secs);
            if (self.vars.gameTime > 60 * 60) {
                byID('errorFinish').style.display = 'block';
                if (self.vars.timer != null) {
                    clearInterval(self.vars.timer);
                }
                self.vars.timer = null;
                VKBlock = true;
                self.vars.started = false;
            }
        }, 999);
    }, 5000);
}, "item":function () {
    byID('cursor').style.display = 'block';
    byID('cursor').style.marginLeft = gs.position.current.x * gs.size[gs.actualSize].cll.w + 'px';
    byID('cursor').style.marginTop = gs.position.current.y * gs.size[gs.actualSize].cll.h + 'px';
}, "rotateCell":function (x, y, back) {
    if (!(cvDraw.vars.model == "MAG200" && win.width == 1920)) {
        if (gs.items[y][x].timer.cond == 'stop') {
            gs.items[y][x].timer.cond = "from";
            if (!back) {
                gs.items[y][x].timer.obj = setInterval(function () {
                    gs.items[y][x].timer.func(x, y, true);
                }, gs.items[y][x].timer.interval);
            } else {
                gs.items[y][x].timer.obj = setInterval(function () {
                    gs.items[y][x].timer.func(x, y);
                }, gs.items[y][x].timer.interval);
            }
        }
    } else {
        log('MAG200&&1920');
        gs.layers.game.clearRect(gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        if (!back) {
            gs.layers.game.drawImage(gs.imgs[gs.items[y][x].img].img, gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        } else {
            gs.layers.game.drawImage(this.bg, gs.size[gs.actualSize].cll.w * x, gs.size[gs.actualSize].cll.h * y, gs.size[gs.actualSize].cll.w, gs.size[gs.actualSize].cll.h);
        }
    }
}, "PressOK":function () {
    if (this.vars.started == false && byID('errorFinish').style.display == 'block') {
        window.location.reload(true);
        return;
    }
    if (this.vars.started == false) {
        byID('errorFinish').style.display = 'none';
        byID('Begin').style.display = 'none';
        this.start();
    } else {
        if (gs.items[gs.position.current.y][gs.position.current.x].status == "ready") {
            return;
        }
        this.vars.counter++;
        this.vars.counterSteps++;
        byID('counter_steps').getElementsByClassName('cover')[0].innerHTML = this.vars.counterSteps < 100 ? (this.vars.counterSteps < 10) ? '00' + this.vars.counterSteps : '0' + this.vars.counterSteps : this.vars.counterSteps;
        if (this.vars.selected == false) {
            this.vars.selectedObj.x = gs.position.current.x;
            this.vars.selectedObj.y = gs.position.current.y;
            this.vars.selectedObj.num = gs.items[gs.position.current.y][gs.position.current.x].img;
            this.rotateCell(gs.position.current.x, gs.position.current.y);
            this.vars.selected = true;
        } else {
            if (this.vars.selectedObj.x == gs.position.current.x && this.vars.selectedObj.y == gs.position.current.y) {
                return;
            }
            this.rotateCell(gs.position.current.x, gs.position.current.y);
            if (this.vars.selectedObj.num == gs.items[gs.position.current.y][gs.position.current.x].img) {
                gs.items[gs.position.current.y][gs.position.current.x].status = "ready";
                gs.items[this.vars.selectedObj.y][this.vars.selectedObj.x].status = "ready";
                this.vars.counterGoodSteps++;
            } else {
                var self = this, coords = {"x1":gs.position.current.x, "y1":gs.position.current.y, "x2":this.vars.selectedObj.x, "y2":this.vars.selectedObj.y};
                this.rotateCell(coords.x1, coords.y1);
                setTimeout(function () {
                    log('rotate back');
                    self.rotateCell(coords.x1, coords.y1, true);
                    self.rotateCell(coords.x2, coords.y2, true);
                }, 1000);
            }
            this.vars.selected = false;
            if (this.vars.counterGoodSteps >= gs.iItems.x * gs.iItems.y / 2) {
                byID('errorFinish').style.display = 'block';
                if (this.vars.timer != null) {
                    clearInterval(this.vars.timer);
                }
                this.vars.timer = null;
                VKBlock = true;
                this.vars.started = false;
            }
        }
    }
}};