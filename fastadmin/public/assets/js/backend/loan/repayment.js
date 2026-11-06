define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'loan/repayment/index' + location.search,
                    add_url: 'loan/repayment/add',
                    edit_url: 'loan/repayment/edit',
                    del_url: 'loan/repayment/del',
                    multi_url: 'loan/repayment/multi',
                    table: 'loan_repayment',
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
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'user.nickname', title: __('Member'), operate: false},
                        {field: 'apply_id', title: __('Apply_id'), operate: false},
                        {field: 'period_no', title: __('Period_no'), operate: false},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'due_time', title: __('Due_time'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime, datetimeFormat: 'YYYY-MM-DD'},
                        {
                            field: 'status', title: __('Status'), searchList: {
                                "0": __('Status 0'), "1": __('Status 1'), "2": __('Status 2'), "3": __('Status 3')
                            },
                            custom: {"0": 'warning', "1": 'info', "2": 'success', "3": 'danger'},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'images', title: __('Images'), operate: false, events: Table.api.events.image,
                            formatter: Table.api.formatter.images
                        },
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'paytime', title: __('Paytime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'checkremark', title: __('Checkremark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'checktime', title: __('Checktime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'check',
                                    text: __('Check'),
                                    title: __('Check'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-check-circle',
                                    url: 'loan/repayment/check?ids={ids}',
                                    refresh: true,
                                    hidden: function (row) {
                                        return row.status !== '1';
                                    }
                                }
                            ]
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
        edit: function () {
            Controller.api.bindevent();
        },
        check: function () {
            var submit = function (action) {
                var form = $("#check-form");
                var data = form.serializeArray();
                Fast.api.ajax({
                    url: location.href,
                    data: $.param(data) + '&action=' + action
                }, function (data, ret) {
                    Fast.api.close(ret.data);
                    return false;
                });
            };
            $(document).on("click", ".btn-pass", function () {
                Layer.confirm('确认已收到该笔还款？', {icon: 3, title: __('Warning')}, function (index) {
                    Layer.close(index);
                    submit('pass');
                });
            });
            $(document).on("click", ".btn-reject", function () {
                var remark = $.trim($("#c-checkremark").val());
                if (!remark) {
                    Layer.msg('驳回时请填写原因');
                    return false;
                }
                submit('reject');
            });
            Form.api.bindevent($("#check-form"));
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
