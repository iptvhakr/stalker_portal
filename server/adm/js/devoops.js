//
//    Main script of DevOOPS v1.0 Bootstrap Theme
//
"use strict";

/*-------------------------------------------
 Dynamically load plugin scripts
 ---------------------------------------------*/
//
//  Dynamically load jQuery Select2 plugin
//  homepage: https://github.com/ivaynberg/select2  v3.4.5  license - GPL2
//


/* $('.dropdown-toggle').click(function() {
        var dropdownList = $('.dropdown-menu');
        var dropdownOffset = $(this).parent('div').position();
       console.log(dropdownOffset.top);
	
        var dropdownWidth = dropdownList.height();
		console.log(dropdownWidth);
        var docWidth = $('#ajax-content').height();
        console.log(docWidth);

        var isDropdownVisible = (dropdownOffset.top + dropdownWidth + dropdownWidth + dropdownWidth <= docWidth);
     
        if (!isDropdownVisible) {
          $('.dropdown-toggle').parent('div').addClass('dropup');
        } else {
           $('.dropdown-toggle').parent('div').removeClass('dropup');
        }
    });*/

$(document).on("click", "div.dropdown-menu *", function(e){
    e.stopPropagation();
});

$("#modalbox").on("show", function () {
    $("body").addClass("modal-open");
}).on("hide", function () {
    $("body").removeClass("modal-open");
});
$('#modalbox_ad').appendTo("#wrapper");
$('#modalbox').appendTo("#wrapper");

$("#ui-datepicker-div").css("z-index:5050;")

