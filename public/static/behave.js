 //jQuery noConflict Ready Function
 $(function($){

/* collapses more info para nodos ***/
	 	 var accordionPPio = $(".form-control.texto ")
			.closest( ".form_field_wrap" );
			
			accordionPPio.each(function(i) {
				$(this).before('<h4 class="colapsa"><a data-toggle="collapse" data-target="#collapse'+i+'"><span class="glyphicon glyphicon-chevron-down"></span> Mas Info</a></h4>');
				$(this).nextAll().andSelf().slice( 0,4 ).wrapAll('<div id="collapse'+i+'" class="panel-collapse collapse"><div class="panel-body"></div></div>');
			});

	 // $('h4.colapsa a').append();

	 $('h4.colapsa a').click(function () {
		if($(this).find('span').hasClass('glyphicon-chevron-down')){
		$(this).html('<span class="glyphicon glyphicon-chevron-up"></span> Menos Info'); 
		}
		else{      
	    $(this).html('<span class="glyphicon glyphicon-chevron-down"></span> Mas Info'); 
		}
	})
	
	 /* centrar popups en pantalla */    
	$('.modal').on('show.bs.modal', centerModal);
		$(window).on("resize", function () {
		$('.modal:visible').each(centerModal);
	});
	imgPopBots();
	randomColors(".form-group.nodoxml");
				
// si es un popup de imágenes creamos los comportamientos para actualizar el parent
if (typeof(isPopUp) == 'undefined' || isPopUp == null){
    // Do nothing
}
else if(isPopUp) linksInPopup();

 })
// el campo de imagen activo cuando abrimos el popup, para cambiar el input.
var activeSelect;

/* external modal, para imagenes, etc ***/
function imgPopBots(){
$('a.external,button.external').unbind('click');
$('a.external,,button.external').click(function(ev) {
    ev.preventDefault();
    activeSelect=$(this).prevAll(".form-control.imagen").first();
    var target = $(this).attr("href")+"?popup=true";
    console.log(target);
	$("#externalModal .modal-body").html('<iframe width="100%" height="100%" frameborder="0" scrolling="yes" allowtransparency="true" src="'+target+'"></iframe>');
	$("#externalModal").modal("show");
     });
/* fin external modal, para imagenes, etc ***/

}

function linksInPopup(){
	$("div.popupImgs a.destthumb").after("<a class='popInsertLink' href='#'>Insertar</a>");
	$("div.popupImgs a.popInsertLink").click(function(ev){
	// actualizo el js de imagenes en carpeta en page parent,
	// si hemos subido alguna, el parent no tiene constancia
	window.parent.imagesinfolder=imagesinfolder;
	// activeselect es el input activo en el parent del popup
	var selectedParentInput=$(window.parent.activeSelect, window.parent.document);
			console.log(selectedParentInput);
			ev.preventDefault(); 
			var valorUrl=$(this).prev("a").attr('data-relimg-url');
			console.log(valorUrl);
	        selectedParentInput.val(valorUrl);
	        parent.$(selectedParentInput).change();
	        parent.$('#externalModal').modal('hide');
	});
}

function randomColors(target){
   return;
var colors = ["antiquewhite","aqua","aliceblue","bisque","cadetblue", "burlywood", "lavender","ivory","lightblue","linen", "seashell", "white", "whitesmoke" ];              
	$(".form-group.nodoxml").each(function(){
	 var rand = Math.floor(Math.random()*colors.length);
	$(this).css("background-color",colors[rand]);
	})
}
// funcion para centrat popups modal en pantalla 
 function centerModal() {

			$(this).css('display', 'block');
			var $dialog = $(this).find(".modal-dialog");
			var offset = ($(window).height() - $dialog.height()) / 2;
			// Center modal vertically in window
			$dialog.css("margin-top", offset);
			}

				