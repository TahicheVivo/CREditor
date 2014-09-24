<?php
session_start();
$root_path = realpath( dirname( __FILE__ ) ) . '/';
include_once( $root_path . '../libs/krumo/class.krumo.php');
include_once( $root_path . 'lib/Images.php' );
$message="";
$nextPaso="CR_television_form2.php";
// variables para el form
setlocale(LC_ALL, 'es_ES');
$todayFolder=strftime("%d%B%y");
$existeXMLTelevision=false;
// krumo($_POST);
if(!isset($_SESSION['workfolder_webdir']) || !is_dir($_SESSION['workfolder_webdir'])){
	$message = "Necesitas crear carpeta para xml e imágenes";
}
else{
	$message= "Existe directorio {$_SESSION['workfolder_webdir']}";
	$xmlDestino=$_SESSION['workfolder_webdir']."Television.xml";
    $fp = @fopen($xmlDestino, 'r');
    if($fp){
    $existeXMLTelevision=$xmlDestino;
    $message.=" y <a href='{$existeXMLTelevision}' target='_blank'>XML</a> ---> <a href='{$nextPaso}'>Próximo paso</a>";
    fclose($fp);
    }
    else{
	    $message.=" <br>No existe el XML - {$xmlDestino} - Importar?";
    }
}

if(isset($_POST['folder'])){
$folder="archivo/".$_POST['folder']."/web/";


$_SESSION['workfolder']=$_POST['folder'];

$todayFolder=$_SESSION['workfolder'];

$_SESSION['workfolder_webdir']=$folder;

if (is_dir($folder)) {
$message = "Carpeta '{$folder}' <b>ya existía</b>";
$message .= "<br> {$folder} es la carpeta activa";
 $message.= "<a href='{$nextPaso}'>Próximo paso</a>";
}

else{
    //krumo($folder);
    if (!createPath( $folder )){
	    $message = "Error making folder";
    }
	$message = "Carpeta {$folder} creada";
	$message .= "<br> {$folder} es la carpeta activa";
	 $message.= "<a href='{$nextPaso}'>Próximo paso</a>";
	}
	
	
}


if(isset($_POST['botimportXML'])){
$message="";
	// create a new cURL resource
$ch = curl_init();
// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, "http://cruzroja.vivocomtech.net/Television.xml");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_HEADER, 0);
// grab URL and pass it to the browser
$out = curl_exec($ch);

// close cURL resource, and free up system resources
curl_close($ch);
$xmlDestino=$_SESSION['workfolder_webdir']."Television.xml";

//krumo(mb_detect_encoding($out));

$fp = fopen($xmlDestino, 'w');
fwrite($fp, $out);
fclose($fp);

//krumo(dirname($_SESSION['workfolder_webdir']));
$safeFolder=dirname($_SESSION['workfolder_webdir'])."/_safe/" ;
if(!createPath($safeFolder) )$message .="No se puede crear _safe folder";
//.strftime("%d%B%y")."
$xmlDestinoSAFE=$safeFolder."Television.xml";

$fp = fopen($xmlDestinoSAFE, 'w');
fwrite($fp, $out);
fclose($fp);


   $message .= "<h2>Archivo <a href='{$xmlDestino}' target='_blank'>{$xmlDestino}</a> creado</h2>";
    $message.= "<a href='{$nextPaso}'>Próximo paso</a>";
    
   // $message.=krumo(file_get_contents($xmlDestino));
   
   
}


       
// http://cruzroja.vivocomtech.net/Television.xml

?>
 <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>CruzRoja Television Noticias XML</title>
		
		<?php include("includes/header.php") ?>

        <script type="text/javascript">
        jQuery(document).ready(function($){
           
            $("#cambiarcarpeta").click(function() {
                $("#folderform").submit();
            });
            
            $("#resetcarpeta").click(function() {
                $("#folder").val('<?php echo $todayFolder ?>');
            });
			
			<?php 
			// si ya existe advertimos de que se sobreescribirá
			if ($existeXMLTelevision):?>
			
			 $("#botimportXML").click(function() {
				 return confirm('Ya existe un XML importado \n "<?php echo $existeXMLTelevision;?>" \n Esto lo sobreescribirá y los cambios se perderán.\n ¿Seguro?');
            });

			<?php endif; ?>
            
        });
        </script>



<div class="container" >
<div class="page-header">
<h1>Editor Cruz Roja Television - XML</h1>
<h2>Paso 1 - Carpeta e importar</h2>
<?php if (!empty($message)):?>
<div class="alert alert-warning" role="alert"><?php echo $message; ?></div>
<?php endif ;?>
</div>



        <form id="folderform" method="post" action='#'>
        <div class="row">
        <label for="submit-folder">Nombre de carpeta:</label>
        <input type="text" name="folder" id="folder"  value="<?php
         echo $carp=isset($_SESSION['workfolder'])?$_SESSION['workfolder']:$todayFolder; 
         ?>"/> 
        <a href="#" id="cambiarcarpeta" class="btn btn-success btn-large">Crear Carpeta</a>
        <a href="#" id="resetcarpeta" class="btn btn-success btn-large">Hoy</a>
        </div>
         </form>
<?php
if (isset($_SESSION['workfolder'])):
?>

        
<form id="importXML" method="post" action='<?php echo $_SERVER['PHP_SELF'];?>'>
<div class="row">
<h3>Importar XML </h3>http://cruzroja.vivocomtech.net/Television.xml
<input type="submit" id="botimportXML" name="botimportXML" class="btn btn-success btn-large" value="Importar XML"></input>
<input type="hidden" name="importXML" />
</div>
</form>
<?php endif; ?>         


</div><!-- fin container -->
    </body>
    </html>
