<?php
session_start();
$root_path = realpath( dirname( __FILE__ ) ) . '/';
include_once( $root_path . 'lib/Images.php' );
include_once( $root_path . '../libs/krumo/class.krumo.php');

$paso1="CR_form1.php";

setlocale(LC_ALL, 'es_ES');
$todayFolder=strftime("%d%B%y");

if(!isset($_SESSION['workfolder_webdir'])){
	$message = "Necesitas crear carpeta para xml e imágenes <a href='{$paso1}'>Paso 1</a>";
}
else{
//$xmlDestino=$_SESSION['workfolder_webdir']."Destacados.xml";
$folder=$_SESSION['workfolder_webdir'];
}

if(!isset($_SESSION['destacados_img_folder']) || !is_dir($_SESSION['destacados_img_folder'])){
$message = "Creando carpeta<br>";
$destacados_img_folder=$_SESSION['workfolder_webdir']."img/destacados/".$todayFolder."/";
$_SESSION['destacados_img_folder']=$destacados_img_folder;
    //krumo($folder);
    if (!createPath( $destacados_img_folder )){
	    $message = "Error making folder";
    }
	$message .= "Carpeta {$destacados_img_folder} creada";
	$message .= "<br> {$destacados_img_folder} es la carpeta activa";

}
else{
	
}