$("#modalbox_ad").on("show", function () {
    $("body").addClass("modal-open");
}).on("hide", function () {
    $("body").removeClass("modal-open");
});
$('#attribute_set').removeClass('dropup');
$('#status').removeClass('dropup');
$('.filter').removeClass('dropup'); 
for (var f = document.forms, i = f.length; i--;)f[i].setAttribute("novalidate", i)
function LoadSelect2Script(callback) {
    if (!$.fn.select2) {
        $.getScript('plugins/select2/select2.min.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load DataTables plugin
//  homepage: http://datatables.net v1.9.4 license - GPL or BSD
//
function LoadDataTablesScripts(callback) {
    function LoadDatatables() {
        $.getScript('plugins/datatables/jquery.dataTables.js', function () {
            $.getScript('plugins/datatables/ZeroClipboard.js', function () {
                $.getScript('plugins/datatables/TableTools.js', function () {
                    $.getScript('plugins/datatables/fnReloadAjax.js', function () {
                        $.getScript('plugins/datatables/dataTables.bootstrap.js', function(){
                            $.fn.dataTableExt.oApi.fnDataUpdate = function ( oSettings, nRowObject, iRowIndex ){
                                $(nRowObject).find("TD").each( function(i) {
                                    var iColIndex = oSettings.oApi._fnVisibleToColumnIndex( oSettings, i );
                                    oSettings.oApi._fnSetCellData( oSettings, iRowIndex, iColIndex, $(this).html() );
                                } );
                            };

                            $.fn.dataTableExt.oApi.fnFeatureReloadButtonHtml = function ( oSettings, nRowObject, iRowIndex ){
                                alert('jjj');
                            };

                            $.fn.dataTableExt.aoFeatures.push( {
                                "fnInit": function( oDTSettings ) {
                                    var filterContainer = $(oDTSettings.nTableWrapper).find("#" + oDTSettings.sTableId + '_filter');
                                    if (filterContainer.length) {
                                        filterContainer.after('<button id="dataTables_ajax_update_button" class="btn" type="button"><i class="fa fa-refresh"></i></button>');
                                        $(document).on("click", "#dataTables_ajax_update_button", function(){
                                            $("#" + oDTSettings.sTableId).DataTable().ajax.reload();
                                        });
                                    }
                                },
                                "cFeature": "A"
                            } );
							
                            $.fn.dataTable.defaults.sDom += "A";

                            $.fn.dataTable.defaults.stateSave = true;
                            $.fn.dataTable.defaults.stateDuration = 0;
                            $.fn.dataTable.defaults.stateSaveCallback = function(settings,data) {
                                var page = window.location.href.split("/");
                                page = (page[page.length - 1] ? page[page.length - 1] : page[page.length - 2]).replace(/[^\w]/ig, '');
                                console.log(page + " dataTable save settings");
                                localStorage.setItem( page + 'DataTables_' + settings.sInstance, JSON.stringify(data) )
                            };
                            $.fn.dataTable.defaults.stateLoadCallback = function(settings) {
                                var page = window.location.href.split("/");
                                page = (page[page.length - 1] ? page[page.length - 1] : page[page.length - 2]).replace(/[^\w]/ig, '');
                                console.log(page + " dataTable load settings");
                                return JSON.parse( localStorage.getItem( page + 'DataTables_' + settings.sInstance ) )
                            };
                            callback();
                        });
                    });
                });
            });
        });
    }
    if (!$.fn.dataTables) {
        LoadDatatables();
    } else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}

//
//  Dynamically load Fancybox 2 plugin
//  homepage: http://fancyapps.com/fancybox/ v2.1.5 License - MIT
//
function LoadFancyboxScript(callback) {
    if (!$.fn.fancybox) {
        $.getScript('plugins/fancybox/jquery.fancybox.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
/*-------------------------------------------
 Main scripts used by theme
 ---------------------------------------------*/
//
//  Function for load content from url and put in $('.ajax-content') block
//
function LoadAjaxContent(url) {
    if (url.length == 0) {
        return false;
    }
    $('.preloader').show();
    window.location = url;
    return;
    $.ajax({
        url: url,
        mimeType: 'text/html; charset=utf-8', // ! Need set mimeType only when run from local file
        type: 'GET',
        success: function (data) {
            $('#ajax-content').html(data);
            $('.preloader').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert(errorThrown);
        },
        dataType: "html",
        async: false
    });
}
//
//  Function maked all .box selector is draggable, to disable for concrete element add class .no-drop
//
function WinMove() {
    var parentArray = (typeof(channelList) != 'undefined') ? channelList : {};
    $("div.box").draggable({
                revert: true,
                zIndex: 2000,
                cursor: "crosshair",
                handle: '.box-name',
				class: 'highlight',
                opacity: 0.8
            })
            .droppable({
                tolerance: 'pointer',
//                activeClass: "ui-state-hover",
                hoverClass: "highlight",//"ui-state-active",
                drop: function (event, ui) {
                    var draggable = ui.draggable;
                    var droppable = $(this);
                    var dragPos = draggable.position();
                    var dropPos = droppable.position();
                    draggable.swap(droppable, parentArray);
                    setTimeout(function () {
                        var dropmap = droppable.find('[id^=map-]');
                        var dragmap = draggable.find('[id^=map-]');
                        if (dragmap.length > 0 || dropmap.length > 0) {
                            dragmap.resize();
                            dropmap.resize();
                        }
                        else {
                            draggable.resize();
                            droppable.resize();
                        }
						
                    }, 50);
                    setTimeout(function () {
					
                        draggable.find('[id^=map-]').resize();
                        droppable.find('[id^=map-]').resize();
                    }, 250);
                    if ($("#channelListContainer").length > 0) {
                        setTimeout(function () {
                            channelListRender('#channelListContainer');
                        }, 300);
                    }
                }
            });
    $('div.box.no-drop').draggable( "disable" ).droppable( "disable" );
}
//
// Swap 2 elements on page. Used by WinMove function
//
jQuery.fn.swap = function (b, parentArray) {
    b = jQuery(b)[0];
    var a = this[0];
    var a_num = $(a).find('span.curr_num').data('number');
    var b_num = $(b).find('span.curr_num').data('number');
    var remove = $(b).children('div').hasClass('empty');
//    var t = a.parentNode.insertBefore(document.createTextNode(''), a);
    b.parentNode.insertBefore(a, b);
//    t.parentNode.insertBefore(b, t);
//    t.parentNode.removeChild(t);
//    if (remove) {
//        setTimeout(function () {
//            $(b).remove();
//        }, 300);
//    }
    if (typeof(parentArray) == 'object' && parentArray.length !=0) {
        a_num = parseInt(a_num, 10) - 1;
        b_num = parseInt(b_num, 10) - 1;
        
        var direction = a_num < b_num ? 1: -1;
        var next_num = a_num + direction;
        
//        if (remove) {
//            parentArray.splice(b_num, 1);
//            b_num += direction;
//        }
        while( Math.abs(b_num - next_num + direction) > 0) {
            if ( typeof(parentArray[next_num]) != 'undefined' && typeof(parentArray[next_num].locked) != 'undefined' &&  !parentArray[next_num].locked) {
                var temp_a_number = parentArray[a_num].number;
                var temp_next_number = parentArray[next_num].number;
                
                var temp_a = parentArray[a_num];
                parentArray[a_num] = parentArray[next_num];
                parentArray[next_num] = temp_a;
                
                parentArray[a_num].number = temp_a_number;
                parentArray[next_num].number = temp_next_number;
                
                a_num = next_num;
            }
            next_num += direction;
        }
    }
    return this;
};
//
//  Function set min-height of window (required for this theme)
//
function SetMinBlockHeight(elem) {
    elem.css('min-height', window.innerHeight - 49)
}
//
//  Beauty tables plugin (navigation in tables with inputs in cell)
//  Created by DevOOPS.
//
(function ($) {
    $.fn.beautyTables = function () {
        var table = this;
        var string_fill = false;
        this.on('keydown', function (event) {
            var target = event.target;
            var tr = $(target).closest("tr");
            var col = $(target).closest("td");
            if (target.tagName.toUpperCase() == 'INPUT') {
                if (event.shiftKey === true) {
                    switch (event.keyCode) {
                        case 37: // left arrow
                            col.prev().children("input[type=text]").focus();
                            break;
                        case 39: // right arrow
                            col.next().children("input[type=text]").focus();
                            break;
                        case 40: // down arrow
                            if (string_fill == false) {
                                tr.next().find('td:eq(' + col.index() + ') input[type=text]').focus();
                            }
                            break;
                        case 38: // up arrow
                            if (string_fill == false) {
                                tr.prev().find('td:eq(' + col.index() + ') input[type=text]').focus();
                            }
                            break;
                    }
                }
                if (event.ctrlKey === true) {
                    switch (event.keyCode) {
                        case 37: // left arrow
                            tr.find('td:eq(1)').find("input[type=text]").focus();
                            break;
                        case 39: // right arrow
                            tr.find('td:last-child').find("input[type=text]").focus();
                            break;
                        case 40: // down arrow
                            if (string_fill == false) {
                                table.find('tr:last-child td:eq(' + col.index() + ') input[type=text]').focus();
                            }
                            break;
                        case 38: // up arrow
                            if (string_fill == false) {
                                table.find('tr:eq(1) td:eq(' + col.index() + ') input[type=text]').focus();
                            }
                            break;
                    }
                }
                if (event.keyCode == 13 || event.keyCode == 9) {
                    event.preventDefault();
                    col.next().find("input[type=text]").focus();
                }
                if (string_fill == false) {
                    if (event.keyCode == 34) {
                        event.preventDefault();
                        table.find('tr:last-child td:last-child').find("input[type=text]").focus();
                    }
                    if (event.keyCode == 33) {
                        event.preventDefault();
                        table.find('tr:eq(1) td:eq(1)').find("input[type=text]").focus();
                    }
                }
            }
        });
        table.find("input[type=text]").each(function () {
            $(this).on('blur', function (event) {
                var target = event.target;
                var col = $(target).parents("td");
                if (table.find("input[name=string-fill]").prop("checked") == true) {
                    col.nextAll().find("input[type=text]").each(function () {
                        $(this).val($(target).val());
                    });
                }
            });
        })
    };
})(jQuery);
//
// Beauty Hover Plugin (backlight row and col when cell in mouseover)
//
//
(function ($) {
    $.fn.beautyHover = function () {
        var table = this;
        table.on('mouseover', 'td', function () {
            var idx = $(this).index();
            var rows = $(this).closest('table').find('tr');
            rows.each(function () {
                $(this).find('td:eq(' + idx + ')').addClass('beauty-hover');
            });
        })
                .on('mouseleave', 'td', function (e) {
                    var idx = $(this).index();
                    var rows = $(this).closest('table').find('tr');
                    rows.each(function () {
                        $(this).find('td:eq(' + idx + ')').removeClass('beauty-hover');
                    });
                });
    };
})(jQuery);

/*-------------------------------------------
 Function for Form Layout page (form layouts.html)
 ---------------------------------------------*/
//
// Example form validator function
//
//
//  Helper for correct size of Messages page
//
function MessagesMenuWidth(){
	var W = window.innerWidth;
	var W_menu = $('#sidebar-left').outerWidth();
	var w_messages = (W-W_menu)*16.666666666666664/100;
	$('#messages-menu').width(w_messages);
}

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
//
//      MAIN DOCUMENT READY SCRIPT OF DEVOOPS THEME
//
//      In this script main logic of theme
//
//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
$(document).ready(function () {
    $( document ).ajaxComplete(function( event,request, settings ) {
        if (typeof(request.responseJSON) !== 'undefined' && typeof(request.responseJSON.error) !== 'undefined' && request.responseJSON.error == 'Need authorization') {
            window.location.reload(true);
            console.log(request.responseJSON.error);
        }
    });
    $( document ).ajaxError(function( event,request, settings ) {
        if (typeof(request.responseJSON) !== 'undefined' && typeof(request.responseJSON.error) !== 'undefined' && request.responseJSON.error == 'Need authorization') {
            window.location.reload(true);
            console.log(request.responseJSON.error);
        }
    });

    if ($("#allowed_locales").length > 0) {
        $("#allowed_locales").on('click', 'a', function(e){
            e.stopPropagation();
            e.preventDefault();
            $.cookies.set('language', $(this).data('locale'), {expiresAt: new Date( 2037, 1, 1 )});
            window.location.reload(true);
            return false;
        });
    }

    $('.show-sidebar').on('click', function (e) {
        e.preventDefault();
        $('div#main').toggleClass('sidebar-show');
        setTimeout(MessagesMenuWidth, 250);
    });
//    var ajax_url = 'http://test1.localhost/stalker_portal/server/administrator/';
    var ajax_url = '';
    var ajax_hash = location.hash.replace(/^#/, '');
    if (ajax_hash.search('ajax') == -1) {
//        ajax_url += ajax_hash.length < 1 ? 'ajax/login.html' : ajax_hash;
        ajax_url += ajax_hash.length < 1 ? '' : ajax_hash;
    } else {
        ajax_url = ajax_hash;
    }
    if (ajax_url.length != 0) {
        LoadAjaxContent(ajax_url);
    } else {
        $('.preloader').hide();
    }
    $('.main-menu').on('click', 'a', function (e) {
        if ($(this).next().length == 0) {
            return true;
        } 
        e.stopPropagation();
        e.preventDefault();
        
        var parents = $(this).parents('li');
        var li = $(this).closest('li.dropdown');
        var another_items = $('.main-menu li').not(parents);
        another_items.find('a').removeClass('active');
        another_items.find('a').removeClass('active-parent');
        if ($(this).hasClass('dropdown-toggle') || $(this).closest('li').find('ul').length == 0) {
            $(this).addClass('active-parent');
            var current = $(this).next();
            if (current.is(':visible')) {
                li.find("ul.dropdown-menu").slideUp('fast');
                li.find("ul.dropdown-menu a").removeClass('active')
            } else {
                another_items.find("ul.dropdown-menu").slideUp('fast');
                current.slideDown('fast');
            }
        } else {
            if (li.find('a.dropdown-toggle').hasClass('active-parent')) {
                var pre = $(this).closest('ul.dropdown-menu');
                pre.find("li.dropdown").not($(this).closest('li')).find('ul.dropdown-menu').slideUp('fast');
            }
        }
        if ($(this).hasClass('active') == false) {
            $(this).parents("ul.dropdown-menu").find('a').removeClass('active');
            $(this).addClass('active')
        }
        if ($(this).hasClass('ajax-link')) {
            e.preventDefault();
            if ($(this).hasClass('add-full')) {
                $('#content').addClass('full-content');
            }
            else {
                $('#content').removeClass('full-content');
            }
            var url = $(this).attr('href');
            window.location.hash = url;
            LoadAjaxContent(url);
        }
        if ($(this).attr('href') == '#') {
            e.preventDefault();
        }
    });

    $('#wrapper').css({minHeight: heightCalculate()})
            .on('click', '.expand-link', function (e) {
                var body = $('body');
                e.preventDefault();
                var box = $(this).closest('div.box');
                var button = $(this).find('i');
                button.toggleClass('fa-expand').toggleClass('fa-compress');
                box.toggleClass('expanded');
                body.toggleClass('body-expanded');
                var timeout = 0;
                if (body.hasClass('body-expanded')) {
                    timeout = 100;
                }
                setTimeout(function () {
                    box.toggleClass('expanded-padding');
                }, timeout);
                setTimeout(function () {
                    box.resize();
                    box.find('[id^=map-]').resize();
                }, timeout + 50);
            })
            .on('click', '.collapse-link', function (e) {
                e.preventDefault();
                var box = $(this).closest('div.box');
                var button = $(this).find('i');
                var content = box.find('div.box-content');
                content.slideToggle('fast');
                button.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
                setTimeout(function () {
                    box.resize();
                    box.find('[id^=map-]').resize();
                }, 50);
            })
            .on('click', '.close-link', function (e) {
                e.preventDefault();
                var content = $(this).closest('div.box');
                content.remove();
            });
    $('#locked-screen').on('click', function (e) {
        e.preventDefault();
        $('body').addClass('body-screensaver');
        $('#screensaver').addClass("show");
        ScreenSaver();
    });
    $('body').on('click', 'a.close-link', function (e) {
        e.preventDefault();
//        CloseModalBox();
    });
    $(document).on('click', 'a', function (e) {
        if ($(this).hasClass('ajax-link')) {
            e.preventDefault();
            if ($(this).hasClass('add-full')) {
                $('#content').addClass('full-content');
            }
            else {
                $('#content').removeClass('full-content');
            }
            var url = $(this).attr('href');
            window.location.hash = url;
            LoadAjaxContent(url);
        }
    });
    $('#search').on('keydown', function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            $('#content').removeClass('full-content');
            ajax_url = 'ajax/page_search.html';
            window.location.hash = ajax_url;
            LoadAjaxContent(ajax_url);
        }
    });
    $('#screen_unlock').on('mouseover', function () {
        var header = 'Enter current username and password';
        var form = $('<div class="form-group"><label class="control-label">Username</label><input type="text" class="form-control" name="username" /></div>' +
                '<div class="form-group"><label class="control-label">Password</label><input type="password" class="form-control" name="password" /></div>');
        var button = $('<div class="text-center"><a href="index.html" class="btn btn-primary">Unlock</a></div>');
        OpenModalBox(header, form, button);
    });
    
    $(document).on("click mousedown mouseup", "#attribute_set .checkbox", function (e) {
        e.stopPropagation();
    });

    $(document).on('click', "#attribute_set li:first-of-type label", function (e) {
        var is_checked = !$(this).find("input[type='checkbox']").is(":checked");
        $(this).find("input[type='checkbox']").prop('checked', is_checked);
        var sendData = {};
        $("#attribute_set input[type='checkbox']:not(:first):not(:last)").each(function(index){
            $(this).prop('checked', is_checked);
            $("#datatable-1").dataTable().fnSetColumnVis( index, is_checked );
            sendData[$(this).val()] = is_checked;
        });
        setDropdownAttribute(sendData);
        e.stopPropagation();
        return false;
    });

    $(document).on('change', "#attribute_set li:not(:first-child) input[type='checkbox']", function (e) {
        $("#attribute_set input[type='checkbox']").each(function(index){
            $("#datatable-1").dataTable().fnSetColumnVis( index - 1, $(this).prop('checked') );
        });
        var allChbLen = $("#attribute_set li:not(:first-child) input[type='checkbox']").length;
        var chkChbLen = $("#attribute_set li:not(:first-child) input[type='checkbox']:checked").length;
        $("#attribute_set li:first-child input[type='checkbox']").prop('checked', allChbLen == chkChbLen);
        var sendData = {};
        $("#attribute_set input[type='checkbox']").each(function(){
            sendData[$(this).val()] = $(this).prop('checked');
        });
        setDropdownAttribute(sendData);
    });
    
    
    $(document).on("click mousedown mouseup", "div.attribute_set[id!='attribute_set']  .checkbox", function (e) {
        e.stopPropagation();
    });

    $(document).on('click', "div.attribute_set[id!='attribute_set'] li:first-of-type label", function (e) {
        var _input = $(this).find("input[type='checkbox']");
        _input.prop('checked', !_input.is(":checked"));
        var parentSetName = "#" + $(this).closest('div.attribute_set').attr('id');
        if($(this).closest('div.attribute_set').find('.divider').length != 0){
            $(parentSetName + " li:not(first-of-type) input[type='checkbox']").prop('checked', _input.is(":checked"));
        } else if(_input.is(":checked")){
            $(parentSetName + " input[type='checkbox']").prop('checked', false).removeAttr('checked');
            _input.prop('checked', true);
        }
        e.stopPropagation();
        return false;
    });

    $(document).on('change', "div.attribute_set[id!='attribute_set'] li:not(:first-child) input[type='checkbox']", function (e) {
        var parentSetName = "#" + $(this).closest('div.attribute_set').attr('id');
        if($(this).closest('div.attribute_set').find('.divider').length != 0){
            var allChbLen = $(parentSetName + " li:not(:first-child) input[type='checkbox']").length;
            var chkChbLen = $(parentSetName + " li:not(:first-child) input[type='checkbox']:checked").length;
            $(parentSetName + " li:first-child input[type='checkbox']").prop('checked', allChbLen == chkChbLen);
        } else if($(this).is(":checked")){
            $(parentSetName + " input[type='checkbox']").prop('checked', false).removeAttr('checked');
            $(this).prop('checked', true);
        }
    });

    $(document).on('change', "#add_channel input[type='checkbox'], #adm_grp_perm :checkbox", function(e){
        if ($(this).is(':checked')) {
            $(this).val('on');
        } else {
            $(this).val('off');
        }
    });


    $(document).on("click", '#datatable-1 a', function (e) {
        if ($(this).hasClass('ajax-link')) {
            e.preventDefault();
            if ($(this).hasClass('add-full')) {
                $('#content').addClass('full-content');
            }
            else {
                $('#content').removeClass('full-content');
            }
            var url = $(this).attr('href');
            window.location.hash = url;
            LoadAjaxContent(url);
        }
        if ($(this).attr('href') == '#') {
            e.preventDefault();
        }
    });

    $(document).on('click', "#add_channel .box-name .toggle-switch", function (e) {
        e.stopPropagation();
        e.preventDefault();
        return false;
    });

    $(document).on('show', ".box-content[id^='add_channel_']", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find('.header_check_info').hide();
        return false;
    });
    $(document).on('hide', ".box-content[id^='add_channel_']", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find('.header_check_info').show();
        return false;
    });
    $(document).on('show hide', "#add_channel_storage_save", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='checkbox']:checked").length != 0));
        return false;
    });
    $(document).on('show hide', "#add_channel_EPG", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='text']").val().trim() !== ''));
        return false;
    });
    $(document).on('show hide', "#add_channel_safety", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='age_restriction']:checked").length != 0));
        return false;
    });
    $(document).on('show hide', "#add_channel_type", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='base_channel']:checked").length != 0));
        return false;
    });
    $(document).on('show hide', "#add_channel_usb_store", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='checkbox']:checked").length != 0));
        return false;
    });
    $(document).on('show hide', "#add_channel_link_monitoring, #add_channel_load_balancing", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='radio']:checked").val() == 'on'));
        return false;
    });

    $(document).on('click', "#modalbox_ad a.close-link, #modalbox_ad a.close-link .fa-times, #modalbox_ad, #modalbox_ad button[type='reset']", function (e) {
        if (e.target != e.currentTarget) {
            return;
        }
        e.stopPropagation();
        e.preventDefault();
        if (typeof(conf) == 'object' && typeof(conf.form) != 'undefined' && typeof($.validate) == 'function' ) {
            $.validate();
            $(conf.form).get(0).reset();
        }
        $("#modalbox_ad").find("input").prop("disabled", "disabled");
        $("#modalbox_ad").hide();
        return false;
    });

    $(document).on('click submit', "#modalbox_ad .channel-form button[type='submit']", function (e) {
        e.stopPropagation();
        e.preventDefault();

        if (typeof(conf) == 'object' && typeof(conf.form) != 'undefined') {
            conf.form = '#add_channel_link_form';
            conf.formContainer = '#modalbox_ad';
            $(conf.form).prop('novalidate', 0).removeAttr('novalidate');
            if ($(conf.form).isValid({}, conf, true)) {
                conf.onSuccess();
                $(conf.form).prop('novalidate', 1);
            } else {
                conf.onError();
                return false;
            }
        }

        var tmp_id = $("#modalbox_ad").data('tr_id');
        var dataObj = {};

        var currentCMD = $("#modalbox_ad #add_channel_url_base input[name*='cmd']");
        var currentCMDVal = $(currentCMD).val();
        currentCMDVal = currentCMDVal.replace(/\s/ig, '');
        var duplicate = false;

        $('#cmd_data tr:visible td:first-of-type input[name*="cmd"]').each(function(){
            var existsCMDVal = $(this).val();
            existsCMDVal = existsCMDVal.replace(/\s/ig, '');
            var currID = $(this).closest('tr').attr('id') || false;
            if (existsCMDVal == currentCMDVal &&  currID != tmp_id) {
                duplicate = true;
                return false;
            }
        });

        if (duplicate) {
            currentCMD.closest('div').after('<span class="help-inline col-xs-12 col-sm-12 duplicate"><span class="txt-danger">' + words['CMD_Exists'] + "</span></span>");
            $("#modalbox_ad").scrollTo(0);
            currentCMD.focus();
            return false;
        } else {
            currentCMD.closest('div').next('span.duplicate').remove();
        }

        $('#modalbox_ad').find('input, select').each(function () {
            var name = (typeof ($(this).attr('name')) != 'undefined') ? $(this).attr('name').replace(/\[\d*?\]/ig, '') : false;
            if (!name) {
                return true;
            }
            var type = typeof ($(this).attr('type')) != 'undefined' ? $(this).attr('type') : $(this).get(0).tagName.toLowerCase();
            var value = $(this).val() || '';
            if (value instanceof Array) {
                value = value.join(';');
            }
            if (type == 'checkbox') {
                dataObj[name] = {'value': ($(this).prop('checked') ? 'on' : 'off'), 'type': type};
            } else if (type == 'radio') {
                if ($(this).prop('checked')) {
                    dataObj[name] = {'value': $(this).val(), 'type': type};
                }
            } else if (type == 'select' && $(this).attr('multiple')) {
                dataObj[name] = {'value': value, 'type': type};
            } else {
                dataObj[name] = {'value': value, 'type': type};
            }
        });
        channelUrlDataUpdate("#" + tmp_id, dataObj);
        $("#modalbox_ad").find("input, select").prop("disabled", "disabled");
        $("#modalbox_ad").find("form").each(function () {
            this.reset();
        });
        $("#modalbox_ad").hide();
        return false;
    });

    $(document).on('click', 'div[data-tvfilter] ul a, div[data-tvfilter] .dropdown-menu button', function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (!$(this).data("postponed")) {
            window.location.href = getURLFilterString(this);
            return false;
        }
    });
    
    $(document).off('click', "a[disabled], a.disabled");
    $(document).on('click', "a[disabled], a.disabled", function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).clearQueue();
        $(this).stop();
        return false;
    });
    
    $("#breadcrumb ul").addClass("breadcrumb");

    $(document).on('click', 'div[id^="ui-datepicker"], div[id^="ui-datepicker"] *, .ui-datepicker-next, .ui-datepicker-prev', function(e){
        e.stopPropagation();
    });

    $.datepicker.setDefaults({
        showButtonPanel: true,
        beforeShow: function (input) {
            $(input).attr('readonly', 'readonly');
            setTimeout(function () {
                var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");
                buttonPane.empty();
                $("<button>", {
                    text: words['Clean'],
                    click: function (e) {
                        //Code to clear your date field (text box, read only field etc.) I had to remove the line below and add custom code here
                        $.datepicker._clearDate(input);
                        e.stopPropagation();
                    }
                }).appendTo(buttonPane).addClass("ui-datepicker-clear ui-state-default ui-priority-primary ui-corner-all");
            }, 1);
        },
        onChangeMonthYear: function (year, month, instance) {
            setTimeout(function () {
                var buttonPane = $(instance).datepicker("widget").find(".ui-datepicker-buttonpane");
                buttonPane.empty();
                $("<button>", {
                    text: words['Clean'],
                    click: function (e) {
                        //Code to clear your date field (text box, read only field etc.) I had to remove the line below and add custom code here
                        $.datepicker._clearDate(instance.input);
                        e.stopPropagation();
                    }
                }).appendTo(buttonPane).addClass("ui-datepicker-clear ui-state-default ui-priority-primary ui-corner-all");
            }, 1);
        }
    });

    $('[id*="datepicker"]').attr('readonly', 'readonly');
    $(document).on('click', "#modalbox, #modalbox a.close-link, #modalbox a.close-link *", function(e){
        if (e.currentTarget != e.target) {
            return;
        }
        e.stopPropagation();
        e.preventDefault();
        if (typeof($("#modalbox").data('complete')) == 'undefined' || $("#modalbox").data('complete') == 1) {
            if (typeof(closeModalBox) == 'function') {
                closeModalBox();
            } else {
                JScloseModalBox();
            }
        } else {
            for(var i=0;i<3;i++) {
                $('#modalbox > div').fadeTo('slow', 0.5).fadeTo('slow', 1.0);
            }
        }
        return false;
    });
    
});

