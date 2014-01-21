/**
 ** IHEngine (Innocent Hill Engine)
 ** ===============================
 ** Motor-script para aventuras conversacionales simples con soporte log-chat multijugador
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

	if (obj.action == 'end') 
		return show_end(obj);
	if (obj.action == 'endurl')
		window.location.href = obj.data;

	$('#text').text(obj.data.description);
	if (obj.data.image != undefined)
		$('#img').attr('src', '/img/' + obj.data.image);
	$('#chat').load('/action/?chat=1');
	if (obj.data.music != undefined)
		music = new Audio('/img/'+ obj.data.music).play();
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

function show_end(obj) {
	$('body').val('');
	score = (obj.data.score ? '<p><strong>Score: ' +obj.data.score+ '</strong></p>' : '');
	$('body').append('<div class="endletter"><div><h2>'+ obj.data.title +'</h2><p>'+ obj.data.text +'</p>'+score+'</div></div>'+
					 '<div class="stars"></div><div class="twinkling"></div><div class="clouds"></div>');
	return;
}

function doAction(s) {

	$.getJSON("language.json", function(lang) {

		// fast cleanner (supertrim & minus)
		s = s.replace(/\s+/g, ' ').toLowerCase().trim();

		// empty action
		if (s.length == 0)
			return false;

		if (s == lang['LOOK_VERB']) {
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
			obj = jQuery.parseJSON(data); //data; 
		});

		if (obj.data) {
			$('#text').text(obj.data);
		}

		if (obj.action == 'nickname') {
			if (obj.data == 'NONICK_SET') {
				var n = 1 + Math.floor(Math.random() * lang['NONICK_EXCUSES'].length);
				m = lang['NONICK_EXCUSES'];
				var message = m[n-1];
				$('#text').text(message);
			}
			else if (obj.data == 'NONICK_EXIST') {
				$('#text').text(lang['NONICK_EXIST']);
				$('#input').val('').focus();
				return;
			}
			else if (obj.data == 'NONICK_ERROR') {
				$('#text').text(lang['NONICK_ERROR']);
			}
			else if (obj.data == 'OK') {
				$('#text').text('OK!');
				$('#input').val('').focus();
				highlight_elem('#input', '#444');
				return;
			}

			highlight_elem('#input', '#444');
			$('#input').val('nickname ').focus();
			return;
		}

		if (obj.action == 'mirar') {
			if (obj.data == 'FAIL') {
				var n = 1 + Math.floor(Math.random() * lang['LOOK_EXCUSES'].length);
				m = lang['LOOK_EXCUSES'];
				message = m[n-1];
				$('#text').text(message);
			}

			highlight_elem('#input', '#444');
		}

		if (obj.action == 'coger') {
			if (obj.data == 'FAIL') {
				var n = 1 + Math.floor(Math.random() * lang['TAKE_EXCUSES'].length);
				m = lang['TAKE_EXCUSES'];
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
					var n = 1 + Math.floor(Math.random() * lang['GOTO_EXCUSES'].length);
					m = lang['GOTO_EXCUSES'];
					message = m[n-1];
				} else {
					message = obj.data;
				}
				$('#text').text(message);
			}
		}

		if (obj.action == 'end') {
			show_end(obj);
		}

		if (obj.action == 'endurl') {
			window.location.href = obj.data;
		}

		if (obj.action == 'chat') {
			doInfo();
		}

		if (obj.action == 'hablar') {
			$('#text').text('');
			$('#text').append('<div class="talk"></div>');
			$("#input").prop('disabled', true);
			$('#input').css('display', 'none');
			$.each(obj.talk, function(k, v) {
				$('#text .talk').append('<a id="talk-'+k+'" class="'+v.v+'" href="#">'+v.m+'</a><br />');
			});
				
		}

	 	$('#input').val('');
	 	$('#input').focus();

	});

}