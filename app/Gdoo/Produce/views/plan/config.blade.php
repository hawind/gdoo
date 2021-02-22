<div class="form-panel">
    <div class="form-panel-header">
        <a data-toggle="dialog-view" data-url="product/product/dialog" data-form_id="product_formula" data-id="product_id" data-name="product_name" data-title="产品" data-is_grid="1" data-multi="1" class="btn btn-sm btn-default"><i class="fa fa-plus-circle"></i> 添加</a>
        <a class="btn btn-default btn-sm" id="product_formula_delete"><i class="fa fa-times"></i> 删除</a>
        <a class="btn btn-default btn-sm" id="product_formula-form-submit"><i class="fa fa-check"></i> 保存</a>
        <a data-toggle="layer-frame-close" class="btn btn-default btn-sm"><i class="fa fa-sign-out"></i> 退出</a>
    </div>
    <form class="product_formula_form" method="post" action="{{url('configSave')}}" id="product_formula" name="product_formula">
        <div id="grid_product_formula" style="width:100%;" class="ag-theme-balham"></div>
        <input type="hidden" name="id" value="{{$id}}">
    </form>
</div>

<style>
.form-panel-header {
    box-shadow: none;
}
.product_formula_form {
    margin-top: 50px;
}
.content-body {
    margin: 0;
}
</style>

<script>
(function($) {
    var id = '{{$id}}';
    var options = {
        title:'',
        master: 'product_formula',
        table: 'product_formula',
        heightTop: 0
    };
    options.columns = [
        {field:"id",hide:true},
        {hide:true,field:"product_id"},
        {hide:true,field:"material_id"},
        {cellClass:"text-center",headerName:"存货编码",width:120,field:"product_code"},
        {suppressNavigable: false, headerName:"产品名称",editable:true,width:220,cellEditorParams:{form_type:"dialog",title:"产品",type:"product",field:"product_name",url:"product/product/dialog",query:{form_id:"product_formula",id:"product_id",name:"product_name"}},cellEditor:"dialogCellEditor",field:"product_name"},
        {cellClass:"text-center",headerName:"规格型号",width:120,field:"product_spec"},
        {cellClass:"text-center",headerName:"计量单位",width:80,field:"product_unit"},
        {suppressNavigable: false, cellClass:"text-right",headerName:"用量",editable:true,width:80,field:"quantity"},
        {suppressNavigable: false, cellClass:"text-right",headerName:"出品率",editable:true,width:80,field:"ratio"},
    ];
    options.links = {
        product_id: {
            product_id:'id',
            product_code:"code",
            product_name:"name",
            product_spec:"spec",
            product_unit:"unit_id_name",
        }
    };
    var grid = gridForm("product_formula", options);
    grid.dataKey = 'product_id';
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = {id: id};

    gdoo.event.set('product_formula.product_id', {
        query(query) {
        },
        onSelect() {
            return true;
        }
    });
    
    // 读取数据
    grid.remoteData();

    $('#product_formula_delete').on('click', function() {
        var rows = grid.api.getSelectedRows();
        if (rows.length > 0) {
            grid.api.deleteRow({id: rows[0].id});
        }
    });

    ajaxSubmit('product_formula', function(res) {
        if (res.status) {
            toastrSuccess(res.data);
            location.reload();
        } else {
            toastrError(res.data);
        }
    });

    formGridList['product_formula'] = [grid];
    gdoo.forms[product_formula] = grid;

})(jQuery);
</script>