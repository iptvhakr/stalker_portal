//
//    Main script of DevOOPS v1.0 Bootstrap Theme
//
"use strict";
/*-------------------------------------------
 Dynamically load plugin scripts
 ---------------------------------------------*/
//
// Dynamically load Fullcalendar Plugin Script
// homepage: http://arshaw.com/fullcalendar
// require moment.js
//
function LoadCalendarScript(callback) {
    function LoadFullCalendarScript() {
        if (!$.fn.fullCalendar) {
            $.getScript('plugins/fullcalendar/fullcalendar.js', callback);
        }
        else {
            if (callback && typeof (callback) === "function") {
                callback();
            }
        }
    }
    if (!$.fn.moment) {
        $.getScript('plugins/moment/moment.min.js', LoadFullCalendarScript);
    }
    else {
        LoadFullCalendarScript();
    }
}
//
// Dynamically load  OpenStreetMap Plugin
// homepage: http://openlayers.org
//
function LoadOpenLayersScript(callback) {
    if (!$.fn.OpenLayers) {
        $.getScript('http://www.openlayers.org/api/OpenLayers.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load  jQuery Timepicker plugin
//  homepage: http://trentrichardson.com/examples/timepicker/
//
function LoadTimePickerScript(callback) {
    if (!$.fn.timepicker) {
        $.getScript('plugins/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load Bootstrap Validator Plugin
//  homepage: https://github.com/nghuuphuoc/bootstrapvalidator
//
function LoadBootstrapValidatorScript(callback) {
    if (!$.fn.bootstrapValidator) {
        $.getScript('plugins/bootstrapvalidator/bootstrapValidator.min.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load jQuery Select2 plugin
//  homepage: https://github.com/ivaynberg/select2  v3.4.5  license - GPL2
//
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
                    $.getScript('plugins/datatables/dataTables.bootstrap.js', callback);
                });
            });
        });
    }
    if (!$.fn.dataTables) {
        LoadDatatables();
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load Widen FineUploader
//  homepage: https://github.com/Widen/fine-uploader  v5.0.1 license - GPL3
//
function LoadFineUploader(callback) {
    if (!$.fn.fineuploader) {
        $.getScript('plugins/fineuploader/jquery.fineuploader-5.0.1.min.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load xCharts plugin
//  homepage: http://tenxer.github.io/xcharts/ v0.3.0 license - MIT
//  Required D3 plugin http://d3js.org/ v3.4.1 license - MIT
//
function LoadXChartScript(callback) {
    function LoadXChart() {
        $.getScript('plugins/xcharts/xcharts.min.js', callback);
    }
    function LoadD3Script() {
        if (!$.fn.d3) {
            $.getScript('plugins/d3/d3.v3.min.js', LoadXChart)
        }
        else {
            LoadXChart();
        }
    }
    if (!$.fn.xcharts) {
        LoadD3Script();
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load Flot plugin
//  homepage: http://www.flotcharts.org  v0.8.2 license- MIT
//
function LoadFlotScripts(callback) {
    function LoadFlotScript() {
        $.getScript('plugins/flot/jquery.flot.js', LoadFlotResizeScript);
    }
    function LoadFlotResizeScript() {
        $.getScript('plugins/flot/jquery.flot.resize.js', LoadFlotTimeScript);
    }
    function LoadFlotTimeScript() {
        $.getScript('plugins/flot/jquery.flot.time.js', callback);
    }
    if (!$.fn.flot) {
        LoadFlotScript();
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load Morris Charts plugin
//  homepage: http://www.oesmith.co.uk/morris.js/ v0.4.3 License - MIT
//  require Raphael http://raphael.js
//
function LoadMorrisScripts(callback) {
    function LoadMorrisScript() {
        if (!$.fn.Morris) {
            $.getScript('plugins/morris/morris.min.js', callback);
        }
        else {
            if (callback && typeof (callback) === "function") {
                callback();
            }
        }
    }
    if (!$.fn.raphael) {
        $.getScript('plugins/raphael/raphael-min.js', LoadMorrisScript);
    }
    else {
        LoadMorrisScript();
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
//
//  Dynamically load jQuery-Knob plugin
//  homepage: http://anthonyterrien.com/knob/  v1.2.5 License- MIT or GPL
//
function LoadKnobScripts(callback) {
    if (!$.fn.knob) {
        $.getScript('plugins/jQuery-Knob/jquery.knob.js', callback);
    }
    else {
        if (callback && typeof (callback) === "function") {
            callback();
        }
    }
}
//
//  Dynamically load Sparkline plugin
//  homepage: http://omnipotent.net/jquery.sparkline v2.1.2  License - BSD
//
function LoadSparkLineScript(callback) {
    if (!$.fn.sparkline) {
        $.getScript('plugins/sparkline/jquery.sparkline.min.js', callback);
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
                opacity: 0.8
            })
            .droppable({
                tolerance: 'pointer',
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
    var a_num = $(a).find('span.curr_num').data('currnum');
    var b_num = $(b).find('span.curr_num').data('currnum');
    
    var t = a.parentNode.insertBefore(document.createTextNode(''), a);
    b.parentNode.insertBefore(a, b);
    t.parentNode.insertBefore(b, t);
    t.parentNode.removeChild(t);
    
    if (typeof(parentArray) == 'object' && parentArray.length !=0) {
        a_num = parseInt(a_num, 10) - 1;
        b_num = parseInt(b_num, 10) - 1;
        var direction = a_num < b_num ? 1: -1;
        var next_num = a_num + direction;
        while( Math.abs(b_num - next_num + direction) > 0) {
            if ( !parentArray[next_num].locked) {
//                var a_n_val = parentArray[a_num].number;
//                var b_n_val = parentArray[next_num].number;
                var temp_i = parentArray[a_num];
                parentArray[a_num] = parentArray[next_num];
                parentArray[next_num] = temp_i;
                a_num = next_num;
            }
            next_num += direction;
        }
        channelListRender('#channelListContainer');
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
 Scripts for DataTables page (tables_datatables.html)
 ---------------------------------------------*/
//
// Function for table, located in element with id = datatable-1
//
function TestTable1() {
    $('#datatable-1').dataTable({
        "bFilter": false,
        "bPaginate": false,
        "bInfo":     false,
        "aoColumnDefs": [ {
          "aTargets": [ 0, -1 ],
          "bSortable": false
        } ]
    });
}

/*-------------------------------------------
 Function for Form Layout page (form layouts.html)
 ---------------------------------------------*/
//
// Example form validator function
//
function DemoFormValidator() {
    $('#defaultForm').bootstrapValidator({
        message: 'This value is not valid',
        fields: {
            username: {
                message: 'The username is not valid',
                validators: {
                    notEmpty: {
                        message: 'The username is required and can\'t be empty'
                    },
                    stringLength: {
                        min: 6,
                        max: 30,
                        message: 'The username must be more than 6 and less than 30 characters long'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_\.]+$/,
                        message: 'The username can only consist of alphabetical, number, dot and underscore'
                    }
                }
            },
            country: {
                validators: {
                    notEmpty: {
                        message: 'The country is required and can\'t be empty'
                    }
                }
            },
            acceptTerms: {
                validators: {
                    notEmpty: {
                        message: 'You have to accept the terms and policies'
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'The email address is required and can\'t be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }
                }
            },
            website: {
                validators: {
                    uri: {
                        message: 'The input is not a valid URL'
                    }
                }
            },
            phoneNumber: {
                validators: {
                    digits: {
                        message: 'The value can contain only digits'
                    }
                }
            },
            color: {
                validators: {
                    hexColor: {
                        message: 'The input is not a valid hex color'
                    }
                }
            },
            zipCode: {
                validators: {
                    usZipCode: {
                        message: 'The input is not a valid US zip code'
                    }
                }
            },
            password: {
                validators: {
                    notEmpty: {
                        message: 'The password is required and can\'t be empty'
                    },
                    identical: {
                        field: 'confirmPassword',
                        message: 'The password and its confirm are not the same'
                    }
                }
            },
            confirmPassword: {
                validators: {
                    notEmpty: {
                        message: 'The confirm password is required and can\'t be empty'
                    },
                    identical: {
                        field: 'password',
                        message: 'The password and its confirm are not the same'
                    }
                }
            },
            ages: {
                validators: {
                    lessThan: {
                        value: 100,
                        inclusive: true,
                        message: 'The ages has to be less than 100'
                    },
                    greaterThan: {
                        value: 10,
                        inclusive: false,
                        message: 'The ages has to be greater than or equals to 10'
                    }
                }
            }
        }
    });
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
            }
            else {
                another_items.find("ul.dropdown-menu").slideUp('fast');
                current.slideDown('fast');
            }
        }
        else {
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
    var height = window.innerHeight - 49;
    $('#main').css('min-height', height)
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
        CloseModalBox();
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
        var _input = $(this).find("input[type='checkbox']");
        _input.prop('checked', !_input.is(":checked"));
        $("#attribute_set li:not(first-of-type) input[type='checkbox']").prop('checked', _input.is(":checked"));
        $('#datatable-1').toggleCollumn('all', _input.is(":checked"));
        e.stopPropagation();
        return false;
    });

    $(document).on('change', "#attribute_set li:not(:first-child) input[type='checkbox']", function (e) {
        var allChbLen = $("#attribute_set li:not(:first-child) input[type='checkbox']").length;
        var chkChbLen = $("#attribute_set li:not(:first-child) input[type='checkbox']:checked").length;
        $("#attribute_set li:first-child input[type='checkbox']").prop('checked', allChbLen == chkChbLen);
        $('#datatable-1').toggleCollumn($(this).val(), $(this).is(":checked"));
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
        $(this).closest('.box').children('.box-header').find('.header_check_info').hide();
    });
    $(document).on('hide', ".box-content[id^='add_channel_']", function (e) {
        $(this).closest('.box').children('.box-header').find('.header_check_info').show();
    });
    $(document).on('show hide', "#add_channel_storage_save", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='storage_save']:checked").val() == 'on'));
    });
    $(document).on('show hide', "#add_channel_EPG", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='epg_xmltv_id']").val().trim() !== ''));
    });
    $(document).on('show hide', "#add_channel_safety", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='age_restriction']:checked").length != 0));
    });
    $(document).on('show hide', "#add_channel_type", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[name='base_channel']:checked").length != 0));
    });
    $(document).on('show hide', "#add_channel_usb_store", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='checkbox']:checked").length != 0));
    });
    $(document).on('show hide', "#add_channel_temp_http_link, #add_channel_link_monitoring, #add_channel_load_balancing", function (e) {
        $(this).closest('.box').children('.box-header').find("input[type='checkbox']").prop("checked", ($(this).find("input[type='radio']:checked").val() == 'on'));
    });


    $(document).on('click', "#add_channel #add_broadcasting_link", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var tmp_id = $('#channel_url_data tr:last').attr('id');
        if (!tmp_id) {
            tmp_id = "tr_" + $.randString();
            $('#channel_url_data tr:last').attr('id', tmp_id);
        }
        $("#modalbox_ad").data('tr_id', tmp_id);

        $("#modalbox_ad").find(".modal-header-name").children('span').text('Добавить URL');
        $("#modalbox_ad").find("input, select").prop("disabled", false).removeAttr('disabled');
        $("#modalbox_ad").show();
//        $(this).closest('.form-group').find('tbody tr:hidden').show();
        return false;
    });
    $(document).on('click', "#modalbox_ad a.close-link", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $("#modalbox_ad").find("input").prop("disabled", "disabled");
        $("#modalbox_ad").hide();
        return false;
    });

    $(document).on('click', "#modalbox_ad button[type='reset']", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $("#modalbox_ad").find("form").each(function () {
            this.reset();
        });
        return false;
    });

    $(document).on('click', "#modalbox_ad button[type='submit']", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var tmp_id = $("#modalbox_ad").data('tr_id');
        var dataObj = {};
        $('#modalbox_ad').find('input, select').each(function () {
            var name = (typeof ($(this).attr('name')) != 'undefined') ? $(this).attr('name').replace('[', '').replace(']', '') : false;
            if (!name) {
                return true;
            }
            var type = typeof ($(this).attr('type')) != 'undefined' ? $(this).attr('type') : $(this).get(0).tagName.toLowerCase();
            var value = $(this).val() || '';
            if (typeof (value) == 'array') {
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

    $(document).on("click", "#channel_url_data .channel_url_remove_data", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest('tr').remove();
        return false;
    });

    $(document).on("click", "#channel_url_data .channel_url_change_data", function (e) {
        e.stopPropagation();
        e.preventDefault();
        var data = channelUrlDataGet($(this).closest('tr'));
        var tmp_id = $(this).closest('tr').attr('id');
        if (!tmp_id) {
            tmp_id = "tr_" + $.randString();
            $(this).closest('tr').attr('id', tmp_id);
        }
        $("#modalbox_ad").data('tr_id', tmp_id);
        $("#modalbox_ad").find('input, select').each(function () {
            var name = (typeof ($(this).attr('name')) != 'undefined') ? $(this).attr('name').replace('[', '').replace(']', '') : false;
            if (typeof (data[name]) == 'undefined') {
                return true;
            }
            $(this).prop("disabled", false).removeAttr("disabled");
            var type = typeof ($(this).attr('type')) != 'undefined' ? $(this).attr('type') : $(this).get(0).tagName.toLowerCase();
            if (type == 'select') {
                var lSelect = $(this);
                lSelect.find('option').prop('selected', false).removeAttr('selected');
                $.each(data[name].value.split(';'), function (i, lValue) {
                    lSelect.find("option[value='" + lValue + "']").prop('selected', 'selected');
                });
            } else if (type == 'checkbox') {
                $(this).prop('checked', (data[name].value == 'on'));
            } else if (type == 'radio') {
                if ($(this).val() == data[name].value) {
                    $(this).prop('checked', 'checked');
                } else {
                    $(this).prop('checked', false).removeAttr('checked');
                }
            } else {
                $(this).val(data[name].value);
            }
        });

        $("#modalbox_ad").find(".modal-header-name").children('span').text('Изменить URL');
        $("#modalbox_ad").show();
        return false;
    });
});

function channelUrlDataGet(container){
    var dataObj = {};
    $(container).find('input').each(function(){
        var name = $(this).attr('name').replace('[', '').replace(']', '');
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
    if (!$(container).is('visible')){
        var clonContainer = $(container).clone(true, true);
        clonContainer.removeAttr('id');
        $(clonContainer).insertAfter(container);
        $(container).css("display", "table-row");
    }
    $(container).find('input').each(function(){
        var name = $(this).attr('name').replace('[', '').replace(']', '');
        if (typeof (data[name]) == 'undefined') {
            return true;
        }
        var type = $(this).attr('type');
        if (type == 'checkbox') {
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
    var maxItemOnBlock = Math.floor(maxBlockHeight/40);
    var currentCount = 0;
    for (var i= 0; i< channelList.length; ) {
        var currentBlock = $("<div/>", {'class': 'col-xs-3 col-sm-2 no-padding'}).appendTo(_container);
        var currentItemsBlock = $("<div/>", {'class': 'col-xs-12 col-sm-12 no-padding'}).appendTo(currentBlock)
        for ( var j = currentCount; j < (currentCount + maxItemOnBlock) && j < channelList.length; j++) {
            currentItemsBlock.append(getChannelListItem(j+1, channelList[j]));
            i++;
        }
        currentBlock.prepend('<div class="col-xs-12 col-sm-12 counter"><span>' + (currentCount + 1) + '-'+ (j) + '</span></div>');
        currentCount = j;
        currentBlock.css('top', 0);
        currentBlock.css('left', (Math.ceil(currentCount/maxItemOnBlock) - 1)*250);
    }
    
    $('#channelListContainer .box-icons a').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var childI = $(this).children('i');
        var childID = parseInt($(this).closest('.box').find('span.curr_num').data('currnum'), 10) - 1;
        if (childI.hasClass('fa-lock')) {
            childI.removeClass('fa-lock').addClass('fa-unlock');
            $(this).closest("div.box").removeClass('no-drop').draggable("enable").droppable("enable");
            channelList[childID].locked = false;
        } else {
            childI.removeClass('fa-unlock').addClass('fa-lock');
            $(this).closest("div.box").addClass('no-drop').draggable("disable").droppable("disable");
            channelList[childID].locked = true;
        }
        return false;
    });
    
     WinMove();
}

function getChannelListItem(num, item){
    return '<div class="box '+(item.locked? 'no-drop': '')+'">\n\
                <div class="box-header">\n\
                    <div class="box-name col-sm-11">\n\
                        <span class="curr_num col-sm-2 no-padding" data-currnum="'+num+'">'+item.number+'</span>\n\
                        <div class="channel col-sm-10 no-padding">\n\
                            <span class=" col-sm-2 no-padding">\n\
                                <img class="img-rounded" src="'+item.logo+'" alt="">\n\
                            </span>\n\
                            <a href="'+item.link+'" class="col-sm-10 no-padding">'+item.name+'</a>\n\
                        </div>\n\
                    </div>\n\
                    <div class="box-icons col-sm-1 no-padding">\n\
                        <a class="lock-link">\n\
                            <i class="fa fa-'+(!item.locked? 'un': '')+'lock"></i>\n\
                        </a>\n\
                    </div>\n\
                    <div class="no-move"></div>\n\
                </div>\n\
            </div>';
}

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

(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);

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