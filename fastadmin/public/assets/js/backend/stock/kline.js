define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'stock/kline/index' + location.search,
                    add_url: 'stock/kline/add',
                    edit_url: 'stock/kline/edit',
                    del_url: 'stock/kline/del',
                    multi_url: 'stock/kline/multi',
                    import_url: 'stock/kline/import',
                    table: 'stock_kline',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'stock_id', title: __('Stock_id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'period', title: __('Period'), operate: 'LIKE'},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'open', title: __('Open'), operate:'BETWEEN'},
                        {field: 'high', title: __('High'), operate:'BETWEEN'},
                        {field: 'low', title: __('Low'), operate:'BETWEEN'},
                        {field: 'close', title: __('Close'), operate:'BETWEEN'},
                        {field: 'volume', title: __('Volume')},
                        {field: 'date', title: __('Date'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
