<?php
session_start();
$root_path = realpath( dirname( __FILE__ ) ) . '/';
include_once( $root_path . 'lib/Images.php' );
include_once( $root_path . '../libs/krumo/class.krumo.php');

$paso1="CR_form1.php";

setlocale(LC_ALL, 'es_ES');
$todayFolder=$_SESSION['workfolder']?$_SESSION['workfolder']:strftime("%d%B%y");

if(!isset($_SESSION['workfolder_webdir'])){
	$message = "Necesitas crear carpeta para xml e imágenes <a href='{$paso1}'>Paso 1</a>";
}
else{
//$xmlDestino=$_SESSION['workfolder_webdir']."Destacados.xml";
$folder=$_SESSION['workfolder_webdir'];
}

$destacados_img_folder=$_SESSION['workfolder_webdir']."img/destacados/".$todayFolder."/";
if(!is_dir($destacados_img_folder)){
$message = "Creando carpeta<br>";
    //krumo($folder);
    if (!createPath( $destacados_img_folder )){
	    $message = "Error making folder";
    }
	$message .= "Carpeta {$destacados_img_folder} creada";
	$message .= "<br> {$destacados_img_folder} es la carpeta activa";

}
$_SESSION['destacados_img_folder']=$destacados_img_folder;


if(isset($_GET['delete'])){
	//unset($_SESSION);
	unlink($_GET['delete']);
	header("Location:".$_SERVER['PHP_SELF']);
}

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
				$final_img_name = url_slug($final_image_name).".jpg";
				//krumo($final_img_name);
				//set image thumbnail destination
				$store_filename = $root_path.$path.$final_img_name;
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
    // $images=readImagesFolder();
    //krumo($images);
    // pintamos las imágenes disponibles
    $images=findFiles($_SESSION['destacados_img_folder'], $ext=array("png","gif","jpg"));
    //krumo($images);
		$availableImgs="";
		foreach($images as $path){
		$relPath=translatePathToRelIMG($path);
		$deletepath=$_SERVER['PHP_SELF']."?delete=".$path;
		
			$availableImgs.= "<div class='popupImgs' style='width:200px;display: inline-block;margin:0 10px'>";
			$availableImgs.="<a class='destthumb' href='#imgModal' data-toggle='modal' data-relimg-url='{$relPath}' data-img-url='{$path}'><img  width='200' height='auto' src='{$path}' /></a>"; 
			$availableImgs.= "<div style='word-break:break-all;font-size:11px'>{$path}</div><a href='{$deletepath}'  onclick='return confirm(\"Borrar Imagen?\")'><span class='glyphicon glyphicon-remove ' style='color:red'></span></a></div>";
		}
	
	$_SESSION['destacados_folderimages']=$images;
    
    ?>
    
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Upload Image</title>
		
		<?php include("includes/header.php") ?>
        
        <?php
        // si estamos un popup modificamos display, pasamos el desde el opener
      if(isset($_GET['popup']) && $_GET['popup']):?>
      <style>
	    .navbar{
			display: none;
			}
		.page-header  { margin: -20px 0 20px;} 
        </style>
      <?php endif; ?>  
      
      <script> 
	   // las imagenes presentes en esta carpeta, para usar luego en jquery
	   var imagesinfolder=<?php echo json_encode($_SESSION['destacados_folderimages']); ?>;
	   
	   var isPopUp=<?php echo isset($_GET['popup'])?'true':'false';?>;
	   
	   </script>

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
        
        
        

        </script>
        <style>
	        
        </style>
    </head>
    
    <body>

        
        
        <div class="container" >
<div class="page-header">
<h1>Editor Cruz Roja - Imágenes Destacados</h1>
<p>Imágenes 577 x 299 </p>
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