function getURLFilterString(obj){
    var hrefM = window.location.href;
    var filterName = $(obj).closest('div[data-tvfilter]').data('tvfilter');
    var filter_str = 'filters[' + filterName + ']=' + ((obj.tagName == "A") ? $(obj).data('filter'): $(obj).prev('input').val() );
    if (window.location.search == '') {
        return hrefM + '?' + filter_str;
    } else {
        var filterRegExp = new RegExp('filters\\[' + filterName + '[^=]*=[^&|^$]*', 'ig');
        return filterRegExp.test(hrefM) ? hrefM.replace(filterRegExp, filter_str) : hrefM + '&' + filter_str;
    }
}


function heightCalculate(){
    var height =$("#content").height;
    var tableHeight = $("#datatable-1").length ? ($('#datatable-1 tr').length > 50 ? 50 : $('#datatable-1 tr').length) * 50 + $('#datatable-1').position().top + 200: 0;
    if (tableHeight > height) {
        height = tableHeight;
    }
    $('.attribute_set, [data-tvfilter]').each(function(){
        var newHeight = $(this).position().top + $(this).children('a').height() + $(this).children('ul').height();
        if (newHeight > height) {
            height = newHeight;
        }
        $('#main-wrapper').css({minHeight: height});
    });
    return height;
}

