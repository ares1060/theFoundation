/**
 * theFoundation Object
 * provides important functions like Ajaxrequests or string replaces
 * @author Matthias (scrapy1060@gmail.com)
 */
var tf = {
	loading_counter: 0,
	/**
	 * inits theFoundation system
	 * @param params 
	 * 		to_root | active path to root
	 * 		tpl_root |Êactive template root
	 * 		template |Êactive template
	 * 		login_url | url to login mask
	 */
	init: function(params) {
		this.to_root = params.to_root;
		this.tpl_root = params.tpl_root;
		this.template = params.template;
		this.login_url = params.login_url;
		this.tf_loading_counter = 0;
		
		// init interface objects
		$(document).ready(function() {
			tfcontextmenu.init();
			tfdialog.init();
			
			$(document).keydown(function(e) { tf.triggerKeyDown(e.keyCode); });
		});
	},
	/** 
	 * Gets Data from a specific service and method via connector
	 * @param name | name of the service
	 * @param method | method of the service (view, data, admin, run)
	 * @param args | args (supports arrays with up to 3 dimensions)
	 * @param handle | return handle will get the result
	 */
	getService: function(args_){
		var name = args_.service;
		var method = args_.method;
		var handle = args_.handle;
		var args = args_.args;
		var json_return = (args_.json == undefined) ? false : args_.json;

		if(this.template != undefined ) template_str = '&args[template]='+this.template;
		else template_str = '';
		
		if(typeof args == 'object' && args != null){
			args = this.array2String(args);
		}

		$.ajax({
			type: "POST",
			url: this.to_root+"_core/connector.php",
			data: 'service_name='+name+'&service_method='+method+template_str+'&to_root='+this.to_root+'&args[ajax]=true&'+args,
			success: function (answer) {
//					(answer);
				try {
					json = answer; //jQuery.parseJSON(answer);
					if(json.content != undefined){
						if(json.content == 'session_expired') document.location = this.login_url;
						if(json_return) handle(json.content);
						else handle(json.content.toString());
					}

					if(json.msg != undefined && json.msg != ''){
						$('#tf_msg').html(json.msg);
						tf.showMessages();
					} 
					
					if(json.debug != undefined && json.debug != ''){
						console.log(json.debug);
					}

				} catch (e) {
//					console.log(e);
				}
			}
		});
	},
	/**
	 * recursive function to confert multidimensional arrays to post string
	 * used by getService
	 */
	array2String: function(array, front) {
		var string = '';
		if(front == '' || front == undefined) front = '&args';
		for(var key in array){
			if(typeof array[key] == 'object' && array[key] != null){
				string += this.array2String(array[key], front+'['+key+']');
			} else {
				string += front+'['+key+']='+array[key];
			}
		}
		return string;
	},
	/**
	 * Shows Message div and hides it after some time
	 */
	showMessages: function() {
		$('#tf_msg').stop(true);
		$('#tf_msg').show().delay(10000).slideUp('slow'); 
	},
	/**
	 * shows Admin Loading div in Admincenter
	 */
	showAdminLoading: function () {
		this.tf_loading_counter++; 
		$('#admin_loading_div').fadeIn(200); 
	},
	/**
	 * hides Admin Loading div in Admincenter
	 */
	hideAdminLoading: function() {
		this.tf_loading_counter--; 
		if(this.tf_loading_counter <= 0) $('#admin_loading_div').fadeOut(700);
	},
	/**
	 * loads Settings and fills given div with return
	 * @param service | active service
	 * @param div_id | div to fill with returnes
	 */
	loadSettings: function (service, div_id) {
		tf.showAdminLoading();
		tf.getService({
			service: service, 
			method: 'admin', 
			args: { 
				chapter:'settings'
			}, 
			handle: function (msg) {
					tf.hideAdminLoading();
					$('#'+div_id).html(msg);
				}
		});
	},
	/**
	 * little string replace function 
	 * @param search | search string
	 * @param replace | string
	 * @param subject | string to replace
	 */
	str_replace: function(search, replace, subject) {
		return subject.split(search).join(replace);
	},
	createPasswordChecker: function(id){
		$('#'+id).after('<span id="password_strength-'+id+
					'" class="tf_password_strength">Password St&auml;rke:<span id="password_strength_text-'+id+
					'" class="text">&nbsp;</span></span>');
		
		$('#'+id).keyup(function() {
			tf.getService({
				service: 'TextFunctions', 
				method: 'data', 
				args: { 
					action:'getPasswordStrength',
					pwd: $(this).val()
				}, 
				handle: function (msg) {
					//console.log(msg);
					$('#password_strength_text-'+id).html(msg.averageScoreInfo);
					$('#password_strength-'+id).attr('class', msg.averageScoreInfo.split(' ').join('').toLowerCase()+' tf_password_strength');
				},
				json: true
			});
		});
	},
	keydown: {},
	tmp: undefined,
	registerKeyDown: function(keycode, listener, id){
		if(this.keydown.keycode == undefined) this.keydown['a'+keycode] = new Array();
		this.keydown['a'+keycode].push({id: id, listener:listener});
	},
	removeKeyDown: function(keycode, id) {
		if(this.keydown['a'+keycode] != undefined) {
			for(var i=0; i< this.keydown['a'+keycode].length; i++){
				if(this.keydown['a'+keycode][i] != undefined && this.keydown['a'+keycode][i].id == id) {
					this.keydown['a'+keycode][i] = undefined;
					if(this.keydown['a'+keycode].length == 0) this.keydown['a'+keycode] = undefined;
				}
			}
		}
	},
	triggerKeyDown: function(keycode) {
		if(this.keydown['a'+keycode] != undefined) {
			while( this.tmp == undefined) this.tmp = this.keydown['a'+keycode].pop();
			if(typeof this.tmp.listener == 'function') {
				this.tmp.listener();
			}
			this.tmp = undefined;
		}
	}
};

