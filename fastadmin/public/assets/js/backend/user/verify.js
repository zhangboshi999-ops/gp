define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/verify/index' + location.search,
                    add_url: 'user/verify/add',
                    edit_url: 'user/verify/edit',
                    del_url: 'user/verify/del',
                    multi_url: 'user/verify/multi',
                    import_url: 'user/verify/import',
                    table: 'user_verify',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'real_name', title: __('Real_name'), operate: 'LIKE'},
                        {field: 'id_type', title: __('Id_type'), operate: 'LIKE'},
                        {field: 'id_number', title: __('Id_number'), operate: 'LIKE'},
                        {field: 'country', title: __('Country'), operate: 'LIKE'},
                        {field: 'front_image', title: __('Front_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'back_image', title: __('Back_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'hand_image', title: __('Hand_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"pending":__('Status pending'),"pass":__('Status pass'),"reject":__('Status reject')}, formatter: Table.api.formatter.status},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
                                    title: '审核实名认证',
                                    icon: 'fa fa-gavel',
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    url: 'user/verify/audit',
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
