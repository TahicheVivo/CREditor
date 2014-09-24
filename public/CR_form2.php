<?php
session_start();
$root_path = realpath( dirname( __FILE__ ) ) . '/';
include_once( $root_path . '../libs/krumo/class.krumo.php');
include_once ($root_path . '../libs/Array2XML.php');
include_once ($root_path . '../libs/XML2Array.php');
include_once($root_path . '../libs/php-form-builder/PhpFormBuilder.php');
include_once( $root_path . 'lib/Images.php' );
//krumo($_SESSION);

$remoteURL = "http://cruzroja.vivocomtech.net";

$message="";
$nextPaso="CR_form2.php";
$paso1="CR_form1.php";
// variables para el form
setlocale(LC_ALL, 'es_ES');

if(!isset($_SESSION['workfolder_webdir'])){
	$message = "Necesitas crear carpeta para xml e imágenes";
}
$xmlDestino=$_SESSION['workfolder_webdir']."Destacados.xml";

//krumo($xmlDestino);

//si hemos post, guardo el nuevo XML con los datos
if($_SERVER['REQUEST_METHOD'] == "POST"  && isset($_POST['id']) ){
	$errores=array();
	$destacadosArrForm=array();
	$campos=array(
		"id",
		"Titulo",
		"Texto",
		"Imagen",
		"Enlace",
		"TextoEnlace"
		);
	
	// $errores[]="Quitar esto en linea 38 para que guarde el archivo";
	
	for($i=0; $i<(int)(count($_POST['id']));$i++){
		$nodo=array();
		foreach($campos as $x=>$val){
			$nodo[$val]=$_POST[$val][$i];
			if(!$nodo[$val] || empty($nodo[$val])) $errores[]="No value for ".$val." ".$i;
		}
		$nodo["@attributes"]=array("id"=>$_POST['id'][$i]);
		$destacadosArrForm[]=$nodo;
		//krumo($nodo);
	}
	$Destacados=array(
	"Destacados"=>array(
	"Destacado"=>$destacadosArrForm,
	"@attributes"=>array("noNamespaceSchemaLocation"=>"Destacados.xsd"))
	);
	//krumo($Destacados);	
	if(empty($errores)){
	$Array2XML= Array2XML::init( '1.0', 'iso-8859-1');
	$outputDestXML = Array2XML::createXML('Destacados', $Destacados);
	$xmlString=$outputDestXML->saveXML();
	$xmlString=utf8_encode($xmlString);
	
	$xmlDestinoFile=$_SESSION['workfolder_webdir']."Destacados.xml";
	$fp = fopen($xmlDestinoFile, 'w');
	fwrite($fp, $xmlString);
	fclose($fp);
	}
	else $message="<h3>Errores al parsear </h3>".implode("<br>",$errores);
}


/*$xml = simplexml_load_file($xmlDestino, 'SimpleXMLElement',LIBXML_NOCDATA); 
$json = json_encode($xml);
$XMLarray = json_decode($json,TRUE);
krumo($XMLarray);*/

$xml=@file_get_contents($xmlDestino);
// por lo visto es mejor iconv si el texto puede contener caracteres como € que no estan en utf_decode
//$xml=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $xml);
if($xml){
// utf8_decode para los caracteres especiales, ñ y demás
$xml=utf8_decode($xml);
// cargamos el XML Destacados y lo transformamos en un Array para poder crear el formulario
$destacadosArray = XML2Array::createArray($xml);
$formulario = buildDestacadosForm($destacadosArray);
}
else{
	$message = "No se encuentra el XML {$xmlDestino} <a href='{$paso1}'>Paso 1</a>";
}

//krumo($destacadosArray);

// http://cruzroja.vivocomtech.net/Destacados.xml

