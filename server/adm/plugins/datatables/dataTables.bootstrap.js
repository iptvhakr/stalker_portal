/* Set the defaults for DataTables initialisation */
$.extend(true, $.fn.dataTable.defaults, {
	"searchHighlight": true,
    "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
    "sPaginationType": "bootstrap",
    "iDisplayLength": 50,
	"fnInitComplete": function (oSettings) {
        var tWidth = oSettings.oInstance.width();
        var tOffset = oSettings.oInstance.offset().left;

        if (oSettings.sTableId !== 'applications_version_table' && tWidth > 980 && tWidth <= 1280) {
            $("body").css({width: tOffset + tWidth});
        }
        $(oSettings.nTableWrapper).on("input keypress keyup", "input", function (e) {
            this.value = this.value.replace(/^\s+/ig, '').replace(/\s{2}/ig, ' ');
        });
	},
    "fnDrawCallback": function (oSettings) {
        var paginateRow = $(oSettings.nTableWrapper).find('div.dataTables_paginate');
        var pageCount = Math.ceil((this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength);
        if (pageCount > 1) {
            $(paginateRow).css("display", "block");
        } else {
            $(paginateRow).css("display", "none");
        }
        if (oSettings.fnRecordsDisplay() && oSettings.aoData && oSettings.aoData.length) {
            var tableHeight = oSettings.oInstance.height();
            var ddMenuMaxHeight = 0, ddMenuHeight = 0, trParentOffset = 0, ddMenuItem;
            $(oSettings.nTable).children("tbody").find('tr').each(function(){
                ddMenuItem = $(this).find('td:last-of-type').find(".dropdown-menu");
                if (!ddMenuItem.length) {
                    console.log("ddMenu not found");
                    return false;
                }
                ddMenuItem.closest('dropup').removeClass('dropup');
                trParentOffset = $(this).position();
                trParentOffset = trParentOffset.top;
                ddMenuHeight = ddMenuItem.height() + 10;

                if (ddMenuHeight > ddMenuMaxHeight){
                    ddMenuMaxHeight = ddMenuHeight ;
                }

                if (ddMenuHeight > tableHeight) {
                    return true;
                }

                if ((trParentOffset > ddMenuHeight) && (trParentOffset + ddMenuHeight - 30) > tableHeight ) {
                    ddMenuItem.closest('div').addClass('dropup');
                }

            });

            if (((tableHeight - trParentOffset) - ddMenuMaxHeight) < 30) {
                $(oSettings.nTableWrapper).css('minHeight', ddMenuMaxHeight + tableHeight + 30);
            }
        }
        var oSearch = oSettings.oSearch? oSettings.oSearch: oSettings.oPreviousSearch;
        var classOperation = (oSearch.sSearch) ? 'addClass': 'removeClass';
        var table = this.DataTable();
        $.each(oSettings.aoColumns, function(){
            var header = table.column(this.idx).header();
            if (this.bSearchable) {
                $(header)[classOperation]('DThighlight');
                table.columns( this.idx ).nodes().flatten().to$()[classOperation]( 'DThighlight' );
            } else {
                $(header)[classOperation]('DTbacklight');
                table.columns( this.idx ).nodes().flatten().to$()[classOperation]( 'DTbacklight' );
            }
        });
    },
    "fnRowCallback": function (nRow, aData, iDisplayIndex) {
        if (aData && aData.RowOrder) {
            nRow.setAttribute('id', aData.RowOrder);  //Initialize row id for every row
        }
    },
    "ajax" : {
        data: function(data) {
            data = dataTableDataPrepare(data);
        }
    },
    "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
    },
    "aoColumnDefs": [
        {"width": "16px", "targets": [-1]}
    ]
});

$.extend(true, $.fn.dataTable.defaults.column, {
    "createdCell" : function (td, cellData, rowData, row, col) {
        var oSettings = this.fnSettings();
        var oSearch = this.fnSettings().oSearch? this.fnSettings().oSearch: this.fnSettings().oPreviousSearch;
        var colSettings = oSettings.aoColumns[col];
        if (oSearch.sSearch) {
            if (colSettings.bSearchable) {
                $(td).addClass('DThighlight');
            } else {
                $(td).addClass('DTbacklight');
            }
        }
    }
});