function channelUrlDataGet(container){
    var dataObj = {};
    $(container).find('input').each(function(){
        var name = $(this).attr('name').replace(/\[\d*?\]/ig, '');
        var value = $(this).val();
        var type = $(this).attr('type');
        if (type != 'checkbox') {
            dataObj[name] = {'value': value, 'type': type};
        } else {
            dataObj[name] = {'value': ($(this).prop('checked') ? 'on': 'off'), 'type': type};
        }
    });
    return dataObj;
}

function channelUrlDataUpdate(container, data){
    if ($(container).css('display') == 'none'){
        var clonContainer = $(container).clone(true, true);
        clonContainer.removeAttr('id');
        $(clonContainer).insertAfter(container);
        $(container).css("display", "table-row");
        var countRow = $(container).parent().find($(container).get(0).tagName+":visible").length;
        $(container).find('input').each(function(){
            $(this).attr('name', $(this).attr('name').replace(/\d+?/ig, countRow));
            $(this).attr('id', $(this).attr('id').replace(/\d+?/ig, countRow));
        });
    }
    $(container).find('input').each(function(){
        $(this).prop("disabled", false).removeAttr("disabled");
        var name = $(this).attr('name').replace(/\[\d*?\]/ig, '');
        if (typeof (data[name]) == 'undefined') {
            return true;
        }
        var type = $(this).attr('type');
        if (type == 'checkbox') {
            if ($(this).attr('name').search('enable_monitoring') != -1) {
                var targetLabel = $(this).closest('td').next('td').find('label');
                if (targetLabel.text() != '-' && $(this).prop('checked') != (data[name].value == 'on')) {
                    targetLabel.text('-');
                    targetLabel.removeClass();
                }
            }
            $(this).prop('checked', (data[name].value == 'on'));
        } else {
            $(this).val(data[name].value);
        }
        var label = $(this).prev("label[data-field='"+name+"']");
        if (label) {
            label.text(data[name].value);
        }
    });
}

