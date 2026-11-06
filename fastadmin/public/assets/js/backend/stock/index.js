define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'stock/index/index' + location.search,
                    add_url: 'stock/index/add',
                    edit_url: 'stock/index/edit',
                    del_url: 'stock/index/del',
                    multi_url: 'stock/index/multi',
                    import_url: 'stock/index/import',
                    table: 'stock',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {
                            field: 'market',
                            title: __('Market'),
                            operate: 'LIKE',
                            searchList: {"TSE": "东京证券(TSE)", "NASDAQ": "纳斯达克(NASDAQ)", "NYSE": "纽约证券(NYSE)", "CRYPTO": "加密货币(CRYPTO)"},
                            formatter: function (value) {
                                var names = {"TSE": "东京证券", "NASDAQ": "纳斯达克", "NYSE": "纽约证券", "CRYPTO": "加密货币"};
                                var text = names[value] ? names[value] : value;
                                var cls = {"TSE": "primary", "NASDAQ": "success", "NYSE": "warning", "CRYPTO": "info"};
                                return '<span class="label label-' + (cls[value] ? cls[value] : 'default') + '">' + text + '</span>';
                            }
                        },
                        {field: 'type', title: __('Type'), searchList: {"stock":__('Type stock'),"crypto":__('Type crypto'),"index":__('Type index'),"sector":__('Type sector')}, formatter: Table.api.formatter.normal},
                        {field: 'currency', title: __('Currency'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'prev_close', title: __('Prev_close'), operate:'BETWEEN'},
                        {field: 'open', title: __('Open'), operate:'BETWEEN'},
                        {field: 'high', title: __('High'), operate:'BETWEEN'},
                        {field: 'low', title: __('Low'), operate:'BETWEEN'},
                        {field: 'change_amount', title: __('Change_amount'), operate:'BETWEEN'},
                        {field: 'change_rate', title: __('Change_rate'), operate:'BETWEEN'},
                        {field: 'volume', title: __('Volume')},
                        {field: 'turnover', title: __('Turnover'), operate:'BETWEEN'},
                        {field: 'total_shares', title: __('Total_shares')},
                        {field: 'lot_size', title: __('Lot_size')},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'is_hot', title: __('Is_hot'), searchList: {"0":__('Is_hot 0'),"1":__('Is_hot 1')}, formatter: Table.api.formatter.normal},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"suspended":__('Status suspended'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'sector', title: __('Sector'), operate: 'LIKE'},
                        {field: 'source', title: __('Source'), operate: 'LIKE'},
                        {field: 'symbol', title: __('Symbol'), operate: 'LIKE'},
                        {field: 'market_cap', title: __('Market_cap'), operate:'BETWEEN'},
                        {field: 'pe_ratio', title: __('Pe_ratio'), operate:'BETWEEN'},
                        {field: 'pb_ratio', title: __('Pb_ratio'), operate:'BETWEEN'},
                        {field: 'dividend_yield', title: __('Dividend_yield'), operate:'BETWEEN'},
                        {field: 'turnover_rate', title: __('Turnover_rate'), operate:'BETWEEN'},
                        {field: 'amplitude', title: __('Amplitude'), operate:'BETWEEN'},
                        {field: 'high_52w', title: __('High_52w'), operate:'BETWEEN'},
                        {field: 'low_52w', title: __('Low_52w'), operate:'BETWEEN'},
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
