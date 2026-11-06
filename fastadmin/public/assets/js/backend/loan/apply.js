define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'loan/apply/index' + location.search,
                    add_url: 'loan/apply/add',
                    edit_url: 'loan/apply/edit',
                    del_url: 'loan/apply/del',
                    multi_url: 'loan/apply/multi',
                    import_url: 'loan/apply/import',
                    table: 'loan_apply',
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
                        {field: 'user.nickname', title: __('Member'), operate: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate: 'BETWEEN'},
                        {field: 'purpose', title: __('Purpose'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {
                            field: 'status', title: __('Status'), searchList: {
                                "0": __('Status 0'), "1": __('Status 1'), "2": __('Status 2'), "3": __('Status 3'), "4": __('Status 4')
                            },
                            custom: {"0": 'warning', "1": 'success', "2": 'danger', "3": 'info', "4": 'primary'},
                            formatter: Table.api.formatter.status
                        },
                        {field: 'term', title: __('Term'), operate: false},
                        {field: 'payment', title: __('Payment'), operate: false},
                        {field: 'interest', title: __('Interest'), operate: false},
                        {field: 'total', title: __('Total'), operate: false},
                        {field: 'withdraw_amount', title: __('Withdraw_amount'), operate: false},
                        {
                            field: 'bank.account_name', title: __('Bank account'), operate: false,
                            formatter: function (value, row, index) {
                                if (!row.bank) {
                                    return '<span class="text-muted">-</span>';
                                }
                                var no = row.bank.account_no ? String(row.bank.account_no) : '';
                                var tail = no.length > 4 ? no.substr(-4) : no;
                                return row.bank.bank_name + '<br>' +
                                    row.bank.account_type + ' ' + (tail ? '****' + tail : '') + ' / ' + value;
                            }
                        },
                        {field: 'checkremark', title: __('Checkremark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'checktime', title: __('Checktime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'withdrawtime', title: __('Withdrawtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'pass',
                                    text: __('Pass'),
                                    title: __('Pass'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-check',
                                    url: 'loan/apply/pass?ids={ids}',
                                    refresh: true,
                                    hidden: function (row) {
                                        return row.status !== '0';
                                    }
                                },
                                {
                                    name: 'reject',
                                    text: __('Reject'),
                                    title: __('Reject'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-times',
                                    url: 'loan/apply/reject?ids={ids}',
                                    refresh: true,
                                    hidden: function (row) {
                                        return row.status !== '0';
                                    }
                                },
                                {
                                    name: 'pay',
                                    text: __('Pay'),
                                    title: __('Pay'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-jpy',
                                    url: 'loan/apply/pay?ids={ids}',
                                    refresh: true,
                                    hidden: function (row) {
                                        return row.status !== '3';
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
        pass: function () {
            Controller.api.bindevent();
        },
        reject: function () {
            Controller.api.bindevent();
        },
        pay: function () {
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
