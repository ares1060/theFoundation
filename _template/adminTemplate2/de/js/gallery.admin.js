$(document).ready(function() {tf_gallery.initKeyHandler(); });

var tf_gallery = {
	activeNewFolder: undefined,
	initKeyHandler: function() {
		$(document).keydown(function(e) {
			if(e.keyCode == 27 /* esc */){
				// do nothing
			} else if(e.keyCode == 13 /* enter */ ){
				tf_gallery.showEditFolder();
			}
		});
	},
	/* ===========  selection of images ======= */
	selectImg: function(id) {
		if($('#imageDiv_'+id).hasClass('selected')){
			$('#imageDiv_'+id).removeClass('selected');
		} else {
			$('#imageDiv_'+id).addClass('selected');
		}
	},
	/* =========  new Folder ======== */
	showNewFolder: function() {
		parent = active_folder;
		
		if(parent != undefined && parent != -1) {
			this.activeNewFolder = parent;
			tfcontextmenu.hideActive();
			$('#new_folder_'+parent).show();
			if(!$('#new_folder_'+parent).parent().is(':visible')) $('#new_folder_'+parent).parent().show();
			$('#new_folder_input_'+parent).keydown(function(key) {
				console.log(key.which);
				if (key.which == 13) { /* enter */
					// send new folder
					tf.showAdminLoading();
					tf.getService({
						service: 'Gallery', 
						method: 'admin', 
						args: { 
							action: 'new_folder',
							name: $('#new_folder_input_'+parent).val(),
							parent: parent,
							noMsg: true
						}, 
						handle: function (msg) {
							location.reload();
						}
					});
				} else if(key.which == 27) { /* esc */
					tf_gallery.hideNewFolder();
				}
			});
			$('#new_folder_input_'+parent).blur(function() { tf_gallery.hideNewFolder(); });
			$('#new_folder_input_'+parent).focus();
		} else {
			this.showNewAlbum();
		}
	},
	hideNewFolder: function() {
		if(this.activeNewFolder != undefined) {
			$('#new_folder_'+this.activeNewFolder).hide();
			$('#new_folder_input_'+this.activeNewFolder).val('');
			$('#new_folder_input_'+this.activeNewFolder).unbind('keydown');
			$('#new_folder_input_'+this.activeNewFolder).unbind('blur');
			this.activeNewFolder = undefined;
		}
	},
	/* =========  new Album ======== */
	showNewAlbum: function() {
		tfcontextmenu.hideActive();
		$('#new_album').show();
		$('#new_album_input').keydown(function(key) {
			console.log(key.which);
			if (key.which == 13) { /* enter */
				// send new folder
				tf.showAdminLoading();
				tf.getService({
					service: 'Gallery', 
					method: 'admin', 
					args: { 
						action: 'new_album',
						name: $('#new_album_input').val(),
						noMsg: true
					}, 
					handle: function (msg) {
						location.reload();
					}
				});
			} else if(key.which == 27) { /* esc */
				tf_gallery.hideNewAlbum();
			}
		});
		$('#new_album_input').blur(function() { tf_gallery.hideNewAlbum(); });
		$('#new_album_input').focus();
	},
	hideNewAlbum: function() {
		$('#new_album').hide();
		$('#new_album_input').val('');
		$('#new_album_input').unbind('keydown');
		$('#new_album_input').unbind('blur');
	},
	/* =========  SHOW HIDE ======== */
	showFolder: function() {
		tf.showAdminLoading();
		tf.getService({
			service: 'Gallery', 
			method: 'admin', 
			args: { 
				action: 'show_folder',
				id: active_folder,
				noMsg: true
			}, 
			handle: function (msg) {
				location.reload();
			}
		});
	},
	hideFolder: function() {
		tf.showAdminLoading();
		tf.getService({
			service: 'Gallery', 
			method: 'admin', 
			args: { 
				action: 'hide_folder',
				id: active_folder,
				noMsg: true
			}, 
			handle: function (msg) {
				location.reload();
			}
		});
	},
	showEditFolder: function() {
		tfcontextmenu.hideActive();
		this.editFolderOpen = true;
		$('#menu_folder_text_'+active_folder).hide();
		$('#menu_folder_input_'+active_folder).show();
		$('#menu_folder_input_'+active_folder).children('input').val($('#menu_folder_text_'+active_folder).children('.text').text());
		$('#menu_folder_input_'+active_folder).children('input').focus();
		$('#menu_folder_input_'+active_folder).children('input').select(false);
		$('#menu_folder_input_'+active_folder).children('input').keydown(function(key) {
			if (key.which == 13) { /* enter */
				// send new folder
				if($('#menu_folder_input_'+active_folder).children('input').val() != $('#menu_folder_text_'+active_folder).children('.text').text()) {
					tf.showAdminLoading();
					tf.getService({
						service: 'Gallery', 
						method: 'admin', 
						args: { 
							action: 'rename_folder',
							id: active_folder,
							name: $('#menu_folder_input_'+active_folder).children('input').val(),
							noMsg: true
						}, 
						handle: function (msg) {
							location.reload();
						}
					});
				} else {
					tf_gallery.closeEditFolder();
				}
			} else if(key.which == 27) { /* esc */
				tf_gallery.closeEditFolder();
			}
		});
		$('#menu_folder_input_'+active_folder).children('input').blur(function() { tf_gallery.closeEditFolder(); })
	},
	closeEditFolder: function() {
		if(this.editFolderOpen){
			this.editFolderOpen = false;
			$('#menu_folder_text_'+active_folder).show();
			$('#menu_folder_input_'+active_folder).hide();
		}
	},
	/* =========  DELETE ======== */
	deleteFolder: function() {
		if(active_folder != undefined){
			tfdialog.show({
				title: 'Ordner l&ouml;schen',
				text: 'Wollen sie den ausgew&auml;hlten Ordner wirklich l&ouml;schen?<br />Alle Unterordner und Bilder werden aus der Datenbank gel&ouml;scht.',
				handler: function(result) {
					if(result.type == 'ok'){
						tf.showAdminLoading();
						tf.getService({
							service: 'Gallery', 
							method: 'admin', 
							args: { 
								action: 'delete_folder',
								id: active_folder,
								noMsg: true
							}, 
							handle: function (msg) {
								location.href = $.address.baseURL()+'/';
							}
						});
						return false;
					} else {
						// do nothing
						return false;
					}
				}
			});
		}
	},
	/* ================ UPLOAD ====== */
	updateUploadFolderArrow: function() {
		var folder = ($('#menu_folder_'+active_folder).size() == 0) ? $('#menu_subfolder_'+active_folder) : $('#menu_folder_'+active_folder);
		
		var top = $('#admin_sidemenu').height()+15;
		
		$('#upload_folder_spacer').css('height', top+'px');

		if(folder.length > 0) {
			$('#upload_folder_indicator').css('top', folder.children('.link_line').position().top+$('#admin_sidemenu_block').scrollTop());
			$('#admin_sidemenu').trigger('scroll');
		} else {
			$('#upload_folder_spacer').css('height', '100px');
			$('#upload_folder_indicator').css('top', '10px');
		}
	}
}