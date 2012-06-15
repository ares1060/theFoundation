function loadAlbum(id) {
	showLoadingDiv();
	getService('Gallery', 'view', 'args[action]=album&args[ajax]=true&args[id]='+id+'&args[page]='+active_page, 
			function (msg) {
				$('#gallery_main').html(msg);
				hideLoadingDiv();
			});
}

function loadImage(id) {
	showLoadingDiv();
	getService('Gallery', 'view', 'args[action]=image&args[ajax]=true&args[id]='+id+'&args[page]='+active_page+'&args[album]='+active_album, 
			function (msg) {
				$('#gallery_main').html(msg);
				hideLoadingDiv();
			});
}

function loadMain() {
	showLoadingDiv();
	getService('Gallery', 'view', 'args[action]=albums&args[ajax]=true', 
			function (msg) {
				$('#gallery_main').html(msg);
				hideLoadingDiv();
			});
}

/* --- helper functions --- */
function showLoadingDiv() {$('#loading_div').show();}
function hideLoadingDiv() {$('#loading_div').hide();}