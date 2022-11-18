<html>
    <script src="jquery.min.js"></script>
    <script>
        $(function(){
			$('#fileUpload').on('change',function(){
				var fileExtension = $(this).val().split('.').pop();
				$(".select option[value='"+fileExtension+"']").remove();
				//console.log('fileExtension==>'+fileExtension);
			});
		});
    </script>
    <body>
        <form action="" method="post" enctype="multipart/form-data">
            <label>File Upload</label>
            <input type="file" name="fileUpload" id="fileUpload" />
            <label>Convert To</label>
            <select class="select" name="convertTo" id="convertTo">
                <option value="">--- Select ---</option>
                <option value="gif">GIF</option>
                <option value="png">PNG</option>
                <option value="jpg">JPG</option>
            </select>
            <input type="submit" name="btnSubmit" id="btnSubmit" value="Submit" />
        </form>
    </body>
</html>
<?php 
    if(isset($_POST) && !empty($_POST) && isset($_FILES['fileUpload']))
    {
        $uploadImg = 'uploadedImage/original/';
        $uploadCompreesedImg = 'uploadedImage/compreesed/';
        $uploadReseizeImg = 'uploadedImage/resize/';
        $uploadCropImg = 'uploadedImage/crop/';
        $ext = pathinfo($_FILES['fileUpload']['name'], PATHINFO_EXTENSION);
        $baseName = basename($_FILES['fileUpload']['name'],$ext);
        $only_name = basename($_FILES['fileUpload']['name'], '.'.$ext);
	    $targetFileName = $only_name.time().'.'.$ext;
	    $target_file = $uploadImg.$targetFileName;
        $compreesedImg = $only_name.time().'.'.$_POST['convertTo'];
        $cropImg = $only_name.time().'.'.$_POST['convertTo'];
        $convertFile = $uploadCompreesedImg.$compreesedImg;
        move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file);
        require "img-compressor.php";
        $_IC->pack($target_file, $convertFile)? "OK" : $_IC->error;

        require "SimpleImage.php";
        $resizeTarget = $uploadReseizeImg.$compreesedImg;
        $image = new SimpleImage();
        $image->load($convertFile);
        $image->resize('1500', '1500');
        $image->save($resizeTarget);

        $image_info = getimagesize($resizeTarget);
        $croppedTarget = $uploadCropImg.$cropImg;
        $imagemime = $image_info['mime'];
        $x = '50';
        $y = '50';
        $width = '500';
        $height = '500';
        if($imagemime=='image/jpeg')
        {
            $im = imagecreatefromjpeg($resizeTarget);
            //$size = min(imagesx($im), imagesy($im));
            $im2 = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
            if ($im2 !== FALSE) {
                imagejpeg($im2, $croppedTarget);
                //imagedestroy($im2);
            }
        }else if($imagemime=='image/png')
        {
            
            $im = imagecreatefrompng($resizeTarget);
            //$size = min(imagesx($im), imagesy($im));
            $im2 = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
            if ($im2 !== FALSE) {
                imagepng($im2, $croppedTarget);
                //imagedestroy($im2);
            }
            //imagedestroy($im);
        }else{
            $im = imagecreatefromgif($resizeTarget);
            //$size = min(imagesx($im), imagesy($im));
            $im2 = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
            if ($im2 !== FALSE) {
                imagegif($im2, $croppedTarget);
                //imagedestroy($im2);
            }
        }
        /* $im = imagecreatefrompng('example.png');
        $size = min(imagesx($im), imagesy($im));
        $im2 = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => $size, 'height' => $size]);
        if ($im2 !== FALSE) {
            imagepng($im2, 'example-cropped.png');
            imagedestroy($im2);
        }
        imagedestroy($im); */
    }
?>