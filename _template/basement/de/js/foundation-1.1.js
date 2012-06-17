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
						handle(json.content.toString());
					}
					if(json.msg != undefined && json.msg != ''){
						$('#tf_msg').html(json.msg);
						tf.showMessages();
					} 
					
					if(json.debug != undefined && json.debug != ''){
						console.log(json.debug);
					}
				} catch (e) {
					console.log(e);
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
		$('#tf_msg').show().delay(10000).hide('slow'); 
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
		
		this.updatePathFromArray(tmp);
	},
	/**
	 * reloads Adresspath 
	 */
	reloadPath: function() {
		this.updatePathFromArray(this.getPathNames());
		
	},
	/**
	 * updates Adresspath to given array data
	 * @param array |Êpath data [key:value, key2:value2,...] will be translated to /key/value/key2/value2/
	 */
	updatePathFromArray: function(array) {
		this.tf_string = '';
		
		for(key in array){
			if(array[key] != undefined) this.tf_string = this.tf_string + '/'+key+'/'+array[key];
		}
		
		$.address.path(this.tf_string+'/');
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
});