/**
 * theFoundation Adress Object
 * provides function for accessing and setting deep links
 * uses jquery deep linking plugin
 * @author Matthias (scrapy1060@gmail.com)
 */
var tfaddress = {
	params: null,
	old_params: {},
	handler_key: {},
	/**
	 * returnes Adress path names as an assosiative array
	 * adress path will be parsed as /key/value/key2/value2/...
	 */
	getPathNames: function() {
		var tmp1 = $.address.pathNames();
		
		var tmp = [];
		
		var count = tmp1.length;
		if(count % 2 != 0) count--;
		
		for(var i=0; i < count; i += 2){
			tmp[tmp1[i]] = tmp1[i+1];
		}

		return tmp;
	},
	loadParams: function () {
		if(this.params != null) this.old_params = this.params;
		this.params = this.getPathNames();
		this.checkHandler();
	},
	/**
	 * sets Path with given values
	 * will execute theFoundationAdress.editPath
	 * @param values |Êobject {key:'value', ...}
	 */
	setPath: function(values){
		this.editPath({set:values});
	},
	/**
	 * unsets Keys in Adresspath
	 * will execute theFoundationAdress.editPath
	 * @param keys | array ['key', ...]
	 */
	unsetPath: function(keys) {
		this.editPath({unset:keys});
	},
	/**
	 * edits path by given params
	 * @param param
	 * 			set:{} - all keys to be set
	 * 			unset:[] - all keys to be unset
	 * @param optional: force update
	 */
	editPath: function(param){
		tmp = this.getPathNames();
		
		if(param.unset != undefined){
			$.each(param.unset, function(key, value) {
				if(tmp[value] != undefined) tmp[value] = undefined;
			});
		}
		
		if(param.set != undefined) {
			$.each(param.set, function(key, value) {
				tmp[key] = value;
			});
		}
		if(this.editPath.arguments[1] == true)  this.updatePathFromArray(tmp, true);
		else this.updatePathFromArray(tmp);
	},
	/**
	 * reloads Adresspath 
	 */
	reloadPath: function() {
		this.updatePathFromArray(this.getPathNames(), true);
		
	},
	/**
	 * updates Adresspath to given array data
	 * @param array |Êpath data [key:value, key2:value2,...] will be translated to /key/value/key2/value2/
	 * @param optional: force update even if adress is the same
	 */
	updatePathFromArray: function(array) {
		this.tf_string = '';
		
		for(key in array){
			if(array[key] != undefined) this.tf_string = this.tf_string + '/'+key+'/'+array[key];
		}
		console.log(this.tf_string);
		var sameArray = tfutil.equals(array, this.getPathNames());
		//(sameArray);
		$.address.path(this.tf_string+'/');
		
		// if optional update flag is set and array is the same
		if(this.updatePathFromArray.arguments[1] == true && sameArray) { $.address.update();}
		
		this.loadParams();
	},
	/**
	 * returnes deep linking value
	 */
	getValue: function(name) {
		this.loadParams();
		return this.params.name;
	},
	// ============================================================================== Handler ==============================================================================
	/**
	 * @param array
	 * 		key: {
	 * 			value: function() {},
	 * 			value1: function() {}
	 * 		}
	 */
	checkHandler: function() {	
		if(this.params == null ) this.params = this.getPathNames();
		if(this.params != null && this.handler_key != null){
			for(key in this.params){
				// if change handler is set
				if(this.handler_key[key] != null && this.handler_key[key].onChange != null){
					// if value has changed run handler
					if(	(this.params_old == null) ||
						(this.params_old != null && 
						this.params[key] != null &&
						this.params_old[key] != null &&
						this.params[key] != this.params_old[key] && 
						typeof this.handler[key].onChange == 'function')) {
								
						this.handler_key[key].onChange();
					}
				}
				// if key exists
				if(this.handler_key != null && this.handler_key[key] != null && this.handler_key[key].onValue != null){
					// if key=value = handler(key, value) run handler
					if(this.handler_key[key].onValue[this.params[key]] != undefined && typeof this.handler_key[key].onValue[this.params[key]] == 'function') this.handler_key[key].onValue[this.params[key]]();
				}
			}
		}
	},
	addHandler: function(params) {
		if(params.onKey != null){
			var tmp = {}
			for(key in params.onKey){
				tmp = {};
				if(params.onKey[key].onValue != null) tmp.onValue = params.onKey[key].onValue;
				if(params.onKey[key].onChange != null) tmp.onChange = params.onKey[key].onChange;
				if(tmp != {}) this.handler_key[key] = tmp;
			}
		}	
	}
}

