window.onload = init;
window.onkeydown = key.press;
function init() {
    VKBlock = true;
    win = {"width":screen.width, "height":screen.height};
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
    var graphicres_mode = "720";
    window.resizeTo(win.width, win.height);
    window.moveTo(0, 0);
    try {
        modes.emulate = false;
        stb = gSTB;
        stb.ExecAction("graphicres " + graphicres_mode);
        stb.EnableServiceButton(true);
        cvDraw.vars.model = trim.all(stb.RDir("Model"));
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
}
var cvDraw = {"vars":{"model":"MAG250", "mode":"setNums", "tips":true, "modeCandidats":true, "started":false, "gameTime":0, "timer":null, "openOne":false, "counterSteps":0, "counterGoodSteps":0, "complexity":"easy"}, "start":function () {
    var self = this;
    gs.items = new Array();
    for (var y = 0; y < gs.infoItems.y; y++) {
        gs.items[y] = new Array();
        for (var x = 0; x < gs.infoItems.x; x++) {
            gs.items[y][x] = {"delCand":new Array(), "val":-1, "show":false, "changeble":false};
        }
    }
    var f_elems = [1, 2, 3, 4, 5, 6, 7, 8, 9].shuffle(), b1 = [f_elems[0], f_elems[1], f_elems[2]], b2 = [f_elems[3], f_elems[4], f_elems[5]], b3 = [f_elems[6], f_elems[7], f_elems[8]];
    gs.items[0][0].val = b1[0];
    gs.items[0][1].val = b1[1];
    gs.items[0][2].val = b1[2];
    gs.items[0][3].val = b2[0];
    gs.items[0][4].val = b2[1];
    gs.items[0][5].val = b2[2];
    gs.items[0][6].val = b3[0];
    gs.items[0][7].val = b3[1];
    gs.items[0][8].val = b3[2];
    gs.items[1][0].val = b3[0];
    gs.items[1][1].val = b3[1];
    gs.items[1][2].val = b3[2];
    gs.items[1][3].val = b1[0];
    gs.items[1][4].val = b1[1];
    gs.items[1][5].val = b1[2];
    gs.items[1][6].val = b2[0];
    gs.items[1][7].val = b2[1];
    gs.items[1][8].val = b2[2];
    gs.items[2][0].val = b2[0];
    gs.items[2][1].val = b2[1];
    gs.items[2][2].val = b2[2];
    gs.items[2][3].val = b3[0];
    gs.items[2][4].val = b3[1];
    gs.items[2][5].val = b3[2];
    gs.items[2][6].val = b1[0];
    gs.items[2][7].val = b1[1];
    gs.items[2][8].val = b1[2];
    gs.items[3][0].val = b3[1];
    gs.items[3][1].val = b3[2];
    gs.items[3][2].val = b3[0];
    gs.items[3][3].val = b1[1];
    gs.items[3][4].val = b1[2];
    gs.items[3][5].val = b1[0];
    gs.items[3][6].val = b2[1];
    gs.items[3][7].val = b2[2];
    gs.items[3][8].val = b2[0];
    gs.items[4][0].val = b2[1];
    gs.items[4][1].val = b2[2];
    gs.items[4][2].val = b2[0];
    gs.items[4][3].val = b3[1];
    gs.items[4][4].val = b3[2];
    gs.items[4][5].val = b3[0];
    gs.items[4][6].val = b1[1];
    gs.items[4][7].val = b1[2];
    gs.items[4][8].val = b1[0];
    gs.items[5][0].val = b1[1];
    gs.items[5][1].val = b1[2];
    gs.items[5][2].val = b1[0];
    gs.items[5][3].val = b2[1];
    gs.items[5][4].val = b2[2];
    gs.items[5][5].val = b2[0];
    gs.items[5][6].val = b3[1];
    gs.items[5][7].val = b3[2];
    gs.items[5][8].val = b3[0];
    gs.items[6][0].val = b1[2];
    gs.items[6][1].val = b1[0];
    gs.items[6][2].val = b1[1];
    gs.items[6][3].val = b2[2];
    gs.items[6][4].val = b2[0];
    gs.items[6][5].val = b2[1];
    gs.items[6][6].val = b3[2];
    gs.items[6][7].val = b3[0];
    gs.items[6][8].val = b3[1];
    gs.items[7][0].val = b3[2];
    gs.items[7][1].val = b3[0];
    gs.items[7][2].val = b3[1];
    gs.items[7][3].val = b1[2];
    gs.items[7][4].val = b1[0];
    gs.items[7][5].val = b1[1];
    gs.items[7][6].val = b2[2];
    gs.items[7][7].val = b2[0];
    gs.items[7][8].val = b2[1];
    gs.items[8][0].val = b2[2];
    gs.items[8][1].val = b2[0];
    gs.items[8][2].val = b2[1];
    gs.items[8][3].val = b3[2];
    gs.items[8][4].val = b3[0];
    gs.items[8][5].val = b3[1];
    gs.items[8][6].val = b1[2];
    gs.items[8][7].val = b1[0];
    gs.items[8][8].val = b1[1];
    var original_order = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8]
    ], tmp_collumns = [[0, 1, 2].shuffle(), [0, 1, 2].shuffle(), [0, 1, 2].shuffle()], tmp_collumns_order = [0, 1, 2].shuffle(), columns = [original_order[tmp_collumns_order[0]][tmp_collumns[0][0]], original_order[tmp_collumns_order[0]][tmp_collumns[0][1]], original_order[tmp_collumns_order[0]][tmp_collumns[0][2]], original_order[tmp_collumns_order[1]][tmp_collumns[1][0]], original_order[tmp_collumns_order[1]][tmp_collumns[1][1]], original_order[tmp_collumns_order[1]][tmp_collumns[1][2]], original_order[tmp_collumns_order[2]][tmp_collumns[2][0]], original_order[tmp_collumns_order[2]][tmp_collumns[2][1]], original_order[tmp_collumns_order[2]][tmp_collumns[2][2]], ], tmp_lines = [[1, 2, 0].shuffle(), [1, 2, 0].shuffle(), [1, 2, 0].shuffle()], tmp_lines_order = [1, 2, 0].shuffle(), lines = [original_order[tmp_lines_order[0]][tmp_lines[0][0]], original_order[tmp_lines_order[0]][tmp_lines[0][1]], original_order[tmp_lines_order[0]][tmp_lines[0][2]], original_order[tmp_lines_order[1]][tmp_lines[1][0]], original_order[tmp_lines_order[1]][tmp_lines[1][1]], original_order[tmp_lines_order[1]][tmp_lines[1][2]], original_order[tmp_lines_order[2]][tmp_lines[2][0]], original_order[tmp_lines_order[2]][tmp_lines[2][1]], original_order[tmp_lines_order[2]][tmp_lines[2][2]], ];
    log('\n\noriginal_order:\n[' + original_order.toString() + ']\ncolumns_order:\n[' + columns.toString() + ']\nlines_order:\n[' + lines.toString() + ']\n\n');
    var t_arr = new Array();
    for (var y = 0; y < gs.infoItems.y; y++) {
        t_arr[y] = new Array();
        for (var x = 0; x < gs.infoItems.x; x++) {
            t_arr[y][x] = gs.items[lines[y]][columns[x]];
        }
    }
    gs.items = t_arr;
    delete t_arr;
    var v_v = random(0, gs.complexity[this.vars.complexity].length - 1);
    for (var i = 0; i < gs.complexity[this.vars.complexity][v_v].length; i++) {
        var open = [0, 1, 2, 3, 4, 5, 6, 7, 8].shuffle().slice(0, gs.complexity[this.vars.complexity][v_v][i]);
        for (var j = 0; j < open.length; j++) {
            gs.items[gs.squares[i][open[j]][0]][gs.squares[i][open[j]][1]].show = true;
        }
    }
    for (var y = 0; y < gs.infoItems.y; y++) {
        for (var x = 0; x < gs.infoItems.x; x++) {
            if (gs.items[y][x].show == false) {
                gs.items[y][x].val = -1;
                gs.items[y][x].changeble = true;
                gs.items[y][x].flag = false;
            }
        }
    }
    setTimeout(function () {
        self.item();
        log('\n\n\n\n\n\n*******************START\n\n\n\n\n\n');
        self.vars.started = true;
        VKBlock = false;
        if (self.vars.timer != null) {
            clearInterval(self.vars.timer);
        }
        self.vars.timer = null;
        self.Candidats();
        self.vars.timer = setInterval(function () {
            self.vars.gameTime++;
            var mins = Math.floor(self.vars.gameTime / 60), secs = (self.vars.gameTime - mins * 60);
            byID('counter_time').getElementsByClassName('cover')[0].innerHTML = (mins < 10 ? '0' + mins : mins) + ' ' + (secs < 10 ? '0' + secs : secs);
            if (self.vars.gameTime >= 60 * 60 * 2) {
                byID('finish').style.display = 'block';
                if (self.vars.timer != null) {
                    clearInterval(self.vars.timer);
                }
                self.vars.timer = null;
                VKBlock = true;
                self.vars.started = false;
            }
        }, 999);
        self.fillVariants();
    }, 1);
}, "item":function () {
    byID('cursor').style.display = 'block';
    byID('cursor').style.marginLeft = gs.position.current.x * gs.size[gs.actualSize].cll.w + 'px';
    byID('cursor').style.marginTop = gs.position.current.y * gs.size[gs.actualSize].cll.h + 'px';
}, "PressOK":function () {
    if (this.vars.started == false && byID('finish').style.display == 'block') {
        window.location.reload(true);
        return;
    }
    if (this.vars.started == false) {
        byID('finish').style.display = 'none';
        byID('begin').style.display = 'none';
        this.start();
    }
}, "fillVariants":function () {
    log("fillVariants");
    var items = byID('game').getElementsByClassName('box'), y = 0, x = 0, j;
    for (var i = 0; i < items.length; i++) {
        y = Math.floor(i / gs.infoItems.x);
        x = i % gs.infoItems.x;
        j = (i < 10) ? '0' + i : '' + i;
        if (gs.items[y][x].val != -1) {
            if (gs.items[y][x].changeble == false) {
                byID('p_' + j).innerHTML = "<span>" + gs.items[y][x].val + "</span>";
            } else {
                byID('p_' + j).innerHTML = "<strong>" + gs.items[y][x].val + "</strong>";
            }
        } else {
            if (this.vars.tips == true) {
                var arr = this.getArrToCell(x, y, byID('p_' + j).parentNode.id), _out = '', str = new Array();
                if (gs.items[y][x].delCand.length > 0) {
                    log("x: " + x + ",y: " + y + ",delCand: " + gs.items[y][x].delCand.toString() + "\n" + arr.toString() + "\n" + '*******');
                }
                for (var m = 0; m < arr.length; m++) {
                    if (gs.items[y][x].delCand.length > 0) {
                        var tmp = false;
                        for (var l = 0; l < gs.items[y][x].delCand.length; l++) {
                            if (gs.items[y][x].delCand[l] == arr[m]) {
                                tmp = true;
                                break;
                            }
                        }
                        if (tmp != true) {
                            str.push(arr[m]);
                        }
                    } else {
                        str.push(arr[m]);
                    }
                }
                var _s = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                for (var o = 0; o < 9; o++) {
                    var tmp = false;
                    for (var h = 0; h < str.length; h++) {
                        if (_s[o] == str[h]) {
                            tmp = true;
                        }
                    }
                    if (tmp == true) {
                        _out += "" + _s[o] + " ";
                    } else {
                        _out += "<s>" + _s[o] + "</s> ";
                    }
                }
                byID('p_' + j).innerHTML = _out;
            } else {
                byID('p_' + j).innerHTML = '';
            }
        }
    }
}, "fillOne":function (x, y, set_num, _x, _y) {
    var id = gs.infoItems.x * _y + _x, flag;
    id = (id < 10) ? '0' + id : '' + id;
    var arr = this.getArrToCell(_x, _y, byID('p_' + id).parentNode.id);
    if (_x == x && _y == y) {
        for (var t = 0; t < arr.length; t++) {
            if (arr[t] == set_num) {
                gs.items[_y][_x].flag = true;
            }
        }
    } else {
        if (gs.items[_y][_x].val != -1) {
            if (gs.items[_y][_x].changeble == true) {
                byID('p_' + id).innerHTML = "<strong>" + gs.items[_y][_x].val + "</strong>";
            } else {
                byID('p_' + id).innerHTML = "<span>" + gs.items[_y][_x].val + "</span>";
            }
        } else {
            if (this.vars.tips == true) {
                var _out = "", _s = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                for (var o = 0; o < _s.length; o++) {
                    var tmp = false;
                    for (var h = 0; h < arr.length; h++) {
                        if (_s[o] == arr[h]) {
                            tmp = true;
                        }
                    }
                    if (tmp == true) {
                        if (gs.items[y][x].flag == true && set_num == _s[o]) {
                            _out += "<s>" + _s[o] + "</s> ";
                            continue;
                        }
                        _out += "" + _s[o] + " ";
                    } else {
                        _out += "<s>" + _s[o] + "</s> ";
                    }
                }
                byID('p_' + id).innerHTML = _out;
            } else {
                byID('p_' + id).innerHTML = '';
            }
        }
    }
}, "fillCellCand":function (x, y) {
    log('fillCellCand');
    var id = gs.infoItems.x * y + x;
    id = (id < 10) ? '0' + id : '' + id;
    var arr = this.getArrToCell(x, y, byID('p_' + id).parentNode.id);
    if (gs.items[y][x].val == -1) {
        if (this.vars.tips == true) {
            var _out = "", _s = [1, 2, 3, 4, 5, 6, 7, 8, 9];
            for (var o = 0; o < _s.length; o++) {
                var tmp = false;
                for (var h = 0; h < arr.length; h++) {
                    if (_s[o] == arr[h]) {
                        tmp = true;
                        break;
                    }
                }
                log(gs.items[y][x].delCand.toString());
                for (var l = 0; l < gs.items[y][x].delCand.length; l++) {
                    if (gs.items[y][x].delCand[l] == _s[o]) {
                        tmp = false;
                        break;
                    }
                }
                if (tmp == true) {
                    _out += "" + _s[o] + " ";
                } else {
                    _out += "<s>" + _s[o] + "</s> ";
                }
            }
            byID('p_' + id).innerHTML = _out;
        } else {
            byID('p_' + id).innerHTML = '';
        }
    }
}, "fillVariantsXY":function (x, y, set_num) {
    gs.items[y][x].val = -1;
    var _x, _y, id;
    log("fillVariantsXY");
    this.fillOne(x, y, set_num, x, y);
    for (_x = 0; _x < gs.infoItems.x; _x++) {
        this.fillOne(x, y, set_num, _x, y);
    }
    for (_y = 0; _y < gs.infoItems.y; _y++) {
        this.fillOne(x, y, set_num, x, _y);
    }
    if (this.vars.tips == true && gs.items[y][x].flag == true) {
        id = gs.infoItems.x * y + x;
        id = (id < 10) ? '0' + id : '' + id;
        var v_block = byID(byID('p_' + id).parentNode.id).getElementsByClassName('box');
        for (var u = 0; u < v_block.length; u++) {
            var d = parseInt(v_block[u].id.substr(2, 2), 10);
            _x = d % gs.infoItems.x;
            _y = Math.floor(d / gs.infoItems.x);
            this.fillOne(x, y, set_num, _x, _y);
        }
    }
    if (set_num != -1) {
        id = gs.infoItems.x * y + x;
        id = (id < 10) ? '0' + id : '' + id;
        if (gs.items[y][x].flag == true) {
            gs.items[y][x].val = set_num;
            byID('p_' + id).innerHTML = "<strong>" + gs.items[y][x].val + "</strong>";
        } else {
            byID('errorCell').style.margin = y * gs.size[gs.actualSize].cll.h + 'px 0 0 ' + x * gs.size[gs.actualSize].cll.w + 'px';
            byID('errorCell').style.display = 'block';
            setTimeout(function () {
                byID('errorCell').style.display = 'none';
            }, 1250);
        }
    } else {
        gs.items[y][x].val = set_num;
        var id = gs.infoItems.x * y + x;
        id = (id < 10) ? '0' + id : '' + id;
        if (this.vars.tips == true) {
            var arr = this.getArrToCell(x, y, byID('p_' + id).parentNode.id);
            var _out = "", _s = [1, 2, 3, 4, 5, 6, 7, 8, 9];
            for (var o = 0; o < _s.length; o++) {
                var tmp = false;
                for (var h = 0; h < arr.length; h++) {
                    if (_s[o] == arr[h]) {
                        tmp = true;
                    }
                }
                if (tmp == true) {
                    if (gs.items[y][x].flag == true && set_num == _s[o]) {
                        _out += "<s>" + _s[o] + "</s> ";
                        continue;
                    }
                    _out += "" + _s[o] + " ";
                } else {
                    _out += "<s>" + _s[o] + "</s> ";
                }
            }
            byID('p_' + id).innerHTML = _out;
        } else {
            byID('p_' + id).innerHTML = '';
        }
    }
}, "getArrToCell":function (x, y, pId) {
    var arr = [1, 2, 3, 4, 5, 6, 7, 8, 9], n_arr = new Array(), v_block, v_break = false, s = -1;
    for (var i = 0; i < arr.length; i++) {
        v_break = false;
        for (var _x = 0; _x < gs.infoItems.x; _x++) {
            if (gs.items[y][_x].val == arr[i]) {
                v_break = true;
                break;
            }
        }
        for (var _y = 0; _y < gs.infoItems.x; _y++) {
            if (gs.items[_y][x].val == arr[i]) {
                v_break = true;
                break;
            }
        }
        v_block = byID(pId).getElementsByClassName('box');
        for (var j = 0; j < v_block.length; j++) {
            s = parseInt(v_block[j].id.substr(2, 2));
            if (gs.items[Math.floor(s / gs.infoItems.x)][s % gs.infoItems.x].val == arr[i]) {
                v_break = true;
                break;
            }
        }
        if (v_break == false) {
            n_arr.push(arr[i]);
        }
    }
    return n_arr;
}, "PressNUM":function (code) {
    var num = -1;
    switch (code) {
        case keys.NUM1:
            num = 1;
            break;
        case keys.NUM2:
            num = 2;
            break;
        case keys.NUM3:
            num = 3;
            break;
        case keys.NUM4:
            num = 4;
            break;
        case keys.NUM5:
            num = 5;
            break;
        case keys.NUM6:
            num = 6;
            break;
        case keys.NUM7:
            num = 7;
            break;
        case keys.NUM8:
            num = 8;
            break;
        case keys.NUM9:
            num = 9;
            break;
    }
    if (this.vars.modeCandidats == true) {
        var exist = false;
        for (var i in gs.items[gs.position.current.y][gs.position.current.x].delCand) {
            if (gs.items[gs.position.current.y][gs.position.current.x].delCand[i] == num) {
                gs.items[gs.position.current.y][gs.position.current.x].delCand.splice(i, 1);
                exist = true;
            }
        }
        if (exist == false) {
            gs.items[gs.position.current.y][gs.position.current.x].delCand.push(num);
        }
        this.fillCellCand(gs.position.current.x, gs.position.current.y);
    } else {
        if (gs.items[gs.position.current.y][gs.position.current.x].changeble == true) {
            var id = gs.infoItems.x * gs.position.current.y + gs.position.current.x;
            id = (id < 10) ? '0' + id : '' + id;
            var arr = this.getArrToCell(gs.position.current.x, gs.position.current.y, byID('p_' + id).parentNode.id), isset = false;
            for (var u = 0; u < arr.length; u++) {
                if (arr[u] == num) {
                    isset = true;
                }
            }
            if (isset == true) {
                this.fillVariantsXY(gs.position.current.x, gs.position.current.y, num);
            } else {
                byID('errorCell').style.margin = gs.position.current.y * gs.size[gs.actualSize].cll.h + 'px 0 0 ' + gs.position.current.x * gs.size[gs.actualSize].cll.w + 'px';
                byID('errorCell').style.display = 'block';
                setTimeout(function () {
                    byID('errorCell').style.display = 'none';
                }, 1250);
            }
        }
        var counter = 0, iteraciy = 0;
        for (var y = 0; y < gs.infoItems.y; y++) {
            for (var x = 0; x < gs.infoItems.x; x++) {
                iteraciy++;
                if (gs.items[y][x].val != -1) {
                    counter++;
                }
            }
        }
        log('\nNow ' + counter + ' cells filled.\niteraciy: ' + iteraciy + '\n');
        if (iteraciy == counter) {
            byID('finish').style.display = 'block';
            if (this.vars.timer != null) {
                clearInterval(this.vars.timer);
            }
            this.vars.timer = null;
            VKBlock = true;
            this.vars.started = false;
        }
    }
}, "Erase":function () {
    if (gs.items[gs.position.current.y][gs.position.current.x].changeble == true) {
        gs.items[gs.position.current.y][gs.position.current.x].val = -1;
        this.fillVariantsXY(gs.position.current.x, gs.position.current.y, -1);
    }
}, "Tips":function () {
    if (this.vars.tips == true) {
        this.vars.tips = false;
        this.fillVariants();
        byID('showTips').style.backgroundImage = 'url(img/' + gs.actualSize + '/btn_red50.png)';
        byID('showCandidats').style.display = 'none';
        log('tips: OFF');
    } else {
        this.vars.tips = true;
        this.fillVariants();
        byID('showTips').style.backgroundImage = 'url(img/' + gs.actualSize + '/btn_red.png)';
        byID('showCandidats').style.display = 'block';
        byID('showCandidats').style.backgroundImage = 'url(img/' + gs.actualSize + '/btn_blue.png)';
        log('tips: ON');
    }
}, "Candidats":function () {
    if (this.vars.tips == true) {
        if (this.vars.modeCandidats == true) {
            this.vars.modeCandidats = false;
            byID('showCandidats').style.backgroundImage = 'url(img/' + gs.actualSize + '/btn_blue50.png)';
            log('candidats: OFF');
            byID('cursor').className = '';
        } else {
            this.vars.modeCandidats = true;
            byID('showCandidats').style.backgroundImage = 'url(img/' + gs.actualSize + '/btn_blue.png)';
            log('candidats: ON');
            byID('cursor').className = 'del';
        }
    }
}, "startCursor":function (direction) {
    switch (this.vars.complexity) {
        case "easy":
            byID('c_easy').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/easy0.png)';
            if (direction == 1) {
                this.vars.complexity = "hard";
                byID('c_hard').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/hard1.png)';
            } else {
                this.vars.complexity = "normal";
                byID('c_normal').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/normal1.png)';
            }
            break;
        case "normal":
            byID('c_normal').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/normal0.png)';
            if (direction == 1) {
                this.vars.complexity = "easy";
                byID('c_easy').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/easy1.png)';
            } else {
                this.vars.complexity = "hard";
                byID('c_hard').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/hard1.png)';
            }
            break;
        case "hard":
            byID('c_hard').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/hard0.png)';
            if (direction == 1) {
                this.vars.complexity = "normal";
                byID('c_normal').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/normal1.png)';
            } else {
                this.vars.complexity = "easy";
                byID('c_easy').style.backgroundImage = 'url(img/' + gs.actualSize + '/start/easy1.png)';
            }
            break;
    }
}};