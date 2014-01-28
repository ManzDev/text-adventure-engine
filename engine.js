/**
 ** IHEngine (Innocent Hill Engine)
 ** ===============================
 ** Conversational adventure engine with multiplayer chat-logging system.
 **
 ** Twitter: (@Manz)
 **/

$( document ).ready(function() {

	doInfo();
	$('#chat').load('/action/?chat=1');
	$('#input').focus();

	lang = (function() {
		var json = null;
		$.ajax({
			'async': false,
			'global': false,
			'url': 'language.json',
			'dataType': 'json',
			'success': function (data) {
				json = data;
			}
		});
		return json;
	})();

});

function update_items(i) {
	$('#pretext').text('');

	if (items_on_room == undefined)
		items_on_room = [];

	if (i != undefined) {
		if (i.substring(0,1) == '+')
			items_on_room.push(i.substring(1));
		else if (i.substring(0,1) == '-')
			items_on_room.pop(i.substring(1));
	}

	if (items_on_room != undefined)
		$.each(items_on_room, function(k,v) {
			$('#pretext').append('<span onclick="$(\'#input\').val(\'coger '+v+'\')">'+v+'</span>');
		})
}

function doInfo() {
	obj = doIt({"info":"1"});

	if (obj.action == 'end') 
		return show_end(obj);
	if (obj.action == 'endurl')
		window.location.href = obj.data;

	$('#text').text(obj.data.description);
	if (obj.data.image != undefined)
		$('#img img').attr('src', obj.data.image);
	$('#chat').load('/action/?chat=1');
	if (obj.data.music != undefined)
		music = new Audio(obj.data.music).play();

	items_on_room = obj.data.items;
	update_items();
}

function show_help() {
	$('#help div.help').text(lang['HELP_FIRST_TIP']);
	$('#help div.help').toggle();
}

function show_popup(obj) {
	$('#input').val('');
	$('<div id="item"><img src="'+obj.image+'" /><span>'+obj.message+'</span></div>').appendTo('body');
	$('#item').delay(3000).fadeOut(1000, function(){ $(this).remove(); }); 
	$('#input').focus();
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

function reset() {
	$('#input').val('').focus();
}

function doAction(s) {

	// fast cleanner (supertrim & minus)
	s = s.replace(/\s+/g, ' ').toLowerCase().trim();

	//console.log('length: ' + s);
	// empty action
	if (s.length == 0)
		return false;

	if (s == lang['LOOK_VERB']) {
		doInfo();
		reset();
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

	console.log(obj);

	if (obj.action == 'item') {
		show_popup(obj.data);
		return;
	}

	if (obj.action == 'drop') {
		if (obj.data.substring(0,1) == '+') {
			update_items(obj.data);
			$('#text').text(lang['DROP_ITEM']);
		}
		if (obj.data == 'FAIL')
			$('#text').text(lang['DROP_CANT']);

		reset();
		return;
	}

	if (obj.data) 
		$('#text').text(obj.data);

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
		else if (obj.data == 'NONICK_EMPTY') {
			$('#text').text(lang['NONICK_EMPTY']);
		}			
		else if (obj.data == 'NONICK_ERROR') {
			$('#text').text(lang['NONICK_ERROR']);
		}
		else if (obj.data == 'OK') {
			$('#text').text(_('NONICK_OK'));
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
		if (obj.data.substring(0,1) == '-') {
			update_items(obj.data);
			$('#text').text(lang['TAKE_AGAIN']);
		}
		highlight_elem('#input', '#037A00');
		reset();
		return
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
		console.log('aqui');
		$('#text').text('');
		$('#text').append('<div class="talk"></div>');
		$("#input").prop('disabled', true);
		$('#input').css('display', 'none');
		$.each(obj.talk, function(k, v) {
			$('#text .talk').append('<a id="talk-'+k+'" class="'+v.v+'" href="#">'+v.m+'</a><br />');
		});
			
	}

 	reset();

}