function channelListRender(container){

    var _container = $(container);
    _container.empty();
    var maxBlockHeight = $(window).height()- _container.offset().top - 50;
    _container.height(maxBlockHeight);
    maxBlockHeight -= $(document).height() - $(window).height();
    _container.height(maxBlockHeight +10);
    var maxBlockWidth = $(window).width()- _container.offset().left - 50;
    _container.width(maxBlockWidth+10);
    var maxItemOnBlock = Math.floor(maxBlockHeight/50);
    var currentCount = 0;
    for (var i= 0; i< channelList.length; ) {
        var currentBlock = $("<div/>", {'class': 'no-padding'}).appendTo(_container);
        var currentItemsBlock = $("<div/>", {'class': 'no-padding'}).appendTo(currentBlock)
        for ( var j = currentCount; j < (currentCount + maxItemOnBlock) && j < channelList.length; j++) {
            if (typeof(channelList[j]) == 'undefined') {
                continue;
            }
            currentItemsBlock.append(getChannelListItem(j+1, channelList[j]));
            i++;
        }
        currentBlock.prepend('<div class=" counter"><span>' + (currentCount + 1) + '-'+ (j) + '</span></div>');
        currentCount = j;
        currentBlock.css('top', 0);
        currentBlock.css('left', (Math.ceil(currentCount/maxItemOnBlock) - 1)*250);
        if (j >= channelList.length) {
            break;
        }
    }

     WinMove();
}