// construir formulario
function buildDestacadosForm($xmlArray){
//krumo($xmlArray);
$baseNode=$xmlArray['Destacados']['Destacado'];
//krumo(count($baseNode));
	$form = new PhpFormBuilder();
	$form->set_att('enctype', 'multipart/form-data');
	$form->set_att('method', 'post');
	$form->set_att('id', 'cr_xml_edit');
	
	$count=1;
	foreach($baseNode  as $int=>$value){
		//krumo($value);
		$idNodo=$value['@attributes']['id'];
		$idarr = array_combine(range(1, count($baseNode)), range(1, count($baseNode)));
		//
		
		$form->add_input('<div class="form-group nodoxml">', array(
		'type' => 'html'
		),
		"form-group".$count
		);
		
		$form->add_input('numerocampos', array(
		'type' => 'hidden',
		'value' =>count($baseNode)
		)
		);


		$form->add_input('ID', array(
		'wrap_class' => array('form_field_wrap id inline'),
		'class'=>array("form-control id"),
		'type' => 'select',
		'options' => $idarr,
		'selected' =>$idNodo,
		'name' => 'id[]'
		),
		'id_'.$count
		);
		
		$form->add_input('Título', array(
		'wrap_class' => array('form_field_wrap titulo'),
		'class'=>array("form-control titulo"),
		'type' => 'textfield',
		'value' => $value['Titulo'],
		'name'=>'Titulo[]'
		),
		'Titulo_'.$count
		);
		
		$form->add_input('Texto', array(
		'wrap_class' => array('form_field_wrap texto'),
		'class'=>array("form-control texto"),
		'type' => 'textarea',
		'value' => htmlspecialchars_decode($value['Texto']),
		'name'=>'Texto[]'
		),
		'Texto_'.$count
		);
		$form->add_input('Enlace', array(
		'wrap_class' => array('form_field_wrap enlace'),
		'class'=>array("form-control enlace"),
		'type' => 'textfield',
		'value' => $value['Enlace'],
		'name'=>'Enlace[]'
		),
		'Enlace_'.$count
		);
		$form->add_input('TextoEnlace', array(
		'wrap_class' => array('form_field_wrap textoenlace'),
		'label'=>"Posibles imágenes para botón - TextoEnlace",
		'class'=>array("form-control textoenlace"),
		'type' => 'textfield',
		'value' => $value['TextoEnlace'],
		'name'=>'TextoEnlace[]'
		),
		'TextoEnlace_'.$count
		);
		
		$form->add_input('Imagen', array(
		'wrap_class' => array('form_field_wrap'),
		'label'=>"Imagen destacado",
		'class'=>array("form-control imagen"),
		'type' => 'textfield',
		'value' => $value['Imagen'],
		'name'=>'Imagen[]'
		),
		'Imagen_'.$count
		);
		

		$form->add_input('</div>', array(
		'type' => 'html'
		),
		"form-group-close".$count
		);
			
	$count++;
	}
	
		
	$out=$form->build_form(false);
	return $out;

}


?>



 <!doctype html>
    <html >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>CruzRoja XML Paso2</title>

		<?php include("includes/header.php") ?>
		
        <script type="text/javascript">
        
        // imagenes en carpeta para ver si la imagen del xml existe en la carpeta o en servidor
        var imagesinfolder=<?php 
        $imgs=isset($_SESSION['destacados_folderimages'])?$_SESSION['destacados_folderimages']:"";
        echo json_encode($imgs); 
        ?>;
        console.log(imagesinfolder);
        
        
        // funcion para escribir url remota a partir del valor del xml si es una imagen existente en servidor, no de las qu ehemos subido
        function estaEnCarpeta(path){
        // nos llega una url tipo img/destacados/18septiembre14/xxx.jpg
        // miramos si existe en el array de archivos en carpeta, sino existe es externo (servidor)
	    //console.log("----"+path);
	    	for (var i = 0, len = imagesinfolder.length; i < len; i++){
		    	if((imagesinfolder[i].indexOf(path) > -1)) return true;
	    	}
	         
	        return false;
        }
        
        jQuery(document).ready(function($){
           
            $("#cambiarcarpeta").click(function() {
                $("#folderform").submit();
            });
            
            $('<span class="ui-icon ui-icon-arrowthick-2-n-s">kokook</span>').prependTo(".form-group.nodoxml");
            
            $(".form-control.textoenlace").after( "<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#textoEnlaceModal'>Opciones Botones</button>" );
			
			/*$(".form-control.imagen").after( "<a class='btn btn-primary btn-sm' target='_blank' href='image_destacados_generator.php'>Ir a imágenes</a>" );*/
			
			
			$(".form-control.imagen").after("<button class='btn btn-primary btn-sm imagePopBt external' href='image_destacados_generator.php'>Imágenes >> </button>" );
						
			//$(".form-control.imagen").after( "<button class='btn btn-primary btn-sm imagePopBt' data-toggle='modal' data-target='#imagesModal'>Imágenes >> </button>" );
			
			// container para imagen...
			$(".form-control.imagen").after( "<img style='cursor:pointer'  href='#imgDetalleModal' data-toggle='modal'  class='thumblink' width='200' height='auto' src=' ' /><div class='textosubimagen'>YUuyuyuy</div>" );	
			
						
			$('.thumblink').click(function (e) {
				var title=$(this).attr('remote-src')?"Imagen remota - "+$(this).attr('remote-src'):"Imagen en carpeta - "+$(this).attr('src');
				/*if($(this).attr('remote-src')) title=$(this).attr('remote-src');
				else title="Imagen en carpeta - "+$(this).attr('src');*/
				$('#imgDetalleModal img').attr('src', $(this).attr('src'));
				$('#imgDetalleModal .modal-title').text(title);
			});
			
			
					
			$(".form-control.imagen").on('input change',function(){
           var folderImg="<?php echo $_SESSION['workfolder_webdir'] ?>"+$(this).val();
           var encarpeta=estaEnCarpeta($(this).val());
           // console.log(encarpeta);
           if(encarpeta){
           $(this).next('img').attr("remote-src", "");
           $(this).next('img').attr("src", folderImg);
           $(this).next('img').next('.textosubimagen').text("En carpeta");
           }
           else{
           var remoteImg="<?php echo $remoteURL ?>/"+$(this).val();
	           $(this).next('img').attr("src", remoteImg);
	           $(this).next('img').attr("remote-src", remoteImg);
	           $(this).next('img').next('.textosubimagen').text("Remota");
           }
			});
			
			
			$(".form-control.imagen").change();


			$(':submit').addClass("btn btn-success btn-lg");
			
			/*<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Collapsible Group Item #2
        </a>*/
            /*
            <!-- Button trigger modal -->
			<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
			Launch demo modal
			</button>
			*/
			// actualizo funciones de behave.js una vez creados elementos
			
			
			imgPopBots();
        });
        </script>
        
    </head>



