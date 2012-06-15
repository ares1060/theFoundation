var open = '';
var menu_open = '';
var context_open = false;
var imageLoading;

/* ---- context menu functions ------- */
function loadContextContent(menu) {
	context_open = true;
	var id = menu.attr('id').substr(menu.attr('id').search('-')+1, menu.attr('id').length);

	//if($('#album_div > .image.selected').size() <= 1){ // version 1 just if no or ine image is selected move selection to me
	if(!$('#image-'+id).hasClass('selected')){
		$('#album_div > .image.selected').each(function () {$(this).removeClass('selected');});
		$('#image-'+id).addClass('selected');
	}
	
	var menu_base = '';
	
	if($('#image-'+id+' > .img_wrapper_main > .img_wrapper > .offline').size() == 1) menu_base = '<!--<li class="context_online"><a href="javascript:void(0);" onclick="setImageOnline('+id+');">set online</a></li>-->';
	else menu_base = '<!--<li class="context_offline"><a href="javascript:void(0);" onclick="setImageOffline('+id+');">set offline</a></li>-->';
	
	menu_base = menu_base +	'<!--<li class="context_shop"><a href="javascript:void(0);" onclick="toggleShop('+id+');">Add to shop</a></li>-->'+
							'<li class="context_delete"><a href="javascript:void(0);" onclick="deleteImage('+id+');">delete</a></li>';
	
	if($('#album_div > .image.selected').size() > 1){
		menu_base = menu_base+'<li class="seperator">&nbsp;</li>'+
					'<!--<li class="context_offline"><a href="javascript:void(0);" onclick="setImagesOffline();">set '+$('#album_div > .image.selected').size()+' images offline</a></li>'+
					'<li class="context_online"><a href="javascript:void(0);" onclick="setImagesOnline();">set '+$('#album_div > .image.selected').size()+' images online</a></li>//-->'+
					'<li class="context_delete"><a href="javascript:void(0);" onclick="deleteImages();">delete '+$('#album_div > .image.selected').size()+' images</a></li>';
	}
	menu.html(menu_base);
}
function contextClosed() {context_open = false;}

/* --- delete functions --- */
function deleteImages() {
	loading= true;
	var id=0;
	$('#album_div > .image.selected').each(function () {
		id = $(this).attr('id').substr($(this).attr('id').search('-')+1, $(this).attr('id').length);
		deleteImage(id);
		$(this).remove();
	});
}

function deleteImage(id){
	getService('Gallery', 'run', 'args[action]=delete_image&args[ajax]=true&args[id]='+id, 
			function (msg) {
				if(msg == 'true') getService('Messages', 'view', 'args[action]=div&args[msg][type]=1&args[msg][msg]={message}', function (msg) {$('#gallery_messages').append(msg.replace(/{message}/g, delete_success_msg));});
				else getService('Messages', 'view', 'args[action]=div&args[msg][type]=0&args[msg][msg]={message}', function (msg) {$('#gallery_messages').append(msg.replace(/{message}/g, delete_error_msg));});
				if($('#album_div > .image.selected').size() == 1) loadAlbum(active_album);
			});
}

function deleteAlbum(id){
	getService('Gallery', 'run', 'args[action]=delete_album&args[ajax]=true&args[id]='+id, 
			function (msg) {
				if(msg == 'true') {
					getService('Messages', 'view', 'args[action]=div&args[msg][type]=1&args[msg][msg]={message}', function (msg) {$('#gallery_messages').append(msg.replace(/{message}/g, delete_success_msg_album));});
					$('#album_sidebar-'+id).remove();
					loadFirstAlbum();
				} else getService('Messages', 'view', 'args[action]=div&args[msg][type]=1&args[msg][msg]={message}', function (msg) {$('#gallery_messages').append(msg.replace(/{message}/g, delete_error_msg_album));});
				
			});
}

/* --- menu functions for loading the content from server --- */
function loadBase() {
	
}
function openUpload() {
	//if(open != '' || menu_open != '') closeAll();
	if(menu_open == 'upload_action') closeAll();
	else {
		showAdminLoadingDiv();
		var album = '';
		if(active_album != -1) album = '&args[album]='+active_album;
		getService('Gallery', 'admin', 'args[action]=upload&args[ajax]=true&args[link]='+link+album, 
				function (msg) {
					$('#upload_div').html(msg);
					hideAdminLoadingDiv();
				});
		$('#gallery_overlay').show();
		$('#upload_ul').show();
		
		menu_open = 'upload_action';
		open = 'upload_ul';
		
		$('#'+menu_open).addClass('selected');
	}
}

function openNewAlbum() {
	//if(open != '' || menu_open != '') closeAll();
	if(menu_open == 'new_album_action') closeAll();
	else {
		showAdminLoadingDiv();
		getService('Gallery', 'admin', 'args[action]=new_album&args[ajax]=true', 
				function (msg) {
					$('#new_album_div').html(msg);
					hideAdminLoadingDiv();
				});
		$('#gallery_overlay').show();
		$('#new_album_ul').show();
		
		menu_open = 'new_album_action';
		open = 'new_album_ul';
		
		$('#'+menu_open).addClass('selected');
	}
}