function getChannelListItem(num, item){
    var return_val = '<div class="box '+(item.locked? 'no-drop': '')+'"  style="position:relative; z-index:30;">\n\
                <div class="box-header '+ (item.empty == '1'? 'empty': '') + '"  style="position:relative; z-index:30;">\n\
                    <div class="box-name col-sm-11"  style="position:relative; z-index:30;">\n\
                        <span class="curr_num col-xs-1 col-sm-1 no-padding" data-number="'+num+'">'+item.number+'</span>\n\
                        <div class="channel col-xs-10 col-sm-10 no-padding">\n\
                            <span class="no-padding">\n\
                              <!----  <img class="img-rounded" src="'+item.logo+'" alt="">--->\n\
                            </span>\n\
                            <a style="position:relative; z-index:300;" href="'+item.link+'" class="no-padding">'+item.name+'</a>';
	    if (item.empty != '1') {
        return_val +='<div class="box-icons col-sm-1 no-padding"><a style="position:relative; z-index:300;" class="lock-link">\n\
                        <i data-id="' + item.id + '" class="fa fa-'+(!item.locked? 'un': '')+'lock"></i>\n\
                        </a></div>';
	}						
                 return_val +='    </div>\n\
                    </div>';

    
    return_val +='  <div class="no-move"></div>\n\
                </div>\n\
            </div>';
    return return_val;
}

