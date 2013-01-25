/*
WYSIWYG-BBCODE editor
Copyright (c) 2009, Jitbit Sotware, http://www.jitbit.com/
PROJECT HOME: http://wysiwygbbcode.codeplex.com/
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
	* Redistributions of source code must retain the above copyright
	  notice, this list of conditions and the following disclaimer.
	* Redistributions in binary form must reproduce the above copyright
	  notice, this list of conditions and the following disclaimer in the
	  documentation and/or other materials provided with the distribution.
	* Neither the name of the <organization> nor the
	  names of its contributors may be used to endorse or promote products
	  derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY Jitbit Software ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Jitbit Software BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

var wswgEditor = new function() {

	this.getEditorDoc = function (id) { return myeditor[id]; }
	this.getIframe = function (id) { return ifm[id]; }
	this.IsEditorVisible = function (od) { return editorVisible[id]; }

	var myeditor = {};
	var ifm = {};
	var body_id={}
	var textboxelement={};
	var content={};
	var textRange={};
	var editorVisibleDefault = false;
	var editorVisible = {};
	var enableWysiwygDefault = true;
	var enableWysiwyg = {};
	
	var isIE = /msie|MSIE/.test(navigator.userAgent);
	var isChrome = /Chrome/.test(navigator.userAgent);
	var isSafari = /Safari/.test(navigator.userAgent) && !isChrome;
	var browser = isIE || window.opera;

	function rep(re, str, id) {
		content[id] = content[id].replace(re, str);
	}

	this.initEditor = function (textarea_id, wysiwyg) {
		enableWysiwyg[textarea_id] = (wysiwyg != undefined) ? wysiwyg : enableWysiwygDefault;

		editorVisible[textarea_id] = editorVisibleDefault;
		
		$('#'+textarea_id).closest("form").submit(function() {wswgEditor.doCheck(textarea_id); return true;});
		
		body_id[textarea_id] = textarea_id;
		textboxelement[textarea_id] = document.getElementById(body_id[textarea_id]);
		textboxelement[textarea_id].setAttribute('class', 'editorBBCODE');
		textboxelement[textarea_id].className = "editorBBCODE";
		if (enableWysiwyg[textarea_id]) {
			if (!document.getElementById("rte_"+textarea_id)) { //to prevent recreation
				ifm[textarea_id] = document.createElement("iframe");
				ifm[textarea_id].setAttribute("id", "rte_"+textarea_id);
				$(ifm[textarea_id]).attr('style', 'width: 100%; height: auto;');
				ifm[textarea_id].setAttribute("frameBorder", "0");
//				ifm.style.width = textboxelement.style.width;
//				ifm.style.height = textboxelement.style.height;
				textboxelement[textarea_id].parentNode.insertBefore(ifm[textarea_id], textboxelement[textarea_id]);
				textboxelement[textarea_id].style.display = 'none';
			}
			if (ifm[textarea_id]) {
				InitIframe(textarea_id);
			} else
				setTimeout('InitIframe()', 100);
		}
	}

	function InitIframe(id) {
		myeditor[id] = ifm[id].contentWindow.document;
		myeditor[id].designMode = "on";
		myeditor[id].open();
		myeditor[id].write('<html><head><link href="editor.css" rel="Stylesheet" type="text/css" /></head>');
		myeditor[id].write('<body style="margin:0px 0px 0px 0px" class="editorWYSIWYG">');
		myeditor[id].write('</body></html>');
		myeditor[id].close();
		myeditor[id].body.contentEditable = true;
		ifm[id].contentEditable = true;
		if (myeditor[id].attachEvent) {
			myeditor[id].attachEvent("onkeypress", function() {kp(id);});
		}
		else if (myeditor[id].addEventListener) {
			myeditor[id].addEventListener("keypress", function() {kp(id);}, true);
		}
		wswgEditor.ShowEditor(id);
	}

	this.ShowEditor = function (id) {
		if (!enableWysiwyg[id]) return;
		editorVisible[id] = true;
		content[id] = document.getElementById(body_id[id]).value;
		bbcode2html(id);
		myeditor[id].body.innerHTML = content[id];
	}

	this.SwitchEditor = function (id) {
		if (editorVisible[id]) {
			this.doCheck(id);
			ifm[id].style.display = 'none';
			textboxelement[id].style.display = '';
			editorVisible[id] = false;
			textboxelement[id].focus();
		}
		else {
			if (enableWysiwyg[id] && ifm[id]) {
				ifm[id].style.display = '';
				textboxelement[id].style.display = 'none';
				this.ShowEditor(id);
				editorVisible[id] = true;
				ifm[id].contentWindow.focus();
			}
		}
	}

	function html2bbcode(id) {
//		console.log(content[id]);
		rep(/<img\s[^<>]*?src=\"?([^<>]*?)\"?(\s[^<>]*)?\/?>/gi, "[img]$1[/img]", id);
		rep(/<\/(strong|b)>/gi, "[/b]", id);
		rep(/<(strong|b)(\s[^<>]*)?>/gi, "[b]", id);
		rep(/<\/(em|i)>/gi, "[/i]", id);
		rep(/<(em|i)(\s[^<>]*)?>/gi, "[i]", id);
		rep(/<\/u>/gi, "[/u]", id);
		rep(/\n/gi, " ", id);
		rep(/\r/gi, " ", id);
		rep(/<u(\s[^<>]*)?>/gi, "[u]", id);
		rep(/<div><br(\s[^<>]*)?>/gi, "<div>", id); //chrome-safari fix to prevent double linefeeds
		rep(/<br(\s[^<>]*)?>/gi, "\n", id);
		rep(/<p(\s[^<>]*)?>/gi, "", id);
		rep(/<\/p>/gi, "\n", id);
		rep(/<ul>/gi, "[list]", id);
		rep(/<\/ul>/gi, "[/list]", id);
		rep(/<ol>/gi, "[ol]", id);
		rep(/<\/ol>/gi, "[/ol]", id);
		rep(/<li>/gi, "[*]", id);
		rep(/<\/li>/gi, "[/*]", id);
		rep(/<\/div>\s*<div([^<>]*)>/gi, "</span>\n<span$1>", id); //chrome-safari fix to prevent double linefeeds
		rep(/<div([^<>]*)>/gi, "\n<span$1>", id);
		rep(/<\/div>/gi, "</span>\n", id);

		rep(/<br\/>/gi, "[br]\n", id);

		rep(/<table([^<>]*)>/gi, "[table]", id);
		rep(/<\/table>/gi, "[/table]", id);
		rep(/<tr([^<>]*)>/gi, "[tr]", id);
		rep(/<tr([^<>]*)>/gi, "[tr]", id);
		rep(/<td([^<>]*)>/gi, "[td]", id);
		rep(/<td([^<>]*)>/gi, "[td]", id);

		rep(/&nbsp;/gi, " ", id);
		rep(/&quot;/gi, "\"", id);
		rep(/&amp;/gi, "&", id);

		//remove style & script tags
		rep(/<script.*?>[\s\S]*?<\/script>/gi, "", id);
		rep(/<style.*?>[\s\S]*?<\/style>/gi, "", id);

		//remove [if] blocks (when pasted from outlook etc)
		rep(/<!--\[if[\s\S]*?<!\[endif\]-->/gi, "", id);
//		console.log(content[id]);

		var sc, sc2;
		do {
			sc = content[id];
			rep(/<font\s[^<>]*?color=\"?([^<>]*?)\"?(\s[^<>]*)?>([^<>]*?)<\/font>/gi, "[color=$1]$3[/color]", id);
			if (sc == content[id])
				rep(/<font[^<>]*>([^<>]*?)<\/font>/gi, "$1", id);
			rep(/<a\s[^<>]*?href=\"?([^<>]*?)\"?(\s[^<>]*)?>([^<>]*?)<\/a>/gi, "[url=$1]$3[/url]", id);
			sc2 = content[id];
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-weight: ?bold;?\"?\s*([^<]*?)<\/\1>/gi, "[b]<$1 style=$2</$1>[/b]", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-weight: ?normal;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-style: ?italic;?\"?\s*([^<]*?)<\/\1>/gi, "[i]<$1 style=$2</$1>[/i]", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-style: ?normal;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?text-decoration: ?underline;?\"?\s*([^<]*?)<\/\1>/gi, "[u]<$1 style=$2</$1>[/u]", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?text-decoration: ?none;?\"?\s*([^<]*?)<\/\1>/gi, "<$1 style=$2</$1>", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?color: ?([^<>]*?);\"?\s*([^<]*?)<\/\1>/gi, "[color=$2]<$1 style=$3</$1>[/color]", id);
			rep(/<(span|blockquote|pre)\s[^<>]*?style=\"?font-family: ?([^<>]*?);\"?\s*([^<]*?)<\/\1>/gi, "[font=$2]<$1 style=$3</$1>[/font]", id);
			rep(/<(blockquote|pre)\s[^<>]*?style=\"?\"? (class=|id=)([^<>]*)>([^<>]*?)<\/\1>/gi, "<$1 $2$3>$4</$1>", id);
			rep(/<pre>([^<>]*?)<\/pre>/gi, "[code]$1[/code]", id);
			rep(/<span\s[^<>]*?style=\"?\"?>([^<>]*?)<\/span>/gi, "$1", id);
			if (sc2 == content[id]) {
				rep(/<span[^<>]*>([^<>]*?)<\/span>/gi, "$1", id);
				sc2 = content[id];
			}
		} while (sc != content[id])
		rep(/<[^<>]*>/gi, "", id);
		rep(/&lt;/gi, "<", id);
		rep(/&gt;/gi, ">", id);

		do {
			sc = content[id];
			rep(/\[(b|i|u)\]\[quote([^\]]*)\]([\s\S]*?)\[\/quote\]\[\/\1\]/gi, "[quote$2][$1]$3[/$1][/quote]", id);
			rep(/\[color=([^\]]*)\]\[quote([^\]]*)\]([\s\S]*?)\[\/quote\]\[\/color\]/gi, "[quote$2][color=$1]$3[/color][/quote]", id);
			rep(/\[(b|i|u)\]\[code\]([\s\S]*?)\[\/code\]\[\/\1\]/gi, "[code][$1]$2[/$1][/code]", id);
			rep(/\[color=([^\]]*)\]\[code\]([\s\S]*?)\[\/code\]\[\/color\]/gi, "[code][color=$1]$2[/color][/code]", id);
		} while (sc != content[id])

		//clean up empty tags
		do {
			sc = content[id];
			rep(/\[b\]\[\/b\]/gi, "", id);
			rep(/\&nbsp\;/gi, " ", id);
			rep(/\[i\]\[\/i\]/gi, "", id);
			rep(/\[u\]\[\/u\]/gi, "", id);
			rep(/\[quote[^\]]*\]\[\/quote\]/gi, "", id);
			rep(/\[code\]\[\/code\]/gi, "", id);
			rep(/\[url=([^\]]+)\]\[\/url\]/gi, "", id);
			rep(/\[img\]\[\/img\]/gi, "", id);
			rep(/\[color=([^\]]*)\]\[\/color\]/gi, "", id);
		} while (sc != content[id])
//			console.log(content[id]);
	}

	function bbcode2html(id) {
//		console.log(content[id]);
		// example: [b] to <strong>
		rep(/\</gi, "&lt;", id); //removing html tags
		rep(/\>/gi, "&gt;", id);

		rep(/\[ul\]/gi, "<ul>", id);
		rep(/\[list\]/gi, "<ul>", id);
		rep(/\[\/ul\]/gi, "</ul>", id);
		rep(/\[\/list\]/gi, "</ul>", id);
		rep(/\[ol\]/gi, "<ol>", id);
		rep(/\[\/ol\]/gi, "</ol>", id);
		rep(/\[li\]/gi, "<li>", id);
		rep(/\[\*\]/gi, "<li>", id);
		rep(/\[\/li\]/gi, "</li>", id);
		rep(/\[\/\*\]/gi, "</li>", id);

		rep(/\[table\]/gi, "<table border='1'>", id);
		rep(/\[\/table\]/gi, "</table>", id);
		rep(/\[tr\]/gi, "<tr>", id);
		rep(/\[\/tr\]/gi, "</tr>", id);
		rep(/\[td\]/gi, "<td>", id);
		rep(/\[\/td\]/gi, "</td>", id);

		rep(/\n/gi, "<br />", id);
		rep(/\[br\/\]/gi, "<br />\n", id);
		rep(/\[br\]/gi, "<br />\n", id);

		rep(/\[sub\]/gi, "<sub>", id);
		rep(/\[\/sub\]/gi, "</sub>", id);
		rep(/\[sup\]/gi, "<sup>", id);
		rep(/\[\/sup\]/gi, "</sup>", id);

		rep(/\[p\]/gi, "<p>", id);
		rep(/\[\/p\]/gi, "</p>", id);
		
		if (browser) {
			rep(/\[b\]/gi, "<strong>", id);
			rep(/\[\/b\]/gi, "</strong>", id);
			rep(/\[i\]/gi, "<em>", id);
			rep(/\[\/i\]/gi, "</em>", id);
			rep(/\[u\]/gi, "<u>", id);
			rep(/\[\/u\]/gi, "</u>", id);
		} else {
			rep(/\[b\]/gi, "<span style=\"font-weight: bold;\">", id);
			rep(/\[i\]/gi, "<span style=\"font-style: italic;\">", id);
			rep(/\[u\]/gi, "<span style=\"text-decoration: underline;\">", id);
			rep(/\[\/(b|i|u)\]/gi, "</span>", id);
		}
		rep(/\[img\]([^\"]*?)\[\/img\]/gi, "<img src=\"$1\" />", id);
		var sc;
		do {
			sc = content[id];
			rep(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/gi, "<a href=\"$1\">$2</a>", id);
			rep(/\[url\]([\s\S]*?)\[\/url\]/gi, "<a href=\"$1\">$1</a>", id);
			if (browser) {
				rep(/\[color=([^\]]*?)\]([\s\S]*?)\[\/color\]/gi, "<font color=\"$1\">$2</font>", id);
				rep(/\[font=([^\]]*?)\]([\s\S]*?)\[\/font\]/gi, "<font face=\"$1\">$2</font>", id);
			} else {
				rep(/\[color=([^\]]*?)\]([\s\S]*?)\[\/color\]/gi, "<span style=\"color: $1;\">$2</span>", id);
				rep(/\[font=([^\]]*?)\]([\s\S]*?)\[\/font\]/gi, "<span style=\"font-family: $1;\">$2</span>", id);
			}
			rep(/\[code\]([\s\S]*?)\[\/code\]/gi, "<pre>$1</pre>&nbsp;", id);
		} while (sc != content[id]);
	}

	this.doCheck = function (id) {
		if (!enableWysiwyg[id]) return;
		if (!editorVisible[id]) {
			this.ShowEditor(id);
		}
		content[id] = myeditor[id].body.innerHTML;
		html2bbcode(id);
		document.getElementById(body_id[id]).value = content[id];
	}

	function stopEvent(evt) {
		evt || window.event;
		if (evt.stopPropagation) {
			evt.stopPropagation();
			evt.preventDefault();
		} else if (typeof evt.cancelBubble != "undefined") {
			evt.cancelBubble = true;
			evt.returnValue = false;
		}
		return false;
	}

	this.doQuote = function (id) {
		if (editorVisible[id]) {
			ifm[id].contentWindow.focus();
			if (isIE) {
				textRange = ifm[id].contentWindow.document.selection.createRange();
				var newTxt = "[quote=]" + textRange.text + "[/quote]";
				textRange[id].text = newTxt;
			}
			else {
				var edittext = ifm[id].contentWindow.getSelection().getRangeAt(0);
				var original = edittext.toString();
				edittext.deleteContents();
				edittext.insertNode(ifm[id].contentWindow.document.createTextNode("[quote=]" + original + "[/quote]"));
			}
		}
		else {
			AddTag('[quote=]', '[/quote]', id);
		}
	}

	function kp(e, id) {
		if (isIE) {
			if (e.keyCode == 13) {
				var r = myeditor[id].selection.createRange();
				if (r.parentElement().tagName.toLowerCase() != "li") {
					r.pasteHTML('<br/>');
					if (r.move('character'))
						r.move('character', -1);
					r.select();
					stopEvent(e);
					return false;
				}
			}
		}
	}
	this.InsertYoutube = function (id) {
		this.InsertText(" http://www.youtube.com/watch?v=XXXXXXXXXXX ", id);
	}
	this.InsertText = function (txt, id) {
		if (editorVisible[id])
			insertHtml(txt, id);
		else
			textboxelement[id].value += txt;
	}

	this.doClick = function (command, id) {
		if (editorVisible[id]) {
			ifm[id].contentWindow.focus();
			myeditor[id].execCommand(command, false, null);
		}
		else {
			switch (command) {
				case 'bold':
					AddTag('[b]', '[/b]', id); break;
				case 'italic':
					AddTag('[i]', '[/i]', id); break;
				case 'underline':
					AddTag('[u]', '[/u]', id); break;
				case 'InsertUnorderedList':
					AddTag('[ul][li]', '[/li][/ul]', id); break;
			}
		}
	}

	function doColor(color, id) {
		ifm[id].contentWindow.focus();
		if (isIE) {
			textRange[id] = ifm[id].contentWindow.document.selection.createRange();
			textRange[id].select();
		}
		myeditor[id].execCommand('forecolor', false, color);
	}

	this.doLink = function (id) {
		if (editorVisible[id]) {
			ifm[id].contentWindow.focus();
			var mylink = prompt("Enter a URL:", "http://");
			if ((mylink != null) && (mylink != "")) {
				if (isIE) { //IE
					var range = ifm[id].contentWindow.document.selection.createRange();
					if (range.text == '') {
						range.pasteHTML("<a href='" + mylink + "'>" + mylink + "</a>");
					}
					else
						myeditor[id].execCommand("CreateLink", false, mylink);
				}
				else if (window.getSelection) { //FF
					var userSelection = ifm[id].contentWindow.getSelection().getRangeAt(0);
					if (userSelection.toString().length == 0)
						myeditor[id].execCommand('inserthtml', false, "<a href='" + mylink + "'>" + mylink + "</a>");
					else
						myeditor[id].execCommand("CreateLink", false, mylink);
				}
				else
					myeditor[id].execCommand("CreateLink", false, mylink);
			}
		}
		else {
			AddTag('[url=', ']click here[/url]', id);
		}
	}
	this.doImage = function () {
//		if (editorVisible) {
//			ifm.contentWindow.focus();
//			myimg = prompt('Enter Image URL:', 'http://');
//			if ((myimg != null) && (myimg != "")) {
//				myeditor.execCommand('InsertImage', false, myimg);
//			}
//		}
//		else {
//			AddTag('[img]', '[/img]');
//		}
		//TODO: do Image
		console.log('TODO: image');
	}

	function insertHtml(html, id) {
		ifm[id].contentWindow.focus();
		if (isIE)
			ifm[id].contentWindow.document.selection.createRange().pasteHTML(html);
		else
			myeditor[id].execCommand('inserthtml', false, html);
	}

	//textarea-mode functions
	function MozillaInsertText(element, text, pos) {
		element.value = element.value.slice(0, pos) + text + element.value.slice(pos);
	}

	function AddTag(t1, t2, id) {
		var element = textboxelement[id];
		console.log(id);
		if (isIE) {
			if (document.selection) {
				element.focus();

				var txt = element.value;
				var str = document.selection.createRange();

				if (str.text == "") {
					str.text = t1 + t2;
				}
				else if (txt.indexOf(str.text) >= 0) {
					str.text = t1 + str.text + t2;
				}
				else {
					element.value = txt + t1 + t2;
				}
				str.select();
			}
		}
		else if (typeof (element.selectionStart) != 'undefined') {
			var sel_start = element.selectionStart;
			var sel_end = element.selectionEnd;
			MozillaInsertText(element, t1, sel_start);
			MozillaInsertText(element, t2, sel_end + t1.length);
			element.selectionStart = sel_start;
			element.selectionEnd = sel_end + t1.length + t2.length;
			element.focus();
		}
		else {
			element.value = element.value + t1 + t2;
		}
	}

	//=======color picker
	function getScrollY() { var scrOfX = 0, scrOfY = 0; if (typeof (window.pageYOffset) == 'number') { scrOfY = window.pageYOffset; scrOfX = window.pageXOffset; } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) { scrOfY = document.body.scrollTop; scrOfX = document.body.scrollLeft; } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) { scrOfY = document.documentElement.scrollTop; scrOfX = document.documentElement.scrollLeft; } return scrOfY; }

	document.write("<style type='text/css'>.colorpicker201{visibility:hidden;display:none;position:absolute;background:#FFF;z-index:999;filter:progid:DXImageTransform.Microsoft.Shadow(color=#D0D0D0,direction=135);}.o5582brd{padding:0;width:12px;height:14px;border-bottom:solid 1px #DFDFDF;border-right:solid 1px #DFDFDF;}a.o5582n66,.o5582n66,.o5582n66a{font-family:arial,tahoma,sans-serif;text-decoration:underline;font-size:9px;color:#666;border:none;}.o5582n66,.o5582n66a{text-align:center;text-decoration:none;}a:hover.o5582n66{text-decoration:none;color:#FFA500;cursor:pointer;}.a01p3{padding:1px 4px 1px 2px;background:whitesmoke;border:solid 1px #DFDFDF;}</style>");

	function getTop2() { csBrHt = 0; if (typeof (window.innerWidth) == 'number') { csBrHt = window.innerHeight; } else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) { csBrHt = document.documentElement.clientHeight; } else if (document.body && (document.body.clientWidth || document.body.clientHeight)) { csBrHt = document.body.clientHeight; } ctop = ((csBrHt / 2) - 115) + getScrollY(); return ctop; }
	var nocol1 = "&#78;&#79;&#32;&#67;&#79;&#76;&#79;&#82;",
clos1 = "X";

	function getLeft2() { var csBrWt = 0; if (typeof (window.innerWidth) == 'number') { csBrWt = window.innerWidth; } else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) { csBrWt = document.documentElement.clientWidth; } else if (document.body && (document.body.clientWidth || document.body.clientHeight)) { csBrWt = document.body.clientWidth; } cleft = (csBrWt / 2) - 125; return cleft; }

	//function setCCbldID2(val, textBoxID) { document.getElementById(textBoxID).value = val; }
	//function setCCbldID2(val, id) { if (editorVisible[id]) doColor(val); else AddTag('[color=' + val + ']', '[/color]', id); }

	function setCCbldSty2(objID, prop, val) {
		switch (prop) {
			case "bc": if (objID != 'none') { document.getElementById(objID).style.backgroundColor = val; }; break;
			case "vs": document.getElementById(objID).style.visibility = val; break;
			case "ds": document.getElementById(objID).style.display = val; break;
			case "tp": document.getElementById(objID).style.top = val; break;
			case "lf": document.getElementById(objID).style.left = val; break;
		}
	}

	this.putOBJxColor2 = function (Samp, pigMent, textBoxId) { if (pigMent != 'x') { setCCbldID2(pigMent, textBoxId); setCCbldSty2(Samp, 'bc', pigMent); } setCCbldSty2('colorpicker201', 'vs', 'hidden'); setCCbldSty2('colorpicker201', 'ds', 'none'); }

	this.showColorGrid2 = function (Sam, textBoxId) {
		var objX = new Array('00', '33', '66', '99', 'CC', 'FF');
		var c = 0;
		var xl = '"' + Sam + '","x", "' + textBoxId + '"'; var mid = '';
		mid += '<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 0px #F0F0F0;padding:2px;"><tr>';
		mid += "<td colspan='9' align='left' style='margin:0;padding:2px;height:12px;' ><input class='o5582n66' type='text' size='12' id='o5582n66' value='#FFFFFF'><input class='o5582n66a' type='text' size='2' style='width:14px;' id='o5582n66a' onclick='javascript:alert(\"click on selected swatch below...\");' value='' style='border:solid 1px #666;'></td><td colspan='9' align='right'><a class='o5582n66' href='javascript:onclick=wswgEditor.putOBJxColor2(" + xl + ")'><span class='a01p3'>" + clos1 + "</span></a></td></tr><tr>";
		var br = 1;
		for (o = 0; o < 6; o++) {
			mid += '</tr><tr>';
			for (y = 0; y < 6; y++) {
				if (y == 3) { mid += '</tr><tr>'; }
				for (x = 0; x < 6; x++) {
					var grid = '';
					grid = objX[o] + objX[y] + objX[x];
					var b = "'" + Sam + "','" + grid + "', '" + textBoxId + "'";
					mid += '<td class="o5582brd" style="background-color:#' + grid + '"><a class="o5582n66"  href="javascript:onclick=wswgEditor.putOBJxColor2(' + b + ');" onmouseover=javascript:document.getElementById("o5582n66").value="#' + grid + '";javascript:document.getElementById("o5582n66a").style.backgroundColor="#' + grid + '";  title="#' + grid + '"><div style="width:12px;height:14px;"></div></a></td>';
					c++;
				}
			}
		}
		mid += "</tr></table>";
		//var ttop=getTop2();
		//setCCbldSty2('colorpicker201','tp',ttop);
		//document.getElementById('colorpicker201').style.left=getLeft2();
		document.getElementById('colorpicker201').innerHTML = mid;
		setCCbldSty2('colorpicker201', 'vs', 'visible');
		setCCbldSty2('colorpicker201', 'ds', 'inline');
	}

}