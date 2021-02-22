<style>
.form-controller {
    padding: 0;
}
input[type=file] {
    display: none;
}
</style>
<form class="form-horizontal form-controller" method="post" id="inspect_report_create" name="inspect_report_create">
    <div class="form-group">
        <div class="col-xs-2 control-label"><span class="red">*</span> 名称</div>
        <div class="col-xs-10 control-text">
            <input type="text" class="form-control input-sm" autocomplete="off" name="name" id="inspect_report_name" />
        </div>
    </div> 
    <div class="form-group">
        <div class="col-xs-2 control-label"><span class="red">*</span> 文件</div>
        <div class="col-xs-10 control-text">
            <div class="input-group">
                <input type="text" class="form-control input-sm" id="file_text" disabled="disabled">
                <input type="file" name="file" class="file" id="inspect_report_file" />
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-default" id="file_upload"><i class="fa fa-folder-open"></i>选择文件</a>
                </span>
            </div>
        </div>

    </div>
</form>
<script>
$(function($) {
    $('#inspect_report_file').off('change').on('change', function() {
        var filename = getFileName(this.value);
        var name = filename.substring(0, filename.indexOf('.'));
        $('#file_text').val(filename);
        $('#inspect_report_name').val(name);
    });
    $('#file_upload').on('click', function() {
        $('#inspect_report_file').click();
    });
});

function getFileName(path) {
    var paths = path.split("\\");
    return paths[paths.length-1];
}
</script>