function ajaxSuccess(data, alertMsg, consoleMsg){
    var alertMsg = typeof(alertMsg) != 'undefined'? alertMsg: true;
    var consoleMsg = typeof(consoleMsg) != 'undefined'? consoleMsg: true;
    if (data.success  && !data.error) {
        if ($.isFunction(window[data.action])) {
            window[data.action](data);
        }
    } else if (data.error) {
        ajaxError({responseJSON: data});
    } else {
        if (alertMsg) {
            alert('Some server error');
        }
        if (consoleMsg) {
            console.log('Some server error');
        }
    }
}

function notty(data,type){
    var msg = data;    
	
	if (type == 'undefined') { type="notification"; }

	if (type=="notification") { var timeout = "1000"; } else { var timeout = "10000";/*false;*/ }
 	noty({
		text: msg,
		layout      :	'topCenter',
		type        :	type,
		theme       :	'defaultTheme',
		timeout		:	timeout,
		animation: {
			open: {height: 'toggle'}, // jQuery animate function property object
			close: {height: 'toggle'}, // jQuery animate function property object
			easing: 'swing', // easing
		    speed: 500 // opening & closing animation speed
    }
});
}

function ajaxError(data, alertMsg, consoleMsg){
    var alertMsg = typeof(alertMsg) != 'undefined'? alertMsg: true;
    var consoleMsg = typeof(consoleMsg) != 'undefined'? consoleMsg: true;
    var errAction = '';
    if (typeof (data.responseJSON) == 'object') {
        errAction += data.responseJSON.action + 'Error';
    }
    if ($.isFunction(window[errAction])) {
        window[errAction](data.responseJSON);
    } else {
        if (alertMsg) {
            alert('Some server error');
        }
        if (consoleMsg) {
            console.log('Some server error');
        }
        if ($.isFunction(window['errAction'])) {
            window['errAction']();
        } else {
            JSErrorModalBox();
        }
    }
}

