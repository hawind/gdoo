<style>
.modal-body .form-controller {
    padding: 0;
}
.modal-body input[type=file] {
    display: none;
}
</style>
<form class="form-horizontal form-controller" method="post" id="import_excel" name="import_excel">
    <div class="form-group">
        <div class="col-xs-2 control-label"><span class="red">*</span> 文件</div>
        <div class="col-xs-10 control-text">
            <div class="input-group">
                <input type="text" class="form-control input-sm" id="file_text" disabled="disabled">
                <input type="file" name="file" class="file" id="import_file" />
                <span class="input-group-btn">
                    <a class="btn btn-sm btn-default" id="file_upload"><i class="fa fa-folder-open"></i>选择文件</a>
                </span>
            </div>
        </div>

    </div>
</form>
<script>
$(function($) {
    $('#import_file').off('change').on('change', function() {
        var filename = getFileName(this.value);
        var name = filename.substring(0, filename.indexOf('.'));
        $('#file_text').val(filename);
    });
    $('#file_upload').on('click', function() {
        $('#import_file').click();
    });
});

function getFileName(path) {
    var paths = path.split("\\");
    return paths[paths.length-1];
}
</script>