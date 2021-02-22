<div class="wrapper">
    <div class="input-group">
        <input type="text" class="form-control" id="import_file_text" disabled>
        <input style="display:none;" type="file" id="import_file">
        <span class="input-group-btn">
            <button class="btn btn-default" onclick="importFile();" type="button"><i class="fa fa-cloud-upload"></i> 选择文件</button>
        </span>
    </div>
    <div class="m-t-xs">{{$tips}}</div>
</div>

<script>
function importFile() {
    $('#import_file').click();
}

$('#import_file').on('change', function() {
    var filename = $(this).val();
    var pos = filename.lastIndexOf("\\");
    $('#import_file_text').val(filename.substring(pos + 1));
});
</script>