function ajaxPostSend(url, sendData, alertMsg, consoleMsg, async){
    var alertMsg = typeof(alertMsg) != 'undefined'? alertMsg: true;
    var consoleMsg = typeof(consoleMsg) != 'undefined'? consoleMsg: true;
    var async = typeof(async) != 'undefined' ? async: false;
    $.ajax({
        url: url,
        type: 'POST',
        data: sendData,
        success: function (data) {
            ajaxSuccess(data, alertMsg, consoleMsg);
        },
        error: function (data) {
            if (typeof(data.nothing_to_do) != 'undefined' && data.nothing_to_do) {
                JScloseModalBox();
            } else {
                ajaxError(data, alertMsg, consoleMsg);
            }
        },
        timeout: 0,
        dataType: "json",
        async: async
    });
}

(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);

function setDropdownAttribute(sendData){
    var param = '';
    var filterLink = $("a.btn-success.active[href*='filters']");
    if (filterLink.length > 0) {
        param = 'with-button-filters';
    }
    ajaxPostSend('./?set-dropdown-attribute' + (param != '' ? "=" + param : ''), sendData);
}

jQuery.extend({
    random: function (X) {
        return Math.floor(X * (Math.random() % 1));
    },
    randomBetween: function (MinV, MaxV) {
        return MinV + jQuery.random(MaxV - MinV + 1);
    },
    randString: function (n) {
        if (!n) {
            n = 5;
        }

        var text = '';
        var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        for (var i = 0; i < n; i++) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }

        return text;
    }
});

(function($) {
    var re = /([^&=]+)=?([^&]*)/g;
    var decode = function(str) {
        return decodeURIComponent(str.replace(/\+/g, ' '));
    };
    $.parseParams = function(query) {
        var params = {}, e;
        if (query) {
            if (query.substr(0, 1) == '?') {
                query = query.substr(1);
            }

            while (e = re.exec(query)) {
                var k = decode(e[1]);
                var v = decode(e[2]);
                if (params[k] !== undefined) {
                    if (!$.isArray(params[k])) {
                        params[k] = [params[k]];
                    }
                    params[k].push(v);
                } else {
                    params[k] = v;
                }
            }
        }
        return params;
    };
})(jQuery);

var d = new Date();
if (!$.isFunction(d['toLocaleFormat'])) {
    Date.prototype.toLocaleFormat = function (format) {
        var year = this.getFullYear();
        var yearShort = year.toString().slice(2);
        var month = this.getMonth() + 1;
            month = (month.toString().length == 1) ? '0' + month: month;
        var monthNum = this.getMonth();
        var monthName = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        if (typeof (words) != 'undefined') {
            for(var counter in monthName){
                if (typeof words[monthName[counter]] != 'undefined') {
                    monthName[counter] = words[monthName[counter]];
                }
            }
        }
        var day = this.getDate();
            day = (day.toString().length == 1) ? '0' + day: day;
        var hour = this.getHours();
            hour = (hour.toString().length == 1) ? '0' + hour: hour;
        var min = this.getMinutes();
            min = (min.toString().length == 1) ? '0' + min: min;
        var sec = this.getSeconds();
            sec = (sec.toString().length == 1) ? '0' + sec: sec;
        return format.replace("%Y", year).replace("%y", yearShort).replace("%m", month).replace("%d", day).replace("%H", hour).replace("%M", min).replace("%s", sec).replace("%b", monthName[monthNum]) ;
    };    
}

String.prototype.ucfirst = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.ucwords = function () {
    return this.replace(/^(.)|\s(.)/g, function ($1) {
        return $1.toUpperCase();
    });
};

String.prototype.camelCase = function () {
    return (this.charAt(0).toLowerCase() + this.ucwords().slice(1)).replace(' ', '');
};

function JScloseModalBox(){
     /*$.noty.closeAll();*/
    $("#modalbox").hide();
    $("#modalbox_ad").hide();
    $("#modalbox").data('complete', 1);
}
        
function JSshowModalBox(){
    notty('<span>' + words['Request_is_being_prossessed'] + '...</span>','notification');
}

function JSSuccessModalBox(data) {
    var msg = '';
    if (typeof(data) != 'undefined') {
        msg = (typeof(data.msg) != 'undefined' ? data.msg : '');
    } else {
        data = {};
    }
    if (typeof(data.nothing_to_do) == 'undefined' || !data.nothing_to_do) {
        notty('<span>' + words['Done'] + '! ' + msg + '</span>', 'success');
    }
    $("#modalbox").hide();
    $("#modalbox_ad").hide();
    $("#modalbox").data('complete', 1);
}

function JSErrorModalBox(data){
        var msg = '';
        if (typeof(data) != 'undefined') {
            msg = ( typeof(data.msg) != 'undefined' ? data.msg : '');
            msg = ( msg.length == 0 && typeof(data.error) != 'undefined' ? data.error : msg);
        } else {
            data = {};
        }
    if (typeof(data.nothing_to_do) == 'undefined' || !data.nothing_to_do) {
        notty('<span>' + words['Failed'] + '! ' + msg + '!</span>', 'error');
    }
    $("#modalbox").data('complete', 1);
}