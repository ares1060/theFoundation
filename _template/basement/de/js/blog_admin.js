/* ---- load Funcitons ----- */
function loadList(){
	showAdminLoadingDiv();
	$('#blog_admin_action_new').removeClass('selected');
	getService('Blog', 'admin', 'args[chapter]=list&args[page]='+active_page+'&args[type]='+active_type, 
			function (msg) {
				$('#admin_blog_main_content').html(msg);
				hideAdminLoadingDiv();
			});
}

function loadNew() {
	showAdminLoadingDiv();
	$('#blog_admin_action_new').addClass('selected');
	getService('Blog', 'admin', 'args[chapter]=new', 
			function (msg) {
				$('#admin_blog_main_content').html(msg);
				hideAdminLoadingDiv();
			});
}

function loadView(id) {
	$('.info_box').remove();
	showAdminLoadingDiv();
	$('#blog_admin_action_new').removeClass('selected');
	getService('Blog', 'admin', 'args[chapter]=view&args[id]='+id, 
			function (msg) {
				$('#admin_blog_main_content').html(msg);
				hideAdminLoadingDiv();
			});
}

/* ----- switch functions ------- */
function showDescription(id){
	$('#description-'+id).hide();
	$('#admin_blog_entry-'+id).children('td').children('.desc').css('display', 'block');
}

function showLoadingDiv() {$('#loading_div').show();}
function hideLoadingDiv() {$('#loading_div').hide();}