/**
 * context menu class
 */
var tfcontextmenu = {
	contextmenucount: 0,
	active_contextmenu: undefined,
	showMenu: function(item, mouseover) {
		if(this.active_contextmenu != undefined) this.hideActive();
		$(item).show();
		$('#contextmenu_background').show();
		this.active_contextmenu = item;
		if(mouseover) {
			$('#contextmenu_background').mouseover(function() {
				tfcontextmenu.hideActive();
			});
		} else {
			$('#contextmenu_background').click(function() {
				tfcontextmenu.hideActive();
			});
		}
		
		tf.registerKeyDown(27, function() { tfcontextmenu.hideActive(); }, 'tf_context_menu');
	},
	init: function () {
		$('body').prepend('<div id="contextmenu_background">&nbsp;</div>');
		tfcontextmenu.update();
	},
	update: function () {
		$('.tf_contextmenu').each(function () {
			if(!$(this).hasClass('generatedContextMenu')) {
				tfcontextmenu.contextmenucount++;
				
				$(this).attr('id', 'tf_contextmenu_'+tfcontextmenu.contextmenucount);
				$(this).addClass('generatedContextMenu');
				
				if($(this).hasClass('mouseover')) {
					$(this).mouseover(function() { tfcontextmenu.showMenu($(this).children('.tf_contextmenu_content'), true); });
				} else $(this).click(function() { tfcontextmenu.showMenu($(this).children('.tf_contextmenu_content'), false); });
				//console.log($(this).parent().children('.tf_contextmenu').attr('id'));
			}
		});
	},
	hideActive: function() {
		tf.removeKeyDown(27, 'tf_context_menu');
		$(this.active_contextmenu).hide();
		$('#contextmenu_background').hide();
		this.active_contextmenu = undefined;
		//$(document).unbind('keydown');
	}
}

