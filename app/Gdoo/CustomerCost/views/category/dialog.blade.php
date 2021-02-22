<div class="wrapper">
    <table id="position-dialog">
        <thead>
            <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="text" data-align="left">名称</th>
                <th data-field="id" data-width="60" data-align="center">ID</th>
            </tr>
        </thead>
    </table>
</div>

<script>
(function($) {
    var $table = $('#position-dialog');
    var params = {{json_encode($get)}};
    var sid = params.prefix == 1 ? 'sid' : 'id';

    var selected = {};

    function getSelected()
    {
        selected = {};
        var id = $('#'+params.id).val();
        var text = $('#'+params.id+'_text').val();

        if(id == '') {
            return;
        }

        id = id.split(',');
        text = text.split(',');
        for (var i = 0; i < id.length; i++) {
            selected[id[i]] = text[i];
        }
    }

    function setSelected() {
        var id = [], text = [];
        $.each(selected, function(k, v) {
            id.push(k);
            text.push(v);
        });
        $('#'+params.id).val(id.join(','));
        $('#'+params.id+'_text').val(text.join(','));
    }

    function setRow(row)
    {
        if (params.multi == 0) {
            selected = {};
        }
        selected[row[sid]] = row.text;
        setSelected();
    }

    function unsetRow(row) {
        $.each(selected, function(id) {
            if (id == row[sid]) {
                delete selected[id];
            }
        });
        setSelected();
    }

    $table.bootstrapTable({
        iconSize:'sm',
        singleSelect: params.multi == 1 ? 0 : 1,
        showColumns: false,
        clickToSelect: true,
        method: 'post',
        url: '{{url()}}',
        height: 350,
        onLoadSuccess: function(res) {

            getSelected();

            $.each(selected, function(j) {
                for (var i = 0; i < res.data.length; i++) {
                    if (res.data[i][sid] == j) {
                        $table.bootstrapTable('check', i);
                   }
                }
            });
        },
        onDblClickRow: function(row) {
            setRow(row);
            if (sid == 'sid') {
                $('#modal-dialog-search').dialog('close');
            } else {
                $('#modal-dialog-user').dialog('close');
            }
        },
        onCheck: function(row) {
            setRow(row);
        },
        onUncheck: function(row) {
            unsetRow(row);
        },
        onCheckAll: function(rows) {
            for (var i = 0; i < rows.length; i++) {
                setRow(rows[i]);
            }
        },
        onUncheckAll: function(rows) {
            for (var i = 0; i < rows.length; i++) {
                unsetRow(rows[i]);
            }
        }
    });
})(jQuery);

</script>