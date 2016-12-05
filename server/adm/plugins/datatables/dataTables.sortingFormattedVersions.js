(function() {

    function versionPrepare(a){
        a = a.replace(/^(\d+\.\d+\.\d+).*$/gi, '$1');
        return a.split('.').map(function(n){return parseInt(n, 10);});
    }

    jQuery.extend( jQuery.fn.dataTableExt.oSort, {

        "formatted-version-asc": function ( a, b ) {
            a = versionPrepare(a);
            b = versionPrepare(b);
            return (a[0] - b[0]) * 100 + (a[1] - b[1]) * 10 + (a[2] - b[2]);
        },

        "formatted-version-desc": function ( a, b ) {
            a = versionPrepare(a);
            b = versionPrepare(b);
            return (b[0] - a[0]) * 100 + (b[1] - a[1]) * 10 + (b[2] - a[2]);
        }
    } );
}());