<style>
  #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
  #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
  #sortable li span { position: absolute; margin-left: -1.3em; }
  </style>
  <script>
  $(function() {
    $( "#sortable,#sortableform > form" ).sortable({
      cursor: 'move',
      placeholder: "ui-state-highlight",
      opacity: 0.5,
      update: function(event, ui) {
                            var nodos = $(this).children(".nodoxml");
                            $i=1;
                            $(nodos).each(function(){
	                            //console.log(this);
	                            $(this).find(".form-control.id ").val($i);
	                            $i++;
                            });
                        }
    });
    // $( "#sortable,#sortableform > form" ).disableSelection();
  });
  </script>
</head>
<body>


<div class="container" >
<div class="page-header">
<h2>Paso 2 - Destacados - Editar XML</h2>
<div id="info">
Carpeta:<?= $_SESSION['workfolder']; ?><br>
XML: <?= $xmlDestino; ?>

</div>
<?php if (!empty($message)):?>
<div class="alert alert-warning" role="alert"><?php echo $message; ?></div>
<?php endif ;?>
</div>

<div id="sortableform">
<?php
// generado por funcion antes
if(isset($formulario)) echo $formulario;
?>
</div> 
             
</div><!-- fin container -->


<!-- modal botones texto enlace -->
<!-- Modal -->
<div class="modal fade" id="textoEnlaceModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Posibles Botones para enlaces</h4>
      </div>
      <div class="modal-body">
<pre>Botones cruz roja:
img/destacados/botonmasinfo.png
img/destacados/botondonaaqui.png
img/destacados/botonsaladeprensa.png
img/destacados/botoncruzrojatv.png
img/destacados/botonvideo.png	</pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>





<script type="text/javascript">
		var activeSelect=null;
        jQuery(document).ready(function($){
        // $(".form-control.imagen").
        $(".imagePopBt").click(function(){
        event.preventDefault(); 
        activeSelect=$(this).prevAll(".form-control.imagen").first();
        //$(this).prev(".form-control.imagen").css( "background", "yellow" );
        $("#availableImgsPop .destthumb").click(function(){
        	event.preventDefault(); 
	        activeSelect.val($(this).attr('data-relimg-url'));
	        activeSelect.change();
	        $('#imagesModal').modal('hide');
        });
        });
        
        
        
        })
</script>

<?php include("includes/modals.php") ?>



    </body>
    </html>