//for TESTING, we'll create a user_id session
$_SESSION['user_id']=1;
if (isset($_SESSION['user_id'])):?>

	<?php
    if($_SERVER['REQUEST_METHOD'] == "POST") {
    
    $image = new Images();
    
    //$folder="archivo/".$_POST['folder']."/web/img/destacados/prepor_";
    $folder= $_SESSION['destacados_img_folder'];
       
		//Upload limit size in megabytes
		$upload_size_limit = 10;
		//this is the image destination path
		$path = $folder;
		//temp image storage directory
		$temp_path = "temp/";
		//list all accepted image formats here (extensions)
		$valid_formats = array("jpg", "jpeg", "png","gif", "bmp");
		//grab the submitted image name and size
		$uploaded_img_name = $_FILES['photoimg']['name'];
		
		$final_image_name=pathinfo($_FILES['photoimg']['name'], PATHINFO_FILENAME );
		//size is measured in bytes
		$uploaded_img_size = $_FILES['photoimg']['size'];
		//get image extension
		$ext = pathinfo($uploaded_img_name, PATHINFO_EXTENSION);
		//make sure extension is acceptable
		// krumo(pathinfo($_FILES['photoimg']['name'], PATHINFO_FILENAME ));
		
		if(in_array(strtolower($ext),$valid_formats)) {
			//make sure image is not too big (1048576 bytes = 1 megabyte)
			if($uploaded_img_size<(1048576*$upload_size_limit)) {
				//store uploaded image temporarily in "temp" directory while resizing is performed
				$tmp_img_name = "temp-".time().".".$ext;
				//create the permanent filename with extension
				$final_img_name = $final_image_name.".jpg";
				krumo($final_img_name);
				//set image thumbnail destination
				$store_filename = $root_path.$path.$final_img_name;
				krumo($store_filename);
				//put uploaded image in var
				$uploaded_img = $_FILES['photoimg']['tmp_name'];
				//move the image into the temp directory while we work with it
				$tempLocationFile=$temp_path.$tmp_img_name;
				if($file=move_uploaded_file($uploaded_img, $tempLocationFile)) {
				// 577 × 299
				// krumo($tempLocationFile);
					    include_once("lib/resize-class.php");
                        $resizeObj = new resize($tempLocationFile);
						$resizeObj -> resizeImage(577, 299, "crop");
						$resizeObj -> saveImage($tempLocationFile, 100);
						
						$bg = imagecreatefromjpeg('overlays/destacados_overlay.jpg');
						 // $img = imagecreatefromjpeg($tempLocationFile);
						 $img=$resizeObj->openImage($tempLocationFile);
						
						 $fileSRC= getRelativePath($store_filename, __FILE__ );
						// imagecopymerge($bg, $img, 0, 0, 0, 0, imagesx($bg), imagesy($bg), 100);

						 imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
						 imagejpeg($bg, $store_filename, 100);

						$message= "{$store_filename} <img src='{$fileSRC}'/>";
						
						// $bg = imagecreatefromjpeg('background.jpg');
												
					//delete original user uploaded image from temp directory.
				      unlink($tempLocationFile);
				}
				else {
					//unable to write file to temporary directory - check folder permissions
					$message= "Error! Please try again (temp move).";
				}
			}
			else {
				$message= "Sorry, your image is too large. Maximum size is 10mb. Please resize it."; 
			}
		}
		else {
			$message=  "Invalid file format. Images must be in jpg, png, gif, or bmp format."; 
		}
    }
    
    function translatePathToRelIMG($imgpath){
    // krumo($imgpath);
	 $newPath =  explode("/web/",$imgpath)[1];
	 return $newPath;
    }
    // en Images.pgp
    $images=readImagesFolder();
    //krumo($images);
    // pintamos las imágenes disponibles
    $images=findFiles($_SESSION['destacados_img_folder'], $ext=array("png","gif","jpg"));
    //krumo($images);
		$availableImgs="";
		foreach($images as $path){
		$relPath=translatePathToRelIMG($path);
		
			$availableImgs.= "<div style='width:200px;display: inline-block;margin:0 10px'>";
			$availableImgs.="<a class='destthumb' href='#imgModal' data-toggle='modal' data-relimg-url='{$relPath}' data-img-url='{$path}'><img  width='200' height='auto' src='{$path}' /></a>"; 
			$availableImgs.= "<div style='word-break:break-all;font-size:11px'>{$path}</div></div>";
		}
    
    ?>
    
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Upload Image</title>
		
		<?php include("header.php") ?>
        
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $("#uploading").hide();
            $("#submit-img").change(function() {
                $("#submit-img").hide();
                $("#uploading").show(100);
                $("#imageform").submit();
            });
            
            $('.destthumb').click(function (e) {
				$('#imgModal img').attr('src', $(this).attr('data-img-url'));
				$('#imgModal .modal-title').text($(this).attr('data-relimg-url'));
			});
			
        });
        
        
        function centerModal() {
			$(this).css('display', 'block');
			var $dialog = $(this).find(".modal-dialog");
			var offset = ($(window).height() - $dialog.height()) / 2;
			// Center modal vertically in window
			$dialog.css("margin-top", offset);
			}

			$('.modal').on('show.bs.modal', centerModal);
			$(window).on("resize", function () {
			$('.modal:visible').each(centerModal);
				});

        </script>
        <style>
	        
        </style>
    </head>
    
    <body>

        
        
        <div class="container" >
<div class="page-header">
<h1>Editor Cruz Roja - Imágenes Destacados</h1>
<?php if (!empty($message)):?>
<div class="alert alert-warning" role="alert"><?php echo $message; ?></div>
<?php endif ;?>
</div>

<?php if (!empty($availableImgs)):?>
<div class="alert alert-warning" role="alert"><?php echo $availableImgs; ?></div>
<?php endif ;?>



        <form id="imageform" method="post" enctype="multipart/form-data" action='#'>
        <div class="row">
        <label for="submit-folder">Nombre de carpeta:<?=$_SESSION['destacados_img_folder']?></label>
        
        </div>
        <div class="row">
        	<h2>Destacados</h2>
            <label for="submit-img">Upload an image:</label>
            <input type="file" name="photoimg" id="submit-img" />
            <input type="hidden" name="MAX_FILE_SIZE" value="11509760" />
            <div id="uploading">Uploading... Please Wait</div>
        </div>        
        </form>
        </div>
        
        
<!-- Modal -->
<div class="modal fade" id="imgModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog"  style="width:1020px;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Titulo Imagen</h4>
      </div>
      <div class="modal-body">
<img src="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



    </body>
    </html>
<?php endif;?>