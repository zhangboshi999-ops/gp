define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'transaction/index' + location.search,
                    add_url: 'transaction/add',
                    edit_url: 'transaction/edit',
                    del_url: 'transaction/del',
                    multi_url: 'transaction/multi',
                    import_url: 'transaction/import',
                    table: 'transaction',
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
                        {field: 'sn', title: __('Sn'), operate: 'LIKE'},
                        {field: 'user_id', title: __('User_id')},
                        {field: 'type', title: __('Type'), searchList: {"deposit":__('Type deposit'),"withdraw":__('Type withdraw'),"transfer":__('Type transfer')}, formatter: Table.api.formatter.normal},
                        {field: 'method', title: __('Method'), operate: 'LIKE'},
                        {field: 'currency', title: __('Currency'), operate: 'LIKE'},
                        {field: 'amount', title: __('Amount'), operate:'BETWEEN'},
                        {field: 'fee', title: __('Fee'), operate:'BETWEEN'},
                        {field: 'bank_name', title: __('Bank_name'), operate: 'LIKE'},
                        {field: 'branch_name', title: __('Branch_name'), operate: 'LIKE'},
                        {field: 'account_no', title: __('Account_no'), operate: 'LIKE'},
                        {field: 'account_name', title: __('Account_name'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'txid', title: __('Txid'), operate: 'LIKE'},
                        {field: 'to_user_id', title: __('To_user_id')},
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Status pending'),"success":__('Status success'),"rejected":__('Status rejected')}, formatter: Table.api.formatter.status},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'checkremark', title: __('Checkremark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'checktime', title: __('Checktime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'audit',
                                    text: '审核',
                                    title: '审核充值/提现',
                                    icon: 'fa fa-gavel',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'transaction/audit',
                                    visible: function (row) {
                                        return row.status === 'pending';
                                    }
                                }
                            ],
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
        edit: function () {
            Controller.api.bindevent();
        },
        audit: function () {
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
