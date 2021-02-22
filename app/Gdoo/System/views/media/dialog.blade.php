<style type="text/css">
#media-navigator {
    height: 480px;
    min-height: 480px;
    background: #f4f5f9;
    border-right: 1px solid #e7e7eb;
    width: 140px;
    float: left;
}
#media-navigator a, #media-navigator a:hover {
    text-decoration: none;
}
#media-navigator ul {
    list-style: none;
    padding: 0;
}
#media-navigator ul li {
    line-height: 38px;
}
#media-navigator ul li .node {
    border-bottom: 1px solid #eee;
    display: block;
    padding: 0 10px;
}
#media-navigator ul li.active,
#media-navigator ul li:hover {
    background-color: #fff;
}

#media-content {
    height: 480px;
    min-height: 480px;
    margin-left: 140px;
    overflow: hidden;
    padding-right: 1px;
}
.media-tool {
    padding: 10px;
    border-bottom: 1px solid #e7e7eb;
}

.folder-tool {
    vertical-align: initial;
    display: none;
    margin-right: 10px;
}

.folder-tool .fa {
    color: #999;
}

#media-navigator ul li:hover .folder-tool {
    display: block;
}

#media-navigator ul li .folder-tool a {
    padding: 0 2px;
}

#media-navigator ul li .folder-tool a:hover .fa {
    color: #0e90d2;
}

.files {
    list-style: none;
    margin: 0;
    padding: 0;
    padding-top: 10px;
    overflow: auto;
    height: 100%;
}

.files > li {
    float: left;
    width: 144px;
    border: 1px solid #eee;
    margin-bottom: 10px;
    margin-left: 10px;
    position: relative;
}

.files > li > .file-select {
    position: absolute;
    top: -4px;
    left: -1px;
}

.file-icon.has-img {
    padding: 0;
}
.file-icon {
    text-align: center;
    font-size: 65px;
    color: #666;
    display: block;
    height: 100px;
}
.file-icon.has-img > img {
    max-width: 100%;
    height: auto;
    max-height: 92px;
}
.file-info {
    text-align: center;
    padding: 10px;
    background: #f4f4f4;
}

