<template>

  <draggable chosenClass="af-chosen" ghostClass="af-ghost" :move="onMove" @change="onChange" class="af-draggable" :list="items" item-key="item_id" group="gdooComponent">
    <template #item="{element, index}">
        <div @click.stop="nodeShow(element)" :class="'af-item af-item-' + element.type + (activeItem.item_id == element.item_id ? ' af-item-active' : '')" :item-id="element.item_id" :item-table="element.table" :key="element.item_id" :style="'width:' + (element.table_id > 0 ? element.width : 'calc(' + (element.grid / 12 * 100) + '% - 5px)')">

            <div :class="'af-panel af-panel-' + element.type">
                <a @click.stop="nodeDelete(index)"><i class="remove fa fa-trash" title="删除"></i></a>
                <div v-if="element.type == 'component'">
                    {{ element.name }}
                </div>
                <div v-else class="af-item-content">
                    <label class="af-item-label" :style="(element.label_width ? 'width:'+ element.label_width : '')" v-show="element.hide_name == 0">{{ element.name }}</label>
                    <div class="af-item-control" :style="'margin-left:' + (element.hide_name == 0 ? element.label_width : '0px')">
                        <input class="form-control input-sm" v-model="element.value" :style="(element.field_width ? 'width:' + element.field_width : '')">
                    </div>
                </div>
            </div>

            <div :class="'af-component fa-group-' + element.type + (element.table == 1 ? ' af-table-component af-over-x' : '')" v-if="element.type == 'component'">
                <gdoo-form-designer :table="element.table == 1" :parent_id="element.item_id" :items="element.children" />
            </div>
        </div>
    </template>

  </draggable>

</template>
<script>
import draggable from 'vuedraggable';
import { watch, defineComponent, inject, reactive, provide, toRaw} from 'vue'
export default defineComponent({
    name: 'gdoo-form-designer',
    props: ['items', 'parent_id', 'table'],
    setup(props) {
        let activeItem = inject('activeItem');
        watch(activeItem, (oldItem, newItem) => {
            props.items.forEach(item => {
                if (item.item_id == newItem.item_id) {
                    for (var key in newItem) {
                        item[key] = newItem[key];
                    }
                }
            });
        })
        return {activeItem};
    },
    components: {
        'draggable': draggable,
    },
    methods: {
        onMove(item) {
            let me = this;
            let oldItem = item.draggedContext.element;
            let newItem = item.relatedContext.element;

            // 获取容器dom
            let node = item.to.offsetParent;

            // 子表节点不能拖出子表容器
            if (oldItem.table_id > 0) {
                // 不能把子表节点拖入其他容器
                if (newItem == undefined) {
                    return false;
                }
                if (oldItem.table_id == newItem.table_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                // 新容器为空
                if (newItem == undefined) {
                    var item_table = node.getAttribute('item-table');
                    // 不允许把普通节点拖入子表
                    if (item_table == 1) {
                        return false;
                    }
                    return true;
                } else {
                    // 不允许普通节点拖入子表
                    if (newItem.table_id) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        },
        onChange(item) {
            let me = this;
            if (item.added) {
                var row = item.added.element;

                row.parent_id = me.parent_id;

                // 已经设置了id
                if (row.item_id > 0) {
                    return;
                }

                row.item_id = getItemMaxId();

                if (row.type == 'component') {
                } else {
                    row.field = getFieldMaxId();
                    if (me.table) {
                        row.table_id = row.parent_id;
                        row.width = '200px';
                        delete row.grid;
                    } else {
                        row.table_id = 0;
                    }
                }

                for (var key in me.activeItem) {
                    delete me.activeItem[key];
                }
                for (var key in row) {
                    me.activeItem[key] = row[key];
                }
            }
        },
        nodeShow(item) {
            for (var key in this.activeItem) {
                delete this.activeItem[key];
            }
            for (var key in item) {
                if (key == 'children') {
                    continue;
                }
                this.activeItem[key] = item[key];
            }
        },
        nodeDelete(index) {
            this.items.splice(index, 1);
        }
    }
});
</script>
<style>
.af-draggable {
    min-height: 40px;
    padding: 0;
    float: left;
    width: 100%;
}

.af-component {
    padding: 5px;
    padding-bottom: 0;
    padding-right: 0;
}

.af-item-component {
    border: 1px dashed #ccc;
    margin-bottom: 5px;
    cursor: move;
    float: left;
    position: relative;
}

.af-item {
    float: left;
    margin-right: 5px;
}

.af-panel-component {
    color: #aaa;
    padding: 5px;
    padding-bottom: 0;
}

.af-item:not(.af-item-component) {
    padding-bottom: 5px;
}

.af-panel:not(.af-panel-component) {
    position: relative;
    padding: 5px;
    padding-right: 5px;
    background-color: #eee;
    border: 1px solid transparent;
    cursor: move;
}

.af-item-component.af-item-active {
    border: 1px dashed #409eff;
}
.af-item-active:not(.af-item-component) .af-panel {
    background: #f6f7ff;
    border: 1px dashed #409eff;
    color: #409eff;
}

.af-item-label {
    width: 150px;
    text-align: right;
    float: left;
    line-height: 28px;
    vertical-align: middle;
    padding: 0 10px 0 0;
    font-weight: normal;
}

.af-item-control {
    margin-left: 150px;
}

.af-panel .fa {
    display: none;
    text-align: center;
    border: 1px solid #409eff;
    background: #fff;
    z-index: 999;
}

.af-item-active > .af-panel .fa {
    display: inline-block;
    position: absolute;
    top: -5px;
    right: 5px;
    color: #409eff;
    padding: 3px 5px;
}
.af-item-active .fa:hover {
    background: #409eff;
    color: #fff;
}

.af-left-group-item.af-ghost,
.af-panel-component.af-ghost .af-panel {
    border: 1px dashed #409eff;
    color: #409eff;
}

.af-left-group-item.af-ghost {
    float: left;
    margin: 0;
    margin-bottom: 5px;
    width:calc(100% - 5px);
}

.af-table-component {
    overflow: hidden;
    overflow-x: auto;
    white-space: nowrap;
}

.af-table-component .af-item { 
    float: none;
    display: inline-block;
}

.af-table-component .af-item-label {
    width: auto;
    float: none;
    text-align: left;
    line-height: inherit;
}

.af-table-component .af-item-control {
    margin-left: 0;
}

.af-table-component .af-ghost {
    width: 200px;
}

</style>