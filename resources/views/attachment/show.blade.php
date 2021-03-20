<div class="uploadify-queue">
    <div id="fileQueue" class="uploadify-queue">
        @if($attachment['rows'])
            @foreach($attachment['rows'] as $n => $file)
            <div id="file_queue_{{$n}}" class="uploadify-queue-item">
                <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_queue_{{$n}}');">{{$file['name']}}</a></span>
                <span class="file-size">({{human_filesize($file['size'])}})</span>

                @if(in_array($file['type'], ['pdf']))
                    <a href="{{$upload_url}}/{{$file['path']}}" class="btn btn-xs btn-default" target="_blank">[预览]</a>
                @endif

                @if(in_array($file['type'], ['jpg','png','gif','bmp']))
                    <img data-original="{{$upload_url}}/{{$file['path']}}" /><a data-toggle="image-show" class="option">[预览]</a>
                @endif

                <div class="clear"></div>
            </div>
            @endforeach
        @endif
    </div>
</div>
<script>
(function($) {
    var galley_id = "fileQueue";
    var galley = document.getElementById(galley_id);
    var viewer = new Viewer(galley, {
        navbar: false,
        url: "data-original",
    });
    $("#" + galley_id).on("click", '[data-toggle="image-show"]', function() {
        $(this).prev().click();
    });
})(jQuery);
</script>