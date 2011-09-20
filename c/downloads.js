/*
    Document   : Downloads
    Created on : 12.07.2011, 11:26:16
    Author     : Affect
*/
(function(){
    /* Downloads */
    function downloads_constructor(){
        this.layer_name = 'downloads';
        this.row_blocks = ['number', 'd_name','d_progress','d_status'];
        this.superclass = ListLayer.prototype;
        this.dialog = new downloads_dialog_constructor();
        this.dialog.hide();
        this.hide = function(do_not_reset){
            //_debug('downloads.hide');
            this.exit();
            this.superclass.hide.call(this, do_not_reset);
        };
        this.show=function(){
            this.superclass.show.call(this);
            var self = this;
            this.interval = setInterval(function(){self.every_interval.call(self);}, 3500);
            this.every_interval(false);
        }
        this.every_interval = function(do_load_data){
            var obj = [];
            if(stbDownloadManager) {
                //stb.Debug(stbDownloadManager.GetQueueInfo());
                obj = eval(stbDownloadManager.GetQueueInfo());//jsonparce
            }
            this.forFill = null;
            this.forFill = [];
            for(var i=0;i<obj.length;i++) {
                this.forFill.push({
                    'number':(i+1).toString(),
                    'd_name': obj[i].filePath.split('/')[obj[i].filePath.split('/').length-1],
                    'd_progress':(obj[i].progressPct != '-1') ?
                        '<span class="pb"><span style="width:'+(obj[i].progressPct * 110 / 100)+'px;"></span></span><span class="txt">'+obj[i].progressPct.substr(0, 4)+'%</span>':
                        '<span class="pb"><span style="width:0px;"></span></span><span class="txt">0%</span>',
                    'd_status':obj[i].stateStr,
                    'state':obj[i].state,
                    'id':obj[i].id
                });
            }
            if(!do_load_data || do_load_data!=false) {
                this.load_data(true);
            }


        };
        this.load_data = function(true_arg){
            //this.reset();
            //_debug(' * * * * * * * *\nthis.load_data || this.forFill.length: '+this.forFill.length+'\n* * * * * * * *');

            this.total_pages = Math.ceil(this.forFill.length /14);
            this.set_total_items(this.forFill.length);

            var begin = (this.cur_page - 1) * 14;
            var end   = this.cur_page * 14;
            this.data_items = this.forFill.slice(begin, end);

            this.result = {
                selected_item : (true_arg) ? this.cur_row :0,
                cur_page : 0
            };
            this.fill_list(this.data_items);
        };
        this.reset = function(){
            this.cur_row  = 0;
            this.total_pages = 1;
            this.set_total_items(0);
        };
        this.bind = function(){
            this.superclass.bind.apply(this);
                (function(){
                    if(this.data_items[this.cur_row].state == 0 || this.data_items[this.cur_row].state == 5 || this.data_items[this.cur_row].state == 4 ){
                        stbDownloadManager.StartJob(this.data_items[this.cur_row].id)
                    }
                    else{
                        stbDownloadManager.StopJob(this.data_items[this.cur_row].id)
                    }
                    var self=this;
                    setTimeout(function(){self.every_interval.call(self);}, 250);
                }).bind(key.OK, this);
                (function(){
                    this.hide();
                    main_menu.show();
                }).bind(key.LEFT, this).bind(key.MENU, this).bind(key.EXIT, this);
        };
        this.exit = function(){
            clearInterval(this.interval);
            this.interval = null;
        };
        this.drawCreateDialog = function(){
            this.dialog.show({"parent":this});//,"url":'http://cs13112.vkontakte.ru/u72912054/video/a372ac588e.720.mp4',"name":'Part 9 "И чо".mp4'
        };
        this.layer_z_index=0;
        this.interval = null;
        this.forFill=[];
        this.reset();
        this.delete_switcher = function(){
            if (this.delete_menu && this.delete_menu.on){
                this.delete_menu.hide();
            }else{
                this.delete_menu.show();
            }
        };
        this.init_delete_menu = function(map, options){
            this.delete_menu = new bottom_menu(this, options);
            this.delete_menu.init(map);
            this.delete_menu.bind();
        };
    }
    downloads_constructor.prototype = new ListLayer();
    var downloads = new downloads_constructor();
    downloads.bind();
    downloads.init();
    downloads.init_left_ear(word['ears_back']);
    downloads.init_header_path(get_word('downloads_title'));
    downloads.init_color_buttons([
        {"label":get_word('downloads_create'),"cmd":(function(){
                this.drawCreateDialog();
        })},
        {"label":get_word('downloads_move_up'),"cmd":(function(){
                stbDownloadManager.AdjustJobPriority(this.data_items[this.cur_row].id, true);
                clearInterval(this.interval);
                this.interval=null;
                this.interval = setInterval(function(){self.every_interval.call(self);}, 3500);
        })},
        {"label":get_word('downloads_move_down'),"cmd":(function(){
                stbDownloadManager.AdjustJobPriority(this.data_items[this.cur_row].id, false);
                clearInterval(this.interval);
                this.interval=null;
                this.interval = setInterval(function(){self.every_interval.call(self);}, 3500);
        })},
        {"label" : get_word('downloads_delete'), "cmd" : downloads.delete_switcher}
    ]);
    downloads.init_delete_menu(
        [
            {"label" : get_word('downloads_record'), "cmd" : function(){
                    stbDownloadManager.DeleteJob(downloads.data_items[downloads.cur_row].id, false);
                    setTimeout(function(){downloads.every_interval.call(downloads);}, 250);
                }
            },
            {"label" : get_word('downloads_record_and_file'),  "cmd" : function(){
                    stbDownloadManager.DeleteJob(downloads.data_items[downloads.cur_row].id, true);
                    setTimeout(function(){downloads.every_interval.call(downloads);}, 250);
                }
            }
        ],
        {
            "offset_x" : 470,
            "color":'blue',
            "need_reset_load_data": false,
            "need_update_header": false
        }
    );
    downloads.hide();
    module.downloads = downloads;
    /* END DOWNLOADS */
    main_menu.add(get_word('downloads_title'), [], 'mm_ico_dm.png', function(){
            main_menu.hide();
            module.downloads.show();
        },
        module.downloads
    );
    loader.next();
})();
