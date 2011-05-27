/*    Epg recorder    */

(function(){

    module.epg_recorder = {

        add_del : function(){
            _debug('epg_recorder.add_del');
            
            var program = this.get_item();

            _debug('program', program);

            var ch_id = this.get_ch_id();

            _debug('ch_id', ch_id);

            var ch_idx = stb.player.channels.getIdxById(ch_id);

            var channel = stb.player.channels[ch_idx];

            _debug('channel', channel);

            if (!channel.mc_cmd){
                return;
            }

            if (program.mark_rec == 1){
                if (program.rec_id){
                    this.del();
                }
            }else{
                this.add();
            }
        },

        add : function(){
            _debug('epg_recorder.add');

            var program_id = this.get_item().id;

            stb.load(
                {
                    "type"        : "remote_pvr",
                    "action"      : "start_rec_deferred",
                    "program_id"  : program_id

                },

                function(result){
                    _debug('result', result);

                    if (result){
                        this.show_mark(program_id);
                        this.get_item().rec_id = result;
                    }else{
                        stb.notice.show(word['recorder_server_error']);
                    }
                },
                this
            )
        },

        del : function(){
            _debug('epg_recorder.del');

            var rec_id = this.get_item().rec_id;

            _debug('rec_id', rec_id);

            stb.load(
                {
                    "type"    : "remote_pvr",
                    "action"  : "del_rec",
                    "rec_id"  : rec_id

                },
                function(result){
                    _debug('result', result);
                },
                this
            );

            this.hide_mark(this.get_item().id);
        },

        get_item : function(){
            _debug('epg_recorder.get_item');
            
            return this.parent.data_items[this.parent.cur_row].epg[this.parent.cur_cell_col];
        },

        get_ch_id : function(){
            _debug('epg_recorder.get_ch_id');

            return this.parent.data_items[this.parent.cur_row].ch_id;
        },

        show_mark : function(program_id){
            _debug('epg_recorder.show_mark', program_id);

            var mark_idx = this.parent.marks_map.getIdxByVal('program_id', program_id);

            if (mark_idx !== null){
                this.parent.marks_map[mark_idx].mark_rec.show();

                this.get_item().mark_rec = 1;
                this.parent.set_active_row(this.parent.cur_row);
            }
        },

        hide_mark : function(program_id){
            _debug('epg_recorder.hide_mark', program_id);

            var mark_idx = this.parent.marks_map.getIdxByVal('program_id', program_id);

            if (mark_idx !== null){
                this.parent.marks_map[mark_idx].mark_rec.hide();

                this.get_item().mark_rec = 0;
                this.parent.set_active_row(this.parent.cur_row);
            }
        }
    };


})();

loader.next();