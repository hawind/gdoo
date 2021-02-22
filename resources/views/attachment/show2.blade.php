<div class="uploadify-queue">
    <div id="fileQueue" class="uploadify-queue">
        @if($attachments['main'])
            @foreach($attachments['main'] as $n => $file)
            <div id="file_queue_{{$n}}" class="uploadify-queue-item">
                <span class="file-name"><span class="icon icon-paperclip"></span> <a href="javascript:uploader.file('file_queue_{{$n}}');">{{$file['name']}}</a></span>
                <span class="file-size">({{human_filesize($file['size'])}})</span>
                <span class="cancel"><a class="option" href="javascript:uploader.cancel('file_queue_{{$n}}');"><i class="fa fa-fw fa-remove"></i></a></span>
                <input type="hidden" class="id" name="{{$field}}[]" value="{{$file['id']}}">
            </div>
            <div class="clear"></div>
            @endforeach
        @endif
    </div>
</div>