/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
	"sWrapper": "dataTables_wrapper form-inline"
} );


/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings)
{
    var page = oSettings.pageNoAjax && !(oSettings.pageNoAjax instanceof Object)? oSettings.pageNoAjax: Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength );
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.b_server_side ? oSettings._iRecordsTotal * 1 : oSettings.aiDisplayMaster.length, // oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          oSettings._iDisplayLength === -1 ?
			0 : page , //Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    oSettings._iDisplayLength === -1 ?
			0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
};


/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).append(
				'<ul class="pagination">'+
					'<li class="prev disabled"><a href="#"><i class="fa fa-arrow-left"></i>'+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a href="#">'+oLang.sNext+'<i class="fa fa-arrow-right"></i></a></li>'+
				'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 5;
			var oPaging = oSettings.oInstance.fnPagingInfo(oSettings);
			var an = oSettings.aanFeatures.p;
			var i, ien, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, ien=an.length ; i<ien ; i++ ) {
				// Remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// Add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// Add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
} );

$.fn.dataTableExt.oApi.fnRemoveCurrentRow = function ( oSettings, row ){

    if (oSettings.oInstance.DataTable().row( row )) {
        oSettings.oInstance.DataTable().rows( row ).remove(); // .invalidate('data')
        oSettings._iRecordsDisplay--;
        oSettings._iRecordsTotal--;
        if (oSettings.aoData.length ) {
            oSettings.oInstance.reDrawNoAjax();
        } else if (oSettings._iRecordsTotal) {
            oSettings.pageNoAjax--;
            oSettings.oInstance.DataTable().page(oSettings._iDisplayStart >= oSettings._iDisplayLength ? 'previous': 'next').draw(false);
        }
    }
};

$.fn.dataTableExt.oApi.reDrawNoAjax = function(oSettings) {
    oSettings.pageNoAjax = oSettings.oInstance.DataTable().page();
    oSettings.ajax_data_get = oSettings.oInstance.dataTable.settings[0]['bAjaxDataGet'];
    oSettings.b_server_side = oSettings.oInstance.dataTable.settings[0]['oFeatures'];
    oSettings.oInstance.dataTable.settings[0]['bAjaxDataGet'] = false;
    /*oSettings._iRecordsDisplay = oSettings.oInstance.DataTable().data().length;*/
    oSettings.oInstance.DataTable().page(oSettings.pageNoAjax).draw(false);
    /*oSettings.oInstance.dataTable.settings[0]['oFeatures'] = false; // oSettings.oInstance._fnUpdateInfo();*/
    oSettings.oInstance.dataTable.settings[0]['bAjaxDataGet'] = oSettings.ajax_data_get;
    oSettings.oInstance.dataTable.settings[0]['oFeatures'] = oSettings.b_server_side;
    oSettings.oInstance._fnCustomUpdateInfo(oSettings.pageNoAjax);
};

