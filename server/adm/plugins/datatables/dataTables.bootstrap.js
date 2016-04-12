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
        var paginateRow = $(this).parent().prev().children('div.dataTables_paginate');
        var pageCount = Math.ceil((this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength);
        if (pageCount > 1) {
            $("#datatable-1_paginate").css("display", "block");
        } else {
            $("#datatable-1_paginate").css("display", "none");
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
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          oSettings._iDisplayLength === -1 ?
			0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
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
			var oPaging = oSettings.oInstance.fnPagingInfo();
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