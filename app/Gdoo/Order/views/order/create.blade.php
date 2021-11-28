<?php extract($form); ?>
<div class="form-panel">
    <div class="form-panel-header">
        <div class="pull-right">
    </div>
    {{$btn}}

    @if(is_customer())
    @else
        <a href="javascript:orderDialog();" class="btn btn-sm btn-default">
            未发货订单
        </a>
    @endif

</div>

<div class="form-panel-body panel-form-{{$action}}">
    <form class="form-horizontal form-controller" method="post" id="{{$table}}" name="{{$table}}">

        {{$tpl}}

    </form>
</div>

<script>
var form_action = '{{$action}}';
(function ($) {

    function get_customer_id() {
        var customer_id = $('#customer_order_customer_id').val();
        return customer_id;
    }

    $('#customer_order_tax_id').on('change', function() {
        serviceCustomerMoney(this.value);
    })

    if (form_action == 'show') {
        $('#balance_money').text('');
        $('#lock_money').text('');
    } else {
        $('#customer_order_lock_money').val('');
        $('#customer_order_balance_money').val('');
    }
    
    function serviceCustomerMoney(tax_id) {
        $.get(app.url('order/order/serviceCustomerMoney'), {tax_id: tax_id}, function (res) {
            if (form_action == 'show') {
                $('#lock_money').text(res.lockMoney);
                $('#balance_money').text(res.accMoney);
            } else {
                $('#customer_order_lock_money').val(res.lockMoney);
                $('#customer_order_balance_money').val(res.accMoney);
            }
        }, 'json');
    }

    function get_customer_tax(customer_id, tax_id) {
        $.post(app.url('customer/tax/dialog'), {customer_id: customer_id}, function (res) {
            var html = '<option value=""> - </option>';
            $.each(res.data, function (i, row) {
                var selected = tax_id == row.id ? 'selected="selected"' : '';
                html += '<option value="' + row.id + '" ' + selected + '>' + row.name + '</option>';
            });
            $('#customer_order_tax_id').html(html);
        }, 'json');
    }

    var grid = null;
    var customer_id = get_customer_id();
    if (customer_id > 0) {
        var tax_id = "{{$row['tax_id']}}";
        get_customer_tax(customer_id, tax_id);
        serviceCustomerMoney(tax_id);
    }

    gdoo.event.set('customer_order.customer_id', {
        query(params) {
        },
        onSelect(row) {
            if (row) {
                $('#customer_order_type_id').val(row.type_id);
                $('#customer_order_type_id_select').val(row.type_id);
                $('#customer_region_region_id').val(row.region_id);
                $('#customer_region_region_id_text').val(row.region_id_name || '');
                $('#customer_order_warehouse_contact').val(row.warehouse_contact);
                $('#customer_order_warehouse_phone').val(row.warehouse_phone);
                $('#customer_order_warehouse_tel').val(row.warehouse_tel);
                var $option = $('<option selected>'+ row.warehouse_address + '</option>').val(row.warehouse_address);
                $('#customer_order_warehouse_address').html($option).trigger('change');
                get_customer_tax(row.id);
                return true;
            }
        }
    });

    // 子表对话框
    gdoo.event.set('customer_order_data.product_id', {
        open(params) {
            params.url = 'product/product/serviceCustomer';
        },
        query(query) {
            var customer_id = get_customer_id();
            query.customer_id = customer_id;
        },
        onSelect(row, selected) {
            row.is_gift_name = '否';
            row.is_gift = 0;
            if (row.product_code == '99001') {
                row.type_id_name = '费用';
                row.type_id = 5;
            } else {
                row.type_id_name = '普通';
                row.type_id = 1;
            }
            row.price = selected.price;
            row.weight = selected.weight;
            return true;
        }
    });

    // 选择收货地址
    gdoo.event.set('customer_order.warehouse_address', {
        init(params) {
            params.ajax.url = app.url('customer/delivery-address/dialog');
            params.resultCache = false;
        },
        query(query) {
            query.customer_id = get_customer_id();
        },
        onSelect(row) {
            if (row) {
                if (row.id.indexOf('draft_') == 0) {
                    return true;
                }
                $('#customer_order_warehouse_address').val(row.address);
                $('#customer_order_warehouse_contact').val(row.name);
                $('#customer_order_warehouse_phone').val(row.phone);
                $('#customer_order_warehouse_tel').val(row.tel);
                return true;
            }
        }
    });

    // grid初始化事件
    gdoo.event.set('grid.customer_order_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'product_id';
        },
        onSaveBefore(rows) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var quantity = toNumber(row.delivery_quantity);
                var price = toNumber(row.price);
                var money = toNumber(row.money).toFixed(2);
                var other_money = toNumber(row.other_money).toFixed(2);
                var new_money = (quantity * price).toFixed(2);
                if (quantity > 0 && price > 0) {
                    if (new_money != money) {
                        toastrError(row.product_name + ' 实发数量 * 单价不等于金额');
                        return false;
                    }
                    if (other_money > 0) {
                        if (other_money != money) {
                            toastrError(row.product_name + '其他金额不等于金额');
                            return false;
                        }
                    }
                }
            }
            return true;
        },
        editable: {
            product_name(params) {
                var data = params.data;
                // 费用不能编辑
                if (data.product_code == '99001') {
                    return false;
                }

                var customer_id = get_customer_id();
                if (customer_id.trim() == '') {
                    toastrError('请先选择客户');
                    return false;
                } else {
                    return true;
                }
            },
            price(params) {
                var type_id = $('#customer_order_type_id').val();
                if (type_id == 2) {
                    return true;
                } else {
                    return false;
                }
            },
            money(params) {
                var data = params.data;
                if (data.product_code == '99001') {
                    return true;
                } else {
                    return false;
                }
            },
            quantity(params) {
                var data = params.data;
                if (data.type_id == 1 || data.type_id == 2) {
                    return true;
                } else {
                    return false;
                }
            }, 
            delivery_quantity(params) {
                if (params.colDef._editable) {
                    var data = params.data;
                    if (data.type_id == 1 || data.type_id == 2) {
                        return true;
                    } else {
                        return false;
                    }
                }
                
            }
        }
    });

    var promotionDialog = function () {
        var customer_id = get_customer_id();
        if (customer_id.trim() == '') {
            toastrError('请先选择客户');
            return;
        }
        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];

        if (form_action == 'show') {
        } else {
            buttons.push({
                text: '提交',
                'class': 'btn-info',
                click: function () {
                    var masters = $ref_promotion.api.getSelectedRows();
                    var master = masters[0];
                    if (master['status'] == 1) {
                        var rows = $ref_promotion_data.api.getSelectedRows();
                        for (let i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            row.is_gift_name = '是';
                            row.is_gift = 1;
                            row.quantity = row.wsy_num;
                            row.delivery_quantity = row.quantity;
                            row.total_weight = row.quantity * row.weight;
                            row.other_money = row.money;
                            grid.api.memoryStore.create(row);
                        }
                        grid.generatePinnedBottomData();
                        $(this).dialog('close');
                    } else {
                        toastrError('促销申请未生效无法使用。');
                    }
                }
            });
        }
        $.dialog({
            title: '促销列表',
            url: '{{url("promotion/promotion/serviceSaleOrder")}}?customer_id=' + customer_id,
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }

    var costDialog = function () {
        var customer_id = get_customer_id();
        if (customer_id.trim() == '') {
            toastrError('请先选择客户');
            return;
        }

        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];

        if (form_action == 'show') {
        } else {
            buttons.push({
                text: "提交",
                'class': "btn-info",
                click: function () {
                    var cost_money = 0, product_money = 0;
                    grid.api.forEachNode(function(node) {
                        var money = parseFloat(node.data.money) || 0;
                        // 费用是负数
                        if (node.data.money < 0) {
                            cost_money += money;
                        } else {
                            product_money += money;
                        }
                    });

                    // 费用必须低于订单金额的20%
                    cost_money = cost_money + (product_money * 0.2);
                    var rows = $ref_customer_cost.api.getSelectedRows();
                    for (let i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        // 费比大于0
                        if (cost_money > 0) {
                            // 剩余的费比小于费用
                            if (cost_money < row.money) {
                                // 计算剩余的费比可以使用多少费用
                                var money = row.money - (row.money - cost_money);
                            } else {
                                var money = row.money;
                            }
                            // 减少费比
                            cost_money = cost_money - row.money;
                        } else {
                            break;
                        }
                        // 金额保留小数两位
                        money = Math.round(money * 100)/100;
                        row.money = 0 - money;
                        grid.api.memoryStore.create(row);
                    }
                    grid.generatePinnedBottomData();
                    $(this).dialog('close');
                }
            });
        }

        $.dialog({
            title: '费用列表',
            url: '{{url("customerCost/cost/serviceSaleOrder")}}?customer_id=' + customer_id,
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }

    var orderDialog = function () {
        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];

        if (form_action == 'show') {
            var customer_name = "{{$row['customer']['name']}}";
        } else {
            var customer_name = $('#customer_order_customer_id_text').val();
            buttons.push({
                text: '提交',
                'class': 'btn-info',
                click: function () {
                    var row = $ref_customer_order.api.getSelectedRows()[0];
                    if (row) {
                        $('#customer_order_customer_id').val(row.customer_id);
                        $('#customer_order_customer_id_text').val(row.customer_name);

                        $('#customer_order_tax_type').val(row.tax_type);
                        get_customer_tax(row.customer_id, row.tax_id);

                        $('#customer_order_type_id').val(row.type_id);
                        $('#customer_order_type_id_select').val(row.type_id);

                        $('#customer_order_found_contact').val(row.found_contact);
                        $('#customer_order_found_phone').val(row.found_phone);
                        
                        $('#customer_order_remark').val(row.remark);

                        $('#customer_order_warehouse_contact').val(row.warehouse_contact);
                        $('#customer_order_warehouse_phone').val(row.warehouse_phone);
                        $('#customer_order_warehouse_tel').val(row.warehouse_tel);
                        var $option = $('<option selected>'+ row.warehouse_address + '</option>').val(row.warehouse_address);
                        $('#customer_order_warehouse_address').html($option).trigger('change');
                    }
        
                    var rows = $ref_customer_order_data.api.getSelectedRows();
                    grid.api.setRowData([]);
                    for (let i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        row.ref_sale_id = row.sale_id;
                        row.ref_sale_data_id = row.id;
                        row.quantity = row.wf_num;
                        row.delivery_quantity = row.wf_num;
                        grid.api.memoryStore.create(row);
                    }
                    grid.generatePinnedBottomData();
                    $(this).dialog('close');
                }
            });
        }
        $.dialog({
            title: '未发货订单',
            url: '{{url("order/order/serviceNotDelivery")}}?field_0=customer.name&condition_0=like&search_0=' + customer_name,
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }

    window.orderDialog = orderDialog;
    window.costDialog = costDialog;
    window.promotionDialog = promotionDialog;
})(jQuery);

</script>