$.fn.dataTableExt.oApi._fnCustomUpdateInfo = function( settings , page) {
    /* Show information about the table
    * * `\_START\_` - Display index of the first record on the current page
     * * `\_END\_` - Display index of the last record on the current page
     * * `\_TOTAL\_` - Number of records in the table after filtering
     * * `\_MAX\_` - Number of records in the table without filtering
     * * `\_PAGE\_` - Current page number
     * * `\_PAGES\_` - Total number of pages of data in the table
    * */
    var nodes = settings.aanFeatures.i;
    if ( nodes.length === 0 ) {
        return;
    }
    var oFeatures = settings.oInstance.dataTable.settings[0]['oFeatures'],
        lang  = settings.oLanguage,
        start = (settings._iDisplayStart+ 1), // + (page * settings._iDisplayLength) ,
        max   = settings.fnRecordsTotal(),
        total = settings.fnRecordsDisplay();
        settings.oInstance.dataTable.settings[0]['oFeatures'] = false;

    var
        end   = settings.fnDisplayEnd() + (page * settings._iDisplayLength),
        out   = total ? lang.sInfo : lang.sInfoEmpty;

    if ( total !== max ) {
        /* Record set after filtering */
        out += ' ' + lang.sInfoFiltered;
    }

    // Convert the macros
    out += lang.sInfoPostFix;
    out = out.replace(/_START_/g, settings.fnFormatNumber.call( settings, start ) ).
            replace(/_END_/g,   settings.fnFormatNumber.call( settings, end ) ).
            replace(/_MAX_/g,   settings.fnFormatNumber.call( settings, max )).
            replace(/_TOTAL_/g,   settings.fnFormatNumber.call( settings, total ) );
    out = settings.oInstance._fnInfoMacros( out );

    var callback = lang.fnInfoCallback;
    if ( callback !== null ) {
        out = callback.call( settings.oInstance,
            settings, start, end, max, total, out
        );
    }

    settings.oInstance.dataTable.settings[0]['oFeatures'] = oFeatures;

    $(nodes).html( out );
};

$.fn.dataTableExt.oApi.fnCheckJSON = function ( oSettings ){

    var lengthM = oSettings.json.data.length > oSettings.aoData.length ? oSettings.json.data.length : oSettings.aoData.length;
    for (var i = 0; i < lengthM; i ++) {
        if (i < oSettings.aoData.length) {
            if (oSettings.json.data[i]) {
                var status = 0;
                for ( var p in oSettings.aoData[i]._aData) {
                    if (typeof(oSettings.json.data[i][p]) != 'undefined' && oSettings.aoData[i]._aData[p] == oSettings.json.data[i][p]) {
                        status = 1;
                    } else {
                        status = 0;
                        break;
                    }
                }
                if (!status) {
                    oSettings.json.data.splice(i, 1);
                    i--;
                }
            } else {
                oSettings.json.data.push(oSettings.aoData[i]);
                i--;
            }
        } else if( i < oSettings.json.data.length) {
            oSettings.json.data.splice(i, oSettings.json.length - i);
            lengthM = oSettings.json.data.length;
        }
    }
};

/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ( $.fn.DataTable.TableTools ) {
	// Set the classes that TableTools uses to something suitable for Bootstrap
	$.extend( true, $.fn.DataTable.TableTools.classes, {
		"container": "DTTT btn-group",
		"buttons": {
			"normal": "btn",
			"disabled": "disabled"
		},
		"collection": {
			"container": "DTTT_dropdown dropdown-menu",
			"buttons": {
				"normal": "",
				"disabled": "disabled"
			}
		},
		"print": {
			"info": "DTTT_print_info modal"
		},
		"select": {
			"row": "active"
		}
	} );

	// Have the collection use a bootstrap compatible dropdown
	$.extend( true, $.fn.DataTable.TableTools.DEFAULTS.oTags, {
		"collection": {
			"container": "ul",
			"button": "li",
			"liner": "a"
		}
	} );
}

function dataTableDataPrepare(data) {
    var visibleFields = {};
    var dataFields = data.columns.map(function(el){ return el.data;});
    $("table.dataTable").each(function(){
        var tmpF = {length: 0};
        var aoColumns = $(this).dataTable().fnSettings().aoColumns;
        $.each(aoColumns, function(){
            if (dataFields.indexOf(this.data) === -1) {
                tmpF.length = 0;
                return true;
            }
            tmpF[this.data] = this.bVisible;
            tmpF.length++;
        });
        if (tmpF.length != 0) {
            delete tmpF.length;
            visibleFields = tmpF;
            return false;
        }
    });
    $.each(data.columns, function(){
        if (visibleFields.hasOwnProperty(this.data)) {
            this.visible = visibleFields[this.data];
        }
    });
    var params = $.parseParams(window.location.href.split('?')[1] || ''); //window.location.href.split('?')[1] || ''
    for (var i in params) {
        data[i] = params[i];
    }
    return data;
}