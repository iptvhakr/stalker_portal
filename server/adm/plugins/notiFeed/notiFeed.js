(function ($) {
    $.extend({
        notiFeed: {
            defaults: {
                title: "Notifications",
                linkText: "Read more",
                emptyListText: "No new notifications",
                notiFeedID: 'notiFeed',
                container: undefined,
                counterObj: undefined,
                containerClass: "dropdown-menu",
                titleClass: "noti-feed-title",
                descClass: "noti-feed-desc",
                ajax_link: '',
                set_readed_link: '',
                set_remind_link: '',
                checkTimeOut: 10*60*1000, // 10 min * 60 sec * 1000 milli sec
                timer: undefined,
                openFeedItemCallback: function(){}
            },
            feedList:{},
            setSettings: function(settings){
                this.defaults = $.extend(this.defaults, settings);
            },
            init: function(settings){
                this.setSettings(settings || {});
                return this;
            },
            create:function(){
                this.container = this.createContainer();
                this.setAutoCheck();
                return this.container;
            },
            createContainer: function(){
                var container = $("<ul></ul>");
                container.addClass(this.defaults.containerClass).attr('id', this.defaults.notiFeedID);
                return container;
            },
            createFeedListTitle: function(){
                this.setCounter();
                var item = $("<li></li>");
                var itemBox = $("<div class='row'></div>").appendTo(item);
                itemBox = $("<div class='col-xs-12'></div>").appendTo(itemBox);
                itemBox = $("<label>" + (Object.keys(this.feedList).length ? this.defaults.title: this.defaults.emptyListText) + "</label>").appendTo(itemBox);
                itemBox.addClass(this.defaults.titleClass);
                this.container.children(':first').remove();
                this.container.prepend(item);
            },
            fillFeedList: function(){
                this.container.empty();
                this.createFeedListTitle();
                var feedItem;
                for(var i in this.feedList){
                    if (!this.feedList[i].itemObj) {
                        feedItem = this.feedItemCreate(i);
                        this.feedList[i].itemObj = feedItem;
                        this.container.append(feedItem);
                    }
                }
            },
            updateFeedList: function(feedList){
                if (!feedList && this.defaults.ajax_link) {
                    $.ajax({
                        async: false,
                        dataType: "json",
                        method: 'POST',
                        url: this.defaults.ajax_link,
                        success: function (data) {
                            feedList = data.data;
                        }
                    });
                }
                var feedList = feedList || {};
                for(var i in feedList) {
                    this.feedList[feedList[i].guid] = feedList[i];
                }
                this.fillFeedList();
            },
            setReadedItem: function(feedItemId, delItem){
                if (feedItemId && this.feedList[feedItemId]) {
                    var self = this;
                    var delItem = typeof(delItem) == 'undefined' ? true: delItem;
                    $.ajax({
                        async: false,
                        dataType: "json",
                        method: 'POST',
                        url: this.defaults.set_readed_link,
                        data: {feed_item_id: feedItemId},
                        success: function (data) {
                            if (delItem) {
                                self.feedList[feedItemId].itemObj.slideUp('fast', function(){$(this).remove()});
                                delete(self.feedList[feedItemId]);
                                self.createFeedListTitle();
                            }
                        }
                    });
                }
            },
            setCounter: function(){
                if (this.defaults.counterObj) {
                    var count = Object.keys(this.feedList).length;
                    this.defaults.counterObj.text(count);
                    if (count > 0 && !this.defaults.counterObj.hasClass('red')) {
                        this.defaults.counterObj.addClass('red');
                    } else if(count == 0){
                        this.defaults.counterObj.removeClass('red');
                    }
                }
            },
            setAutoCheck: function(){
                if (this.defaults.checkTimeOut && !this.defaults.timer) {
                    var self = this;
                    this.defaults.timer = setInterval(function(){
                        self.updateFeedList();
                    }, this.defaults.checkTimeOut);
                }
            },
            feedItemCreate: function(feedItemId){
                var itemData = this.feedList[feedItemId];
                var item = $("<li></li>");
                var itemBox = $("<div class='row'></div>").appendTo(item);
                itemBox = $("<div class='col-xs-12'></div>").appendTo(itemBox);
                itemBox = $('<div class="box"></div>').appendTo(itemBox);
                itemBox = $("<div class='box-content'></div>").appendTo(itemBox);
                itemBox.addClass("category-icon " + itemData.category);
                itemBox = $("<div class='col-xs-10 col-xs-offset-2'></div>").appendTo(itemBox);
                itemBox.append('<a class="close-link"><i class="fa fa-times"></i></a>').append('<label>' + itemData.title + '</label>');
                var itemBoxDescr = $('<div></div>').appendTo(itemBox);
                itemBoxDescr.append('<div class="'+ this.defaults.descClass +'">' + itemData.description + '</div>');
                if (itemData.link) {
                    var self = this;
                    var link = $('<a href="' + itemData.link + '" target="_blank">' + this.defaults.linkText  + '</a>');
                    itemBoxDescr.append(link);
                    link.click(function(e){
                        e.stopPropagation();
                        self.setReadedItem(feedItemId, false);
                    });
                }

                item.data('feedItemId', itemData.guid);

                return item;
            }
        }
    });
    $.fn.extend({
        notiFeed: function(settings) {
            var self = this;
            if (!settings.ajax_link && this.last('a')) {
                settings.ajax_link = this.children('a:last').attr('href');
            }
            if (!settings.set_readed_link){
                settings.set_readed_link = settings.ajax_link + '-set-readed';
            }
            if (!settings.set_remind_link){
                settings.set_remind_link = settings.ajax_link + '-set-remind';
            }

            if (!settings.counterObj) {
                settings.counterObj = this.find('a:last > span');
            }

            var notiFeed = $.notiFeed.init(settings);
            this.append(notiFeed.create());
            this.addClass('dropdown');
            notiFeed.container.prev().addClass('dropdown-toggle').data({toggle: 'dropdown'});

            $(this).on('click', function(e){
                e.stopPropagation();
                e.preventDefault();
                $(this).toggleClass('open');
                if ($(this).hasClass('open')) {
                    notiFeed.updateFeedList();
                }
            });

            $(document).on('click', function(e){
               $(self).removeClass('open');
            });

            $(notiFeed.container).on('click', 'li a.close-link', function(e){
                e.preventDefault();
                e.stopPropagation();
                notiFeed.setReadedItem($(this).closest('li').data('feedItemId'));
            });

            $(notiFeed.container).on('click', 'li', function(e){
                e.preventDefault();
                e.stopPropagation();
                notiFeed.defaults.openFeedItemCallback($(this).data('feedItemId'));
            });

            return notiFeed;
        }
    });
})(jQuery);