function openNewFolder() {
	//if(open != '' || menu_open != '') closeAll();
	if(menu_open == 'new_album_action') closeAll();
	else {
		showAdminLoadingDiv();
		getService('Gallery', 'admin', 'args[action]=new_folder&args[ajax]=true', 
				function (msg) {
					$('#new_album_div').html(msg);
					hideAdminLoadingDiv();
				});
		$('#gallery_overlay').show();
		$('#new_album_ul').show();
		
		menu_open = 'new_folder_action';
		open = 'new_folder_ul';
		
		$('#'+menu_open).addClass('selected');
	}
}

function openEdit() {
	//if(open != '' || menu_open != '') closeAll();
	if(menu_open == 'edit_action') closeAll();
	else {
		if($('#album_div > .image.selected').size() == 1){
			showAdminLoadingDiv();
			var id= $('#album_div > .image.selected').attr('id');
			getService('Gallery', 'admin', 'args[action]=edit_image&args[ajax]=true&args[id]='+(id.substr(6, id.length)), 
					function (msg) {
						$('#edit_action_div').html(msg);
						hideAdminLoadingDiv();
					});
			$('#gallery_overlay').show();
			$('#edit_action_ul').show();
			
			menu_open = 'edit_action';
			open = 'edit_action_ul';
			
			$('#'+menu_open).addClass('selected');
			alert();
		} else {
			showAdminLoadingDiv();
			$('#edit_action_div').html(loading_div);
			if(active_image == -1){
				//album
				getService('Gallery', 'admin', 'args[action]=edit_album&args[ajax]=true&args[id]='+active_album, 
					function (msg) {
						$('#edit_action_div').html(msg);
						hideAdminLoadingDiv();
					});
			} else {
				getService('Gallery', 'admin', 'args[action]=edit_image&args[ajax]=true&args[id]='+active_image, 
						function (msg) {
							$('#edit_action_div').html(msg);
							hideAdminLoadingDiv();
						});
			}
			$('#gallery_overlay').show();
			$('#edit_action_ul').show();
			
			menu_open = 'edit_action';
			open = 'edit_action_ul';
			
			$('#'+menu_open).addClass('selected');
		}
	}
}

function openDelete() {
	if(menu_open == 'delete_action') closeAll();
	else {
		if(active_image > 0) {
			$('#delete_action_album').hide();
			$('#delete_action_selected_images').hide();
			$('#delete_action_image').show();
		} else {
			$('#delete_action_image').hide();
			if($('#album_div > .image.selected').size() == 0) $('#delete_action_selected_images').hide();
			else $('#delete_action_selected_images').show();
			$('#delete_action_album').show();
		}
		
		menu_open = 'delete_action';
		open = 'delete_action_ul';
		$('#gallery_overlay').show();
		$('#delete_action_ul').show();
		$('#delete_action').addClass('selected');
	}
}
/* --- other functions ---- */
function closeAll() {
	if(open != ''){
		$('#'+open).hide();
		$('#gallery_overlay').hide();
		$('#'+menu_open).removeClass('selected');
		open = '';
		menu_open = '';
	} else $('#gallery_overlay').hide();
}

function loadAlbum(id){
	$('#album_sidebar-'+active_album).parent().children('li').removeClass('selected');
	$('#album_sidebar-'+id).addClass('selected');
	showAdminLoadingDiv();
	active_album = id;
	active_image = -1;
	//alert(id);
	getService('Gallery', 'admin', 'args[action]=get_album&args[id]='+id+'&args[link]='+link+'?album='+active_album+'&args[page]='+active_page, 
			function (msg) {
				$('#gallery_subcontent').html(msg);
				hideAdminLoadingDiv();
			});
}

function loadFolder(id){
	$('#album_sidebar-'+active_album).parent().children('li').removeClass('selected');
	$('#album_sidebar-'+id).addClass('selected');
	showAdminLoadingDiv();
	active_album = id;
	active_image = -1;
	//alert(id);
	getService('Gallery', 'admin', 'args[action]=get_folder&args[id]='+id+'&args[link]='+link+'?album='+active_album+'&args[page]='+active_page, 
			function (msg) {
				$('#gallery_subcontent').html(msg);
				hideAdminLoadingDiv();
			});
}

function loadFirstAlbum () {
	var id = $('#gallery_sidebar > ul > li.header > ul').children(':first').attr('id');
    if(id != undefined) $.address.path('/album/'+id.substr(id.search('-')+1, id.length));
    $('#loading_div').hide();
}

function loadImage(id){
	if(!imageLoading) {
		imageLoading=true;
		$('#album_sidebar-'+active_album).parent().children('li').removeClass('selected');
		$('#album_sidebar-'+active_album).addClass('selected');
		showAdminLoadingDiv();
		getService('Gallery', 'admin', 'args[action]=get_image&args[id]='+id+'&args[link]='+link+'&args[album]='+active_album+'&args[page]='+active_page, 
				function (msg) {
					$('#gallery_subcontent').html(msg);
					hideAdminLoadingDiv();
					imageLoading=false;
				});
		active_image = id;
	}
}

/* --- needed ajax functions --- */
function close_upload_form () {closeAll();}
function close_new_album_form () {closeAll();}
function close_edit_album_form () {closeAll();}
function close_edit_image_form () {closeAll();}
