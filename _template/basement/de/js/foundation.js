/* ===================================== OLD functions ================================*/
/** 
 * depricated
 * @use tf.getService
 */
function getService(name, method, args, handle){
	tf.getService({
		name:name,
		method:method,
		args:args,
		handle:handle
	});
}
/**
 * Depricated
 * @use tf.str_replace
 */
function str_replace(search, replace, subject) {
	return tf.str_replace(search, replace, subject);
}

/**
 * Returnes an Array with every param from the insite links (#)
 */
var tf_param_array;
var tf_params;
var tf_i;
var tf_string;


/** deprecated @use tfadress.reloadPath */
function reloadAdressPath() {
	tfadress.reloadPath();
}
/** deprecated @use tfadress.getPathNames */
function getAdressPathNames() {
	return tfadress.getPathNames();
}

/** deprecated @use tfadress.editAdressPath */
function editAdressPath(params){
	tfadress.editPath(params);
}

/**
 * Adds or Updates a value in the inside links (#)
 * @depricated: use editAdressPath instead
 * @param key
 * @param value
 */
function setAdressPathKey(key, value) {

	tf_param_array = getAdressPathNames();
	
	tf_param_array[key] = value;
	
	updateAdressPathFromArray(tf_param_array);
}

/**
 * Adds or Updates a value in the inside links (#)
 * @depricated: use editAdressPath instead
 * @param key
 * @param value
 */
function setAdressPathKeys(key, value) {
	if(key.length == value.length){
		tf_param_array = getAdressPathNames();
		
		for(var i = 0; i < key.length; i++){
			tf_param_array[key[i]] = value[i];
		}
		
		updateAdressPathFromArray(tf_param_array);
	}
}
/**
 * Adds or Updates a value in the inside links (#) 
 * @depricated: use editAdressPath instead
 * @param json string
 */
function setAdressPathKeysJSON(json) {
	editAdressPath({set:json});
}

/**
 * unsets names given as array
 * @depricated: use editAdressPath instead
 * @param names
 */
function unsetAdressPath(names){
	tf_param_array = getAdressPathNames();

	for(key in names){
		if(tf_param_array[names[key]] != undefined) tf_param_array[names[key]] = undefined;
	}
	
	updateAdressPathFromArray(tf_param_array);
}

/**
 * depricated
 * @use tfadress.updatePathFromArray
 */
function updateAdressPathFromArray(array){
	tfadress.updatePathFromArray(array);
}

/**
 * Will show the Messages if some Messages should be displayed
 * and inits the dopdown_background div
 */
$(document).ready(function () {
	if($('#comments_msg').html() != '') $('#comments_msg').show();
	
	if($('#tf_msg').html() != '') {
		showTFMsgs();
	}
});

/**
 * Depricated
 * @use tf.showMessages
 */
function showTFMsgs() {
	tf.showMessages();
}

/**
 * Shows and hides the Loading Div in the admin Interface
 */
function showAdminLoadingDiv() { tf.showAdminLoading(); }
function hideAdminLoadingDiv() { tf.hideAdminLoading(); }

/**
 * 
 */
function showPasswordStrength(pwd, id){
	getService('TextFunctions', 'data', 'args[action]=getPasswordStrength&args[pwd]='+pwd, 
			function (msg) {
				$('#'+id).css('width', (msg)+'0%');
				
				if(msg == 0) text = '';
				if(msg == 1) text = 'Sehr Schwach';
				if(msg == 2) text = 'Ziemlich Schwach';
				if(msg == 3) text = 'Schwach';
				if(msg == 4) text = 'Mittel';
				if(msg == 5) text = 'OK';
				if(msg == 6) text = 'Gut';
				if(msg == 7) text = 'Ziemlich Gut';
				if(msg == 8) text = 'Sehr Gut';
				if(msg == 9) text = 'Fast Perfekt';
				if(msg == 10) text = 'Perfekt';
				
				$('#'+id+'_text').html(text);
	});
}

/**
 * Shows a dropdown
 * @param id
 */
var activeDropdown;
function tf_showDropdown(id) {
	activeDropdown = id;
	$('body').click(tf_toggleDropDown);
}

/**
 * Hides active Dropdown
 */
function tf_toggleDropDown() {
	if($('#'+activeDropdown).css('display') != 'none') {
		$('body').unbind('click');
		$('#'+activeDropdown).hide();
		activeDropdown = undefined;
	} else {
		$('#'+activeDropdown).show();
	}
}