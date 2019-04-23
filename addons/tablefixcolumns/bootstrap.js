require.config({
    paths: {
        'table-fix-columns': '../addons/tablefixcolumns/js/bootstrap-table-fixed-columns',
    },
    shim: {
        'table-fix-columns': {
            deps: ['jquery','bootstrap-table']
        },
    }
});
if ($("table.table").size() > 0) {
    require(['table-fix-columns']);
}