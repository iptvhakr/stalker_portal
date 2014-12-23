(function ($) {
    $.fn.toggleCollumn = function (collName, showState) {
        this.each(function(){
            if (typeof(this.consoleMsg) != 'function' ) {
                this.consoleMsg = function(msg){
                    console.log( (this.tagName || '') + '#' + (this.id || '') + '.' + (this.className? this.className.replace(' ', '.') : '') + ' - ' + (msg || ''));
                }
            }
            
            if (typeof (this) != "undefined" && typeof (this.tagName) == "string" && this.tagName == 'TABLE') {
                this.args = {'collName': collName, 'showState': showState};
                var _table = $(this);
                var _thead = _table.children('thead');
                if (_thead.length == 0) {
                    this.consoleMsg(' without head ');
                    return true;
                }
                if (typeof(this.getFilterSet) != 'function' ) {
                    this.getFilterSet = function(){
                        if (typeof (this.filterSet) == 'undefined') {
                            var filterSet = {};
                            _thead.find('th').each(function(i){
                                var filterName = $(this).data('filter') || false;
                                if (filterName) {
                                    filterSet[filterName] = i;
                                }
                            });
                            this.filterSet = filterSet;
                        }
                    }
                }
                
                if (typeof(this.checkCols) != 'function' ) {
                    this.checkCols = function(){
                        var _object = this;
                        if (typeof(this.filterSet) == 'undefined' || this.filterSet.length == 0) {
                            this.consoleMsg(' filter set is empty ');        
                        }
                                               
                        $(this).children('thead, tbody').children('tr').each(function(){
                            var _tr = $(this);
                            if (_object.args.collName != 'all') {
                                if (_object.args.showState){
                                    $(_tr.children('th, td').get(_object.filterSet[_object.args.collName])).show();
                                } else {
                                    $(_tr.children('th, td').get(_object.filterSet[_object.args.collName])).hide();
                                }
                            } else {
                                $.each(_object.filterSet, function(name, num){
                                    if (_object.args.showState){
                                        $(_tr.children('th, td').get(num)).show();
                                    } else {
                                        $(_tr.children('th, td').get(num)).hide();
                                    }
                                });
                            }
                        });
                    };                
                }
                this.getFilterSet();
                this.checkCols();
            } else {
                this.consoleMsg(' not table ');
            }
        });
        
    }
})(jQuery);