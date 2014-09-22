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

if(!isset($_SESSION['cruzrojatv_img_folder']) || !is_dir($_SESSION['cruzrojatv_img_folder'])){
$message = "Creando carpeta<br>";
$cruzrojatv_img_folder=$_SESSION['workfolder_webdir']."img/cruzrojatv/".$todayFolder."/";
$_SESSION['cruzrojatv_img_folder']=$cruzrojatv_img_folder;
    //krumo($folder);
    if (!createPath( $cruzrojatv_img_folder )){
	    $message = "Error making folder";
    }
	$message .= "Carpeta {$cruzrojatv_img_folder} creada";
	$message .= "<br> {$cruzrojatv_img_folder} es la carpeta activa";

}


    if($_SERVER['REQUEST_METHOD'] == "POST") {
    
     	$folder= $_SESSION['cruzrojatv_img_folder'];     
		//Upload limit size in megabytes
		$upload_size_limit = 10;
		//this is the image destination path
		$path = $folder;
		//temp image storage directory
		$temp_path = "temp/";
		//list all accepted image formats here (extensions)
		$valid_formats = array("jpg", "jpeg", "png","gif", "bmp");
		//grab the submitted image name and size
		$uploaded_img_name = $_FILES['thumbimg']['name'];
		
		$final_image_name=pathinfo($_FILES['thumbimg']['name'], PATHINFO_FILENAME );
		//size is measured in bytes
		$uploaded_img_size = $_FILES['thumbimg']['size'];
		//get image extension
		$ext = pathinfo($uploaded_img_name, PATHINFO_EXTENSION);
		//make sure extension is acceptable
		// krumo(pathinfo($_FILES['thumbimg']['name'], PATHINFO_FILENAME ));
		
		if(in_array(strtolower($ext),$valid_formats)) {
			//make sure image is not too big (1048576 bytes = 1 megabyte)
			if($uploaded_img_size<(1048576*$upload_size_limit)) {
				//store uploaded image temporarily in "temp" directory while resizing is performed
				$tmp_img_name = "temp-".time().".".$ext;
				//create the permanent filename with extension - using time() as filename will ensure a unique filename. Image will be converted to jpg (if it's in another format) so we set it to jpg here.
				$final_img_name = url_slug($final_image_name).".png";
				
				//set image thumbnail destination
				$store_filename = $root_path.$path.$final_img_name;
				//put uploaded image in var
				$uploaded_img = $_FILES['thumbimg']['tmp_name'];
				//move the image into the temp directory while we work with it
				$tempLocationFile=$temp_path.$tmp_img_name;
				if($file=move_uploaded_file($uploaded_img, $tempLocationFile)) {
				// 159x71 la imagen jpg detras
				// 164x71 el archivo final
				
				// krumo($tempLocationFile);
					    include_once("lib/resize-class.php");
                        $resizeObj = new resize($tempLocationFile);
						$resizeObj -> resizeImage(159, 71, "crop");
						$resizeObj -> saveImage($tempLocationFile, 100);
						
						
						 // $img = imagecreatefromjpeg($tempLocationFile);
						$img=$resizeObj->openImage($tempLocationFile);
						imageAlphaBlending($img, false);
						imageSaveAlpha($img, true);
						
						 $fileSRC= getRelativePath($store_filename, __FILE__ );
						// imagecopymerge($bg, $img, 0, 0, 0, 0, imagesx($bg), imagesy($bg), 100);
						$cut = imagecreatetruecolor(164, 71); 
						imagealphablending($cut, false);
						//Create alpha channel for transparent layer
						$col=imagecolorallocatealpha($cut,255,255,255,127);
						//Create overlapping 100x50 transparent layer
						imagefilledrectangle($cut,0,0,164, 71,$col);
						//Continue to keep layers transparent
						imagealphablending($cut,true);						
						 // copying relevant section from background to the cut resource 
						 imagecopy($cut, $img, 0, 0, 0, 0, imagesx($img), imagesy($img)); 
						 imagesavealpha($cut,true);
						 
						 $overlayIMG=imagecreatefrompng("overlays/thumb_overlay.png");
						 // copying relevant section from watermark to the cut resource 
						 imagecopy($cut, $overlayIMG, 0, 0, 0, 0, imagesx($overlayIMG), imagesy($overlayIMG));
						 
						 imagesavealpha($cut,true);
						 // insert cut resource to destination image 
						 // imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 

						imagepng($cut, $store_filename, 1);

						$message= "{$store_filename} <img src='{$fileSRC}'/>";
						
						
												
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
    $images=findFiles($_SESSION['cruzrojatv_img_folder'], $ext=array("png","gif","jpg"));
    //krumo($images);
		$availableImgs="";
		foreach($images as $path){
		$relPath=translatePathToRelIMG($path);
		
			$availableImgs.= "<div style='width:164px;display: inline-block;margin:0 10px'>";
			$availableImgs.="<a class='destthumb' href='#imgModal' data-toggle='modal' data-relimg-url='{$relPath}' data-img-url='{$path}'><img  width='auto' height='auto' src='{$path}' /></a>"; 
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
        });
        </script>
        <style>
	        
        </style>
    </head>
    
    <body>
       
        
        <?php
        // variables para el form
        setlocale(LC_ALL, 'es_ES');
        $todayFolder=strftime("%d%B%y");
        ?>
         <div class="container" >
<div class="page-header">
<h1>Editor Cruz Roja - Imágenes CruzrojaTV</h1>
<?php if (!empty($message)):?>
<div class="alert alert-warning" role="alert"><?php echo $message; ?></div>
<?php endif ;?>
</div>

<?php if (!empty($availableImgs)):?>
<div class="alert " role="alert"><?php echo $availableImgs; ?></div>
<?php endif ;?>

        <form id="imageform" method="post" enctype="multipart/form-data" action='#'>
        
        <div class="row">
        <label for="submit-folder">Nombre de carpeta:<?=$_SESSION['cruzrojatv_img_folder']?></label>
        
        </div>
        
        <div class="row">
        	<h2>CruzrojaTV</h2>
            <label for="submit-img">Upload an image:</label>
            <input type="file" name="thumbimg" id="submit-img" />
            <input type="hidden" name="MAX_FILE_SIZE" value="11509760" />
            <div id="uploading">Uploading... Please Wait</div>
        </div>        
        </form>
        </div>
    </body>
    </html>