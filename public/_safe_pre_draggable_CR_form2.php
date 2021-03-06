<?php
session_start();
$root_path = realpath( dirname( __FILE__ ) ) . '/';
include_once( $root_path . '../libs/krumo/class.krumo.php');
include_once( $root_path . '../libs/Images.php' );
include_once ($root_path . '../libs/Array2XML.php');
include_once ($root_path . '../libs/XML2Array.php');
include_once($root_path . '../libs/php-form-builder/PhpFormBuilder.php');

//krumo($_SESSION);

$message="";
$nextPaso="CR_form2.php";
// variables para el form
setlocale(LC_ALL, 'es_ES');

if(!isset($_SESSION['workfolder_webdir'])){
	$message = "Necesitas crear carpeta para xml e imágenes";
}
$xmlDestino=$_SESSION['workfolder_webdir']."Destacados.xml";

//krumo($xmlDestino);

//si hemos post, guardo el nuevo XML con los datos
if($_SERVER['REQUEST_METHOD'] == "POST"  && isset($_POST['numerocampos']) ){
	//krumo($_POST);
	$destacadosArrForm=array();
	$campos=array(
		"Titulo",
		"Texto",
		"Imagen",
		"Enlace",
		"TextoEnlace"
		);
		
	for($i=1; $i<(int)($_POST['numerocampos']+1);$i++){
		$nodo=array();
		foreach($campos as $x=>$val){
			$nodo[$val]=$_POST[$val."_".$i];
		}
		$nodo["@attributes"]=array("id"=>$_POST["id_".$i]);
		$destacadosArrForm[]=$nodo;
		//krumo($nodo);
	}
	$Destacados=array(
	"Destacados"=>array(
	"Destacado"=>$destacadosArrForm,
	"@attributes"=>array("noNamespaceSchemaLocation"=>"Destacados.xsd"))
	);
	//krumo($Destacados);	
	
	$Array2XML= Array2XML::init( '1.0', 'iso-8859-1');
	$outputDestXML = Array2XML::createXML('Destacados', $Destacados);
	$xmlString=$outputDestXML->saveXML();
	$xmlString=utf8_encode($xmlString);
	
	$xmlDestino2=$_SESSION['workfolder_webdir']."Destacados.xml";
	$fp = fopen($xmlDestino2, 'w');
	fwrite($fp, $xmlString);
	fclose($fp);
}


/*$xml = simplexml_load_file($xmlDestino, 'SimpleXMLElement',LIBXML_NOCDATA); 
$json = json_encode($xml);
$XMLarray = json_decode($json,TRUE);
krumo($XMLarray);*/

$xml=file_get_contents($xmlDestino);
// por lo visto es mejor iconv si el texto puede contener caracteres como € que no estan en utf_decode
//$xml=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $xml);
// utf8_decode para los caracteres especiales, ñ y demás
$xml=utf8_decode($xml);
// cargamos el XML Destacados y lo transformamos en un Array para poder crear el formulario
$destacadosArray = XML2Array::createArray($xml);


$formulario = buildDestacadosForm($destacadosArray);

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
		'class'=>array("form-control id"),
		'type' => 'select',
		'options' => $idarr,
		'selected' =>$idNodo
		),
		'id_'.$count
		);
		
		$form->add_input('Título', array(
		'class'=>array("form-control titulo"),
		'type' => 'textfield',
		'value' => $value['Titulo']
		),
		'Titulo_'.$count
		);
		
		$form->add_input('Texto', array(
		'class'=>array("form-control texto"),
		'type' => 'textarea',
		'value' => htmlspecialchars_decode($value['Texto'])
		),
		'Texto_'.$count
		);
		$form->add_input('Enlace', array(
		'class'=>array("form-control enlace"),
		'type' => 'textfield',
		'value' => $value['Enlace']
		),
		'Enlace_'.$count
		);
		$form->add_input('TextoEnlace', array(
		'label'=>"Posibles imágenes para botón - TextoEnlace",
		'class'=>array("form-control textoenlace"),
		'type' => 'textfield',
		'value' => $value['TextoEnlace']
		),
		'TextoEnlace_'.$count
		);
		
		$form->add_input('Imagen', array(
		'label'=>"Imagen destacado",
		'class'=>array("form-control imagen"),
		'type' => 'textfield',
		'value' => $value['Imagen']
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
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <link href="inc/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<script src="inc/bootstrap/js/bootstrap.min.js"></script>
		
		<script src="inc/jquery-ui.js"></script>
		<link href="inc/jquery-ui.css" rel="stylesheet">
		
		
        <script type="text/javascript">
        jQuery(document).ready(function($){
           
            $("#cambiarcarpeta").click(function() {
                $("#folderform").submit();
            });
            
            $(".textoenlace").after( "<button class='btn btn-primary btn-sm' data-toggle='modal' data-target='#textoEnlaceModal'>Opciones Botones</button>" );
			
			$(".form-control.imagen").after( "<a class='btn btn-primary btn-sm' target='_blank' href='image_destacados_generator.php'>Ir a imágenes</a>" );
			
			
			
			var accordionPPio = $(".form-control.texto ")
			.closest( ".form_field_wrap" );
			
			accordionPPio.each(function(i) {
				$(this).before('<h4 class="colapsa"><a data-toggle="collapse" data-target="#collapse'+i+'"><span class="glyphicon glyphicon-circle-arrow-down"></span></a></h4>');
				$(this).nextAll().andSelf().slice( 0,4 ).wrapAll('<div id="collapse'+i+'" class="panel-collapse collapse"><div class="panel-body"></div></div>');
			});

			/*<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
          Collapsible Group Item #2
        </a>*/
            /*
            <!-- Button trigger modal -->
			<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
			Launch demo modal
			</button>
			*/
        });
        </script>
        <style>
	        .form-group.nodoxml{
	        margin: 40px auto;
	        padding-bottom: 20px;
	        /*border-bottom: 20px solid #aeaeae;*/
		        
	        }
	        .form_field_wrap{
		         margin: 10px auto;
	        }
	        textarea.form-control{
		        height: 120px;
	        }
	        .form-control.titulo{
		        font-size: 2em;
				height: 2em;
	        }
	        h4.colapsa{
		        background-color:#e4e4e4;
		        border-radius:20px;
	        }
	        h4.colapsa a{
		        color: #333;
		        display: block;
		        width: 100%;
		        padding: 10px;
	        }
        </style>
    </head>

<?php include("header.php") ?>

<div class="container" >
<div class="page-header">
<h2>Paso 2 - Editar XML</h2>
<div id="info">
Carpeta:<?= $_SESSION['workfolder']; ?><br>
XML: <?= $xmlDestino; ?>

</div>
<?php if (!empty($message)):?>
<div class="alert alert-warning" role="alert"><?php echo $message; ?></div>
<?php endif ;?>
</div>


<?php
// generado por funcion antes
echo $formulario;
?>
              
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

    </body>
    </html>