.file-name {
    font-weight: bold;
    color: #666;
    display: block;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.file-size {
    color: #999;
    font-size: 12px;
    display: block;
}

</style>

@verbatim
<div id="vue-app">
    <div class="media-controller">
        <div class="media-tool">
            <div class="pull-right">
                <button class="btn btn-success btn-sm" onclick="document.getElementById('upload').click();"><i class="fa fa-cloud-upload"></i> 上传文件</button>
                <input type="file" id="upload" ref="upload" @change="onUpload" accept="image/*" multiple="multiple" style="display:none;">
                <!--
                <button class="btn btn-info btn-sm"><i class="fa fa-arrows"></i> 移动文件</button>
                <button class="btn btn-danger btn-sm"><i class="fa fa-times"></i> 删除文件</button>
                -->
            </div>
            <a @click="folder" class="btn btn-sm btn-default"><i class="fa fa-plus"></i> 添加文件夹</a>
            <div class="clearfix"></div>
        </div>
        <div id="media-navigator">
            <ul>
                <li v-bind:class="{active:folderId == 0}">
                    <a @click="onFolder(0)" class="node">
                        <i class="fa fa-folder-o"></i> 全部
                    </a>
                </li>
                <li v-bind:class="{active:folderId == item.id}" v-for="(item, index) in folders" :key="item.id">
                    <span class="pull-right folder-tool">
                        <!--
                        <a class="hinted" title="修改"><i class="fa fa-pencil"></i></a>
                        -->
                        <a @click="onDelete(index, 1)" class="hinted" title="删除"><i class="fa fa-times"></i></a>
                    </span>
                    <a @click="onFolder(item.id)" class="node"><i class="fa fa-folder-o"></i> {{item.name}}</a>
                </li>
            </ul>
        </div>
        <div id="media-content">
            <ul class="files">
                <li v-for="(item, index) in files" :key="item.id">
                    <span class="file-select">
                        <label class="checkbox-inline i-checks">
                            <input type="checkbox" name="files" v-model="item.checked"><i></i>
                        </label>
                    </span>
                    <span class="file-icon has-img"><img :src="img(item.thumb)"></span>
                    <div class="file-info">
                        <a class="file-name" :title="item.name">
                            {{item.name}}
                        </a>
                        <span class="file-size">
                            {{filesize(item.size)}}
                            &nbsp;
                            <div class="btn-group btn-group-xs pull-right">
                                <button type="button" class="btn btn-default btn-xs" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a class="file-rename">改名 &amp; 移动</a></li>
                                    <li><a @click="onDelete(index, 0)" class="file-delete">删除</a></li>
                                    <li class="divider"></li>
                                    <li><a :href="download(item.id)">下载</a></li>
                                </ul>
                            </div>
                        </span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
@endverbatim

<script>
var vueApp = Vue.createApp({
    data: function() {
        return {
            folderId: 0,
            folders: [],
            files: [],
        }
    },
    created: function() {
        let me = this;
        me.getMedia(1, 0);
        me.getMedia(0, 0);
    },
    methods: {
        filesize: function(val) {
            var size = '';
            val = parseInt(val);
            if (val < 0.1 * 1024) {
                // 如果小于0.1KB转化成B
                size = val.toFixed(2) + ' B';
            } else if(val < 0.1 * 1024 * 1024) {
                // 如果小于0.1MB转化成KB
                size = (val / 1024).toFixed(2) + ' KB';
            } else if(val < 0.1 * 1024 * 1024 * 1024) {
                // 如果小于0.1GB转化成MB
                size = (val / (1024 * 1024)).toFixed(2) + ' MB';
            } else {
                // 其他转化成GB
                size = (val / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
            }
            return size;
        },
        img: function(path) {
            return app.url('uploads/' + path);
        },
        download: function(id) {
            return app.url('system/media/download', {id: id});
        },
        folder: function() {
            let me = this;
            $.dialog({
                title: '文件夹管理',
                url: app.url('system/media/folder'),
                dialogClass: 'modal-md',
                buttons: [{
                    text: '提交',
                    'class': 'btn-success',
                    click: function() {
                        let modal = this;
                        let form = $('#myfolder').serialize();
                        axios.post(app.url('system/media/folder'), form)
                        .then(function(response) {
                            me.getMedia(1, 0);
                            $(modal).dialog('close');
                        }).catch(function(error) {
                            console.log(error);
                        });
                    }
                }]
            });
        },
        getMedia: function(folder, folderId) {
            let me = this;
            axios.post(app.url('system/media/dialog'), {
                folder: folder,
                folder_id: folderId
            }).then(function(response) {
                let res = response.data;
                if (folder == 1) {
                    me.folders = res.data;
                } else {
                    me.files = res.data;
                }
            }).catch(function(error) {
                console.log(error);
            });
        },
        onDelete: function(index, folder) {
            let me = this;
            let id = 0;
            if (folder) {
                id = me.folders[index].id;
            } else {
                id = me.files[index].id;
            }
            $.messager.confirm('操作警告', '确定要删除吗', function(btn) {
                if (btn == true) {
                    axios.post(app.url('system/media/delete'), {
                        id: [id],
                    }).then(function(response) {
                        if (folder) {
                            me.folders.splice(index, 1);
                        } else {
                            me.files.splice(index, 1);
                        }
                    }).catch(function(error) {
                        console.log(error);
                    });
                }
            });
        },
        onFolder: function(id) {
            let me = this;
            me.folderId = id;
            me.getMedia(0, id);
        },
        onUpload: function(e) {
            let me = this;
            let formData = new FormData();
            formData.set('folder_id', me.folderId);
            let files = me.$refs.upload.files;
            for(var i = 0; i < files.length; i++) {
                let file = files[i];
                formData.set('Filedata', file);
                axios.post(app.url('system/media/create'), formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                }).then(function(response) {
                    let res = response.data;
                    me.files.unshift(res.data);
                    console.log(res.data);
                })
                .catch(function(error) {
                    console.log(error);
                });
            }
        }
    }
});
var vm = vueApp.mount('#vue-app');

function saveMedia(params) {
    var files =[];
    for (var i = 0; i < vm.files.length; i++) {
        let file = vm.files[i];
        if (file.checked) {
            files.push(file);
        }
    }

    if (files.length > 0) {
        var media = $('#' + params['id'] + '-media');
        media.empty();
        
        if (params['multi'] == 0) {
            let file = files[0];
            var name = params['name'];
            media.append('<div class="media-item"><input type="hidden" value="' + file.path + '" name="' + name + '" /><img class="img-responsive img-thumbnail" src="'+ app.url('uploads/' + file.path) +'" /><a class="close" title="删除这张图片" data-toggle="media-delete">×</a></div>');
        } else {
            var name = params['name'] + '[]';
            for (var i = 0; i < files.length; i++) {
                let file = files[i];
                media.append('<div class="media-item"><input type="hidden" value="' + file.path + '" name="' + name + '" /><img class="img-responsive img-thumbnail" src="'+ app.url('uploads/' + file.path) +'" /><a class="close" title="删除这张图片" data-toggle="media-delete">×</a></div>');
            }
        }
    }
}
</script>