var tfdialog = {
	init: function() {
		$('body').prepend(
			'<div id="dialog_background">'+
			'	<div id="dialog_box">'+
			'		<a id="dialog_box_close" href="javascript:void(0);"><img src="'+tf.tpl_root+'/img/services/gallery/layout/close_button.png" /></a>'+
			'		<div id="dialog_box_content">'+
			'			<div id="dialog_box_title">Title</div>'+
			'			<div id="dialog_box_text">Content</div>'+
			'		</div>'+
			'		<div class="actions">'+
			'			<button class="button" style="width: 100px;" id="dialog_box_ok"><span>OK</span></button>'+
			'			<button class="button" style="width: 100px;" id="dialog_box_cancel"><span>Abbrechen</span></button>'+
			'		</div>'+
			'	</div>'+		
			'</div>');
		$('#dialog_background').click(function() { tfdialog.close(); });
		$('#dialog_box_close').click(function() { tfdialog.close(); });
	},
	show: function(param) {
		var title = param.title;
		var text = param.text;
		var handler = param.handler;
//		var icon = param.icon;
		
		if(title != undefined && content != undefined && typeof(handler) == 'function' ){
//			if(icon != undefined) {
//				$('#dialog_box_icon').attr('src', icon);
//				$('#dialog_box_icon').show();
//			}
			$('#dialog_box_title').html(title);
			$('#dialog_box_text').html(text);
			$('#dialog_box_ok').click(function() { tfdialog.close(); handler({type:'ok'}); return false; });
			$('#dialog_box_cancel').click(function() { tfdialog.close(); handler({type:'cancel'}); return false; });
			$('#dialog_background').show();
			
			tf.registerKeyDown(27, function() { tfdialog.close(); handler({type:'cancel'}); }, 'tf_dialog');
			tf.registerKeyDown(13, function() { tfdialog.close(); handler({type:'ok'}); }, 'tf_dialog');
		}
	},
	close: function() {
		tf.removeKeyDown(13, 'tf_dialog');
		tf.removeKeyDown(27, 'tf_dialog');
		
		$('#dialog_background').hide();
		$('#dialog_box_title').text('');
		$('#dialog_box_text').text('');
		$('#dialog_box_ok').unbind('click');
		$('#dialog_box_cancel').unbind('click');
//		$('#dialog_box_icon').attr('src', ''); 
//		$('#dialog_box_icon').hide();
	}
}

/**
 * runs important functions on startup
 */
$(document).ready(function() {
	$.address.crawlable(true);

	// if init not run vars have to be defined the old way
	if(tf.to_root == undefined) {
		tf.init({
			to_root: to_root,
			tpl_root: tpl_root,
			template: template,
			login_url: login_url
		});
	}
	
	tfaddress.checkHandler();
	
	if($('#tf_msg').html() != '') tf.showMessages();
});

var tfutil = {
	equals: function(a, b) {
		var alength = Object.keys(a).length;
		var blength = Object.keys(b).length;
		if(!a || !b || alength != blength) {
			return false;
		}
		for(key in a) {
			if(b[key] !== a[key]) {
				return false;
			}
		}
		return true;
	}
}
