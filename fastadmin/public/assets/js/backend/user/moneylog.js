define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/moneylog/index' + location.search,
                    add_url: 'user/moneylog/add',
                    del_url: 'user/moneylog/del',
                    table: 'user_money_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                fixedColumns: true,
                fixedRightNumber: 1,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {
                            field: 'user.nickname', title: __('Member'), operate: false,
                            formatter: function (value, row) {
                                return value ? value + '（#' + row.user_id + '）' : '-';
                            }
                        },
                        {
                            field: 'money', title: __('Change amount'), operate: 'BETWEEN', sortable: true,
                            formatter: function (value) {
                                var v = parseFloat(value) || 0;
                                var cls = v > 0 ? 'text-success' : 'text-danger';
                                return '<span class="' + cls + '">' + (v > 0 ? '+' : '') + v + '</span>';
                            }
                        },
                        {field: 'before', title: __('Before'), operate: 'BETWEEN'},
                        {field: 'after', title: __('After'), operate: 'BETWEEN'},
                        {
                            field: 'admin.nickname', title: __('Operator'), operate: false,
                            formatter: function (value) {
                                return value ? value : '-';
                            }
                        },
                        {field: 'memo', title: __('Memo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
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
