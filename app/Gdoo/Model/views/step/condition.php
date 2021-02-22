<form method="post" action="<?php echo url(); ?>" id="conditionform" name="conditionform">

<?php
if (sizeof($steps)):
foreach ($steps as $step):
?>
<div class="panel">
    <table id="Condition<?php echo $step->id; ?>Ctrl" class="table table-form table-condensed table-hover">
        <thead>
        <tr>
            <th colspan="7" align="left">转入: <?php echo $step->name; ?></th>
        </tr>
        <tr>
            <th align="center">左括号</th>
            <th align="center">字段</th>
            <th align="center">条件</th>
            <th>值</th>
            <th>函数</th>
            <th align="center">右括号</th>
            <th align="center">逻辑</th>
            <th align="center">
                <a @click="create();" class="btn btn-xs btn-default"><i class="icon icon-plus"></i></a>
            </th>
        </tr>
        </thead>
        <tbody>
            <tr v-for="(item,index) in items">
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][l]'" v-model="item.l" class="input-sm form-control">
                        <option value=""></option>
                        <option value="(">(</option>
                    </select>
                </td>
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][f]'" v-model="item.f" class="input-sm form-control">
                        <option value=""></option>
                        <?php 
                            foreach ($columns as $table => $column):
                            foreach ($column['data'] as $field):
                        ?>
                        <?php if ($field['auto'] == 1): ?>
                            <option value="<?php echo $field['field']; ?>"><?php echo $field['name']; ?></option>
                        <?php else: ?>
                            <option value="<?php echo $table; ?>.<?php echo $field['field']; ?>">
                                <?php if ($column['master'] == 0): ?>[<?php echo $column['name']; ?>]<?php endif; ?>
                                <?php echo $field['name']; ?>
                            </option>
                        <?php endif; ?>
                        <?php 
                            endforeach;
                            endforeach;
                        ?>
                    </select>
                </td>
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][c]'" v-model="item.c" class="input-sm form-control">
                        <option value=""></option>
                        <option value="==">等于</option>
                        <option value="<>">不等于</option>
                        <option value=">">大于</option>
                        <option value="<">小于</option>
                        <option value=">=">大于等于</option>
                        <option value="<=">小于等于</option>
                        <!--
                        <option value="like">包含</option>
                        <option value="not like">不包含</option>
                        -->
                    </select>
                </td>
                <td>
                    <input :name="'condition[<?php echo $step->id; ?>]['+index+'][v]'" v-model="item.v" class="input-sm form-control">
                </td>
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][t]'" v-model="item.t" class="input-sm form-control">
                        <option value=""></option>
                        <option value="sum">SUM(合计)</option>
                        <option value="count">COUNT(行数)</option>
                    </select>
                </td>
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][r]'" v-model="item.r" class="input-sm form-control">
                        <option value=""></option>
                        <option value=")">)</option>
                    </select>
                </td>
                <td>
                    <select :name="'condition[<?php echo $step->id; ?>]['+index+'][i]'" v-model="item.i" class="input-sm form-control">
                        <option value=""></option>
                        <option value="and">and</option>
                        <option value="or">or</option>
                    </select>
                </td>
                <td align="center">
                    <a @click="remove(index);" data-condition="delete" class="btn btn-xs btn-default"><i class="icon icon-trash"></i></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php
endforeach;
endif;
?>

<script type="text/javascript">
<?php foreach ($steps as $step): ?>
var conditionCtrl = {
    data() {
        return {
            items: <?php echo json_encode((array)$condition[$step->id]); ?>
        }
    },
    methods: {
        create: function() {
            var item = {};
            this.items.push(item);
        },
        remove: function(index) {
            this.items.splice(index, 1);
        }
    }
};
Vue.createApp(conditionCtrl).mount('#Condition<?php echo $step->id; ?>Ctrl');

<?php endforeach; ?>
$('#myform').submit(function () {
    var url = $(this).attr('action');
    var data = $(this).serialize();
    $.post(url, data, function (res) {
        if (res.status) {
            toastrSuccess(res.data);
        } else {
            toastrError(res.data);
        }
    }, 'json');
    return false;
});
</script>

<input type="hidden" name="id" value="<?php echo $row->id; ?>">
<input type="hidden" name="model_id" value="<?php echo $model->id; ?>">

</form>

</div>