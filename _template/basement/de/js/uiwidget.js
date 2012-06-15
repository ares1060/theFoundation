var counter, allcounter; //counter for max_uploads and allcounter for ids

function addUpload() {
	if(!counter) counter=1;
	if(!allcounter) allcounter=1;
	if(counter < max_uploads){
		counter++;
		allcounter++;
		getService('UIWidgets', 'view', 'args[widget]=FileUploadInput&args[name]=files[]&args[id]=upload_'+allcounter, appendUpload);
	}
}
var plus, minus, div, pluslink, minuslink;

function loadFTPUploads() {
	getService('UIWidgets', 'view', 'args[action]=FTPFiles&args[name]=files[]&args[id]=upload_'+allcounter+'&args[type]='+ftp_types, function (msg) {
		$('#upload_form_ftp_files').html(msg);
	});
}

function appendUpload(msg){
	//create Div
	div = document.createElement("div");
	div.setAttribute("id", "upload_"+allcounter);
	div.setAttribute("class", "upload_div");
	
	$(div).html(msg)
	
	//create links
	pluslink = document.createElement("a");
	pluslink.setAttribute("href", "javascript:addUpload()");
	
	minuslink = document.createElement("a");
	minuslink.setAttribute("href", "javascript:delElem('upload_"+allcounter+"')");
	
	//create Plus image
	plus = document.createElement("img");
	plus.setAttribute("src",  tpl_root+'/img/action/add.png');
	plus.setAttribute("class", "img");
	
	//create minus link
	minus = document.createElement("img");
	minus.setAttribute("src", tpl_root+'/img/action/del.png');
	minus.setAttribute("class", "img");
	
	pluslink.appendChild(plus);
	minuslink.appendChild(minus);
	
	div.appendChild(pluslink);
	div.appendChild(minuslink);
	
	$('#upload_add').append(div);
}

function delElem(id) {
	$('#'+id).remove();
	counter--;
} 

/* --- Switch ---- */
$('.button-better').toggle(function(){
	$(this).delay(200).animate({"marginLeft" : "49px"}, 30);
}, function(){
	$(this).delay(200).animate({"marginLeft" : "-1px"}, 30);
}); 