<?php
if(isset($_GET['borrar_sesion'])){
	unset($_SESSION);
	header("Location:".$_SERVER['PHP_SELF']);
}

?>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <link href="static/inc/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<script src="static/inc/bootstrap/js/bootstrap.min.js"></script>
		<script src="static/inc/jquery-ui/jquery-ui.js"></script>
		<link href="static/inc/jquery-ui/jquery-ui.css" rel="stylesheet"> 
		<link href="static/estilos.css" rel="stylesheet"> 
		<script src="static/behave.js"></script>
		
<nav class="navbar navbar-default " role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <!-- <a class="navbar-brand" href="#">Brand</a> -->
    </div>
	<script>
	 $(function() {
	 $("#navtop > li").click(function(){
	 	$("#navtop > li").removeClass("active");
		 $(this).addClass("active");
	 });
	 });
	</script>
	
    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul id="navtop" class="nav navbar-nav">
       <li><a href="#"><b>Destacados XML</b></a></li>
        <li><a href="CR_form1.php">Paso1</a></li>
        <li><a href="CR_form2.php#">Paso2</a></li>
        <li><a href="image_destacados_generator.php">Imágenes Destacados</a></li>
        <!-- <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="#">Separated link</a></li>
            <li class="divider"></li>
            <li><a href="#">One more separated link</a></li>
          </ul>
        </li> -->
      </ul>
      <!-- <form class="navbar-form navbar-left" role="search">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form> -->
	  <?php
	  // krumo($_SESSION);
if(!isset($_SESSION['workfolder_webdir']) || !is_dir($_SESSION['workfolder_webdir'])) $rutaDescargar="javascript:alert('No hay Zip que descargar');";
	  else $rutaDescargar="lib/zipmake.php?dir=".realpath($_SESSION['workfolder_webdir']);
	  
	  ?>
	  
      <ul class="nav navbar-nav navbar-right">
       <li><a href="#"><b>CruzrojaTV XML</b></a></li>
       <li class="divider"></li>
      <li><a href="CR_television_form1.php">Paso1</a></li>
      <li><a href="CR_television_form2.php">Paso2</a></li>
        <li><a href="image_thumb_generator.php">CruzrojaTV Imágenes</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">Acciones <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
          
            <li><a href="<?=$rutaDescargar?>">Descargar ZIP</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li class="divider"></li>
            <li><a href="?borrar_sesion=true;">Borrar Sesión</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>