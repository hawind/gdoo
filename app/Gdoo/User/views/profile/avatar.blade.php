<div class="wrapper-sm">
    <div class="avatar-form">
        <div class="col-sm-9">
            <div class="image-box">
                <img id="image" class="hide">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="preview-box" style="width:150px;height:150px;">
                <div class="preview" style="width:148px;height:148px;"></div>
            </div>
            <div class="preview-box" style="width:98px;height:98px;">
                <div class="preview" style="width:96px;height:96px;"></div>
            </div>
            <div class="preview-box" style="width:50px;height:50px;">
                <div class="preview" style="width:48px;height:48px;"></div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="btn-avatar">
            <input class="hide" id="avatarInput" name="image" type="file">
            <a class="btn btn-info" onclick="$('#avatarInput').click();"><i class="fa fa-cloud-upload"></i> 上传文件</a>
            <a class="btn btn-success" id="avatarCrop"><i class="fa fa-crop"></i> 裁剪</a>
        </div>
    </div>
</div>
<style>
.avatar-form {
    position: relative;
}
.btn-avatar {
    position:absolute;
    bottom: 0;
    right: 46px;
}
.preview-box {
    border: 1px solid #eee;
    padding: 0;
    margin-bottom: 10px;
}
.preview {
    display: block;
    overflow: hidden;
}
.image-box {
    border: 1px solid #eee;
    height: 420px;
    background-image: url(/assets/images/wf_canvas_bg.png);
    background-position: -1px -1px;
}
#image {
    max-width: 100%;
    height: 420px;
    border: 1px solid #eee;
    display: block;
}
.col-sm-9 {
    padding-left: 5px;
}
</style>

<link href="{{$asset_url}}/vendor/cropper/cropper.min.css" rel="stylesheet" type="text/css" />
<script src="{{$asset_url}}/vendor/cropper/cropper.min.js" type="text/javascript"></script>
<script>
$(function () {
    var options = {
        aspectRatio: 1,
        autoCropArea: 0.5,
        preview: '.preview',
    }
    var $image = $('#image').cropper(options);

    var $inputImage = $('#avatarInput');

    var uploadedImageURL = null;

    if (URL) {
        $inputImage.on('change', function () {
            var files = this.files;
            var file;

            if (!$image.data('cropper')) {
                return;
            }

            if (files && files.length) {
                file = files[0];
                if (/^image\/\w+$/.test(file.type)) {
                    uploadedImageName = file.name;
                    uploadedImageType = file.type;

                    if (uploadedImageURL) {
                    URL.revokeObjectURL(uploadedImageURL);
                    }
                    uploadedImageURL = URL.createObjectURL(file);
                    $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                    $inputImage.val('');
                } else {
                    window.alert('请选择图片文件');
                }
            }
        });
    } else {
        $inputImage.prop('disabled', true).parent().addClass('disabled');
    }

    $('#avatarCrop').on('click', function() {
        $image.cropper('getCroppedCanvas', {width: 128, height: 128}).toBlob(function (blob) {
            var formData = new FormData();
            formData.append('image', blob);
            $.ajax('{{url()}}', {
                method: "post",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    $('#user-avatar').attr('src', app.url('uploads/avatar/' + res.data) + '?s=' + Math.random());
                    toastrSuccess('头像上传成功。');
                    $('#modal-avatar-dialog').dialog('close');
                },
                error: function () {
                    toastrError('头像上传失败。');
                }
            });
        });
    });
});
</script>