define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'account/index/index' + location.search,
                    add_url: 'account/index/add',
                    edit_url: 'account/index/edit',
                    del_url: 'account/index/del',
                    multi_url: 'account/index/multi',
                    import_url: 'account/index/import',
                    table: 'account',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'currency', title: __('Currency'), operate: 'LIKE'},
                        {field: 'balance', title: __('Balance'), operate:'BETWEEN'},
                        {field: 'frozen', title: __('Frozen'), operate:'BETWEEN'},
                        {field: 'total_deposit', title: __('Total_deposit'), operate:'BETWEEN'},
                        {field: 'total_withdraw', title: __('Total_withdraw'), operate:'BETWEEN'},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
