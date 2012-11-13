$(document).ready(function() {tf_gallery.initKeyHandler(); });

var tf_gallery = {
	activeNewFolder: undefined,
	initKeyHandler: function() {
		tf.registerKeyDown(13, tf_gallery.showEditFolder, 'gallery_enter');
//		$(document).keydown(function(e) {
//			if(e.keyCode == 27 /* esc */){
//				// do nothing
//			} else if(e.keyCode == 13 /* enter */ ){
//				tf_gallery.showEditFolder();
//			}
//		});
	},
	/* ===========  selection of images ======= */
	selectedImgs: new Array(),
	selectionLimit: -1,
	selectImg: function(id) {
//		alert('asdf');
		if($('#imageDiv_'+id).hasClass('selected')){
			$('#imageDiv_'+id).removeClass('selected');
			delete tf_gallery.selectedImgs[tf_gallery.selectedImgs.indexOf(id)];
		} else {
			console.log(tf_gallery.selectedImgs.indexOf(id));
			if(tf_gallery.selectedImgs.indexOf(id) == -1){
				$('#imageDiv_'+id).addClass('selected');
				tf_gallery.selectedImgs.push(id);

				//if selectionlimit is set and length is greater than limit -> trim selection from back
				if(tf_gallery.selectionLimit != -1 && tf_gallery.selectedImgs.length > tf_gallery.selectionLimit){
					
					// copy selected Images
					var selectedImgsTmp = tf_gallery.selectedImgs.slice(0);
					
					// trim selection					
					Array.prototype.splice.call(selectedImgsTmp, 0, (selectedImgsTmp.length -  tf_gallery.selectionLimit) );
					
					// unselect interface
					$.each(tf_gallery.selectedImgs, function(key, value){
						if(selectedImgsTmp.indexOf(value) == -1) $('#imageDiv_'+value).removeClass('selected');
					});
					
					// restore real selected images array
					tf_gallery.selectedImgs = selectedImgsTmp.slice(0);
				}
			}
		}
	},
	getSelectedImgs: function() {
		return tf_gallery.selectedImgs;
	},
	setSelectionLimit: function(limit) {
		if(limit >= -1 && limit != 0) tf_gallery.selectionLimit = limit;
	},
	/* =========  new Folder ======== */
	showNewFolder: function() {
		parent = active_folder;
		
		if(parent != undefined && parent != -1) {
			this.activeNewFolder = parent;
			tfcontextmenu.hideActive();
			$('#new_folder_'+parent).show();
			if(!$('#new_folder_'+parent).parent().is(':visible')) $('#new_folder_'+parent).parent().show();
			
			// keycodes
			tf.registerKeyDown(13, function() { 
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
			}, 'gallery_new_folder');
			tf.registerKeyDown(27, function() { tf_gallery.hideNewFolder(); }, 'gallery_new_folder');
			// ======= end keycodes

			$('#new_folder_input_'+parent).blur(function() { tf_gallery.hideNewFolder(); });
			$('#new_folder_input_'+parent).focus();
		} else {
			this.showNewAlbum();
		}
	},
	hideNewFolder: function() {
		if(this.activeNewFolder != undefined) {
			tf.removeKeyDown(13, 'gallery_new_folder');
			tf.removeKeyDown(27, 'gallery_new_folder');
			$('#new_folder_'+this.activeNewFolder).hide();
			$('#new_folder_input_'+this.activeNewFolder).val('');
			$('#new_folder_input_'+this.activeNewFolder).unbind('blur');
			this.activeNewFolder = undefined;
		}
	},
	/* =========  new Album ======== */
	showNewAlbum: function() {
		tfcontextmenu.hideActive();
		$('#new_album').show();
		
		// ======= keycodes
		tf.registerKeyDown(13, function() {
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
		}, 'gallery_new_album' );
		tf.registerKeyDown(27, function() { tf_gallery.hideNewAlbum(); }, 'gallery_new_album');
		// ======= end keycodes
		
		$('#new_album_input').blur(function() { tf_gallery.hideNewAlbum(); });
		$('#new_album_input').focus();
	},
	hideNewAlbum: function() {
		tf.removeKeyDown(13, 'gallery_new_album');
		tf.removeKeyDown(27, 'gallery_new_album');
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
		tf_gallery.editFolderOpen = true;
		$('#menu_folder_text_'+active_folder).hide();
		$('#menu_folder_input_'+active_folder).show();
		$('#menu_folder_input_'+active_folder).children('input').val($('#menu_folder_text_'+active_folder).children('.text').text());
		$('#menu_folder_input_'+active_folder).children('input').focus();
		$('#menu_folder_input_'+active_folder).children('input').select(false);
		
		// ===== keycodes
		tf.removeKeyDown(13, 'gallery_enter');
		tf.registerKeyDown(13, function() {
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
		}, 'gallery_edit_folder_enter');
		tf.registerKeyDown(27, tf_gallery.closeEditFolder, 'gallery_edit_folder_close');
		// ======= end keycodes

		$('#menu_folder_input_'+active_folder).children('input').blur(function() { tf_gallery.closeEditFolder(); })
	},
	closeEditFolder: function() {
		if(tf_gallery.editFolderOpen){
			tf.removeKeyDown(13, 'gallery_edit_folder_enter');
			tf.removeKeyDown(27, 'gallery_edit_folder_close');
			tf.registerKeyDown(13, tf_gallery.showEditFolder, 'gallery_enter');
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
	upload_folder: undefined,
	upload_album: undefined,
	upload_top: undefined,
	updateUploadFolderArrow: function() {
		if($('#menu_folder_'+active_folder).size() == 0) {
			this.upload_folder =  $('#menu_subfolder_'+active_folder);
			this.upload_album = this.upload_folder.closest('.menu_album');
			
			// update upload panel
			$('#upload_album').html(this.upload_album.find('.big_link').first().children('.text').text());
			$('#upload_folder').html(this.upload_folder.find('.big_link').first().children('.text').text());
			$('#upload_folder_input').val(active_folder);
		} else {
			this.upload_folder = $('#menu_folder_'+active_folder);
			this.upload_album = $('#menu_folder_'+active_folder);

			// update upload panel
			$('#upload_album').html(this.upload_album.find('.big_link').first().children('.text').text());
			$('#upload_folder').html('kein Unterordner');
			$('#upload_folder_input').val(active_folder);
		}		
		
		// update upload folder arrow
		this.upload_top = $('#admin_sidemenu').height()+15;
		
		$('#upload_folder_spacer').css('height', this.upload_top+'px');

		if(this.upload_folder.length > 0) {
			$('#upload_folder_indicator').css('top', this.upload_folder.children('.link_line').position().top+$('#admin_sidemenu_block').scrollTop());
			$('#admin_sidemenu').trigger('scroll');
		} else {
			$('#upload_folder_spacer').css('height', '100px');
			$('#upload_folder_indicator').css('top', '10px');
		}
	}
}