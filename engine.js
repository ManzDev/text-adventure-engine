/**
 ** IHEngine (Innocent Hill Engine)
 ** ===============================
 ** Motor para aventuras conversacionales online multijugador
 ** 
 ** Autor: J. Román (@Manz)
 **/

$( document ).ready(function() {

	doInfo();
	$('#chat').load('/action/?chat=1');
	$('#input').focus();

});

function doInfo() {
	obj = doIt({"info":"1"});
	$('#text').text(obj.data.description);
	$('#img').attr('src', '/img/' +obj.data.image);
	$('#chat').load('/action/?chat=1');
	var music = new Audio('/img/'+ obj.data.music).play();
}

function doIt(data) {

	var result = '';
	request = $.ajax({
		type : "GET",
		url : '/action/',		
		data : data,
		datatype : "json",
		async : false
	});

	request.done(function(data) {
		result = jQuery.parseJSON(data);
	});

	return result;
}

function highlight_elem(tag, color) {
	oldcolor = $('#input').css('background-color');
	setTimeout(function() { $('#input').addClass('highlight').css('background-color', color); }, 25);
	setTimeout(function() { $('#input').removeClass('highlight').css('background-color', oldcolor); }, 300);
}

function doAction(s) {

	// fast cleanner (supertrim & minus)
	s = s.replace(/\s+/g, ' ').toLowerCase().trim();

	// empty action
	if (s.length == 0)
		return false;

	if (s == 'mirar lugar' || s == 'mirar') {
		doInfo();
		$('#input').val('');
 		$('#input').focus();
		return true;
	}

	request = $.ajax({
		type: 'POST',
		url : '/action/',
		contentType: 'application/x-www-form-urlencoded; charset=utf-8',
		data : {data: s},
		datatype: "html",
		async : false
	});

	request.done(function(data) {
		console.log(data);
		obj = jQuery.parseJSON(data); //data; 
	});

	console.log(obj);

	if (obj.data) {
		$('#text').text(obj.data);
	}

	if (obj.action == 'mirar') {
		if (obj.data == 'FAIL') {
			var n = 1 + Math.floor(Math.random() * 6);
			m = ["¿Qué mire el qué?", 
				 "No veo nada aquí, ni en mi inventario, parecido a eso.", 
				 "No sé a qué te refieres exactamente...",
				 "¿Qué mire QUÉ?",
				 "Erhm... ¿Qué dices que quieres que mire?",
				 "No se exactamente a que te refieres..."];
			message = m[n-1];
			$('#text').text(message);
		}

		highlight_elem('#input', '#444');
	}

	if (obj.action == 'coger') {
		if (obj.data == 'FAIL') {
			var n = 1 + Math.floor(Math.random() * 6);
			m = ["¿Que coja el qué? No veo como me podría ayudar eso.", 
				 "Mejor no.", 
				 "Olvídalo.",
				 "No entiendo a qué te refieres...",
				 "No me parece buena idea.",
				 "No me llevaré eso a ninguna parte."];
			message = m[n-1];
			$('#text').text(message);
		}		
		highlight_elem('#input', '#037A00');
	}

	if (obj.action == 'goto') {
		if (obj.data == 'OK') {
			doInfo();
		}
		else {
			var message;
			if (obj.data == 'FAIL') {
				var n = 1 + Math.floor(Math.random() * 6);
				m = ["No puedo ir en esa dirección.", 
					 "No hay ninguna salida hacia allá.", 
					 "No hay salida en esa dirección.",
					 "Por ahí no hay salida.",
					 "No hay salida hacia allá.",
					 "No puedo. Para ver las direcciones posibles, escribe: salidas."];
				message = m[n-1];
			} else {
				message = obj.data;
			}
			$('#text').text(message);
		}
	}

	if (obj.action == 'chat') {
		doInfo();
	}

	if (obj.action == 'hablar') {
		$('#text').text('');
		$('#text').append('<div class="talk"></div>');
		$("#input").prop('disabled', true);
		$('#input').css('display', 'none');
		//console.log(obj.talk);
		$.each(obj.talk, function(k, v) {
			$('#text .talk').append('<a id="talk-'+k+'" class="'+v.v+'" href="#">'+v.m+'</a><br />');
		});
			
	}

 	$('#input').val('');
 	$('#input').focus();
}