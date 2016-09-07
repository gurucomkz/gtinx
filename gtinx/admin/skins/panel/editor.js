//			GTTBBtn			//

function GTTBBtn(parent,inivals,persistent){this.persistent=(!persistent)?false:true;this.parent = parent;this.icon=[-1,-1];for(var x in inivals)this[x] = inivals[x];}
GTTBBtn.prototype.getEditable = function(){return this.editor().getEditable();}
GTTBBtn.prototype.getToolBar = function(){return this.editor().getToolBar();}
GTTBBtn.prototype.wrap = function(a,b){this.editor().wrap(a); }
GTTBBtn.prototype.cmd = function(a,b){if(this.o.disabled) return; this.editor().cmd(a,b); this.editor().editorOnChangeVisual.call(this.editor().oEditable); }
GTTBBtn.prototype.fire = function(){this.command();}
GTTBBtn.prototype.editor = function(){return this.parent;}
GTTBBtn.prototype.applyTo = function(o){
	this.o = o;
	o.title = this.name;
	$(o).css({border: '1px solid Gray',width:'28px',height:'28px', margin:'0',padding:'0'});
	this.bcnt = $$.c('div');
	$$.aC(o,this.bcnt);
	if(this.text)
		this.bcnt.innerHTML = this.text;
	if(this.icon[0]>=0&&this.icon[1]>=0){
		this.bcnt.style.background = 'no-repeat url(/gtinx/admin/skins/panel/images/icons.gif) -'+(this.icon[0]*20)+'px -'+(this.icon[1]*20)+'px';
		this.bcnt.style.lineHeight = '20px';
		this.bcnt.style.width = '20px';
		this.bcnt.style.height = '20px';
		if(this.text){
		
		}
	}
	var me = this;
	$(o).bind('click',{b:me},function(b){b.data.b.fire(); return false;});
}
;
//////////////////////////////
/*private*/ function isTextNode(node) {return node.nodeType==3;}
/*private*/ function rightPart(node, ix) {return node.splitText(ix);}
/*private*/ function leftPart(node, ix) {node.splitText(ix);return node;}
/*private*/ function w3_getContaining(filter) {var range=window.getSelection().getRangeAt(0),container = range.commonAncestorContainer;	return getAncestor(container, filter);}
/*private*/ function ie_getContaining(filter) {var selection = window.document.selection;if (selection.type=="Control") {var range = selection.createRange();if (range.length==1) { var elem = range.item(0); }else { return null; }} else {var range = selection.createRange();var elem = range.parentElement();}return getAncestor(elem, filter);} 
/*private*/ function ie_overwriteWithNode(node) {var rng = window.document.selection.createRange();var marker = writeMarkerNode(rng);marker.appendChild(node);marker.removeNode();}
/*private*/ function w3_overwriteWithNode(node) {var rng=window.getSelection().getRangeAt(0);rng.deleteContents();if(isTextNode(rng.startContainer)){var refNode=rightPart(rng.startContainer,rng.startOffset);refNode.parentNode.insertBefore(node,refNode);}else{if(rng.startOffset==rng.startContainer.childNodes.length){refNode.parentNode.appendChild(node);} else{var refNode=rng.startContainer.childNodes[rng.startOffset];refNode.parentNode.insertBefore(node,refNode);}}}
/*private*/ function writeMarkerNode(rng){var id=window.document.uniqueID,html="<span id='"+id+"'></span>";rng.pasteHTML(html);var node=window.document.getElementById(id);return node;}

var getContaining = typeof (window.getSelection) != "undefined"?w3_getContaining:ie_getContaining;
var overwriteWithNode = typeof (window.getSelection) != "undefined"?w3_overwriteWithNode:ie_overwriteWithNode;


function GTEditor(inivals){
	this.U = this;
	this.state=0; /*0 for disabled, 1 for inited, 2 for built, 3 for run*/
	this.swState=1;/*1 for html, 2 for code, 3 for combined */
	this.DM='/gtinx/templates/.general/';
	this.H=document.getElementsByTagName('head')[0]; /*header element*/
	this.D; /*body element*/
	ie=$$.UA.ie;
	this.oDialog=null;
	this.oEditable=null;
	this.oEditableCode=null;
	this.oToolbar=null;
	this.O=[];
	this.uniqCounter=0;
	this.curname='';
	
	this.buttonGroups = {
					"std":{items:{
								bold: 		new GTTBBtn(this,{name:"Bold",icon:[0,0],command:function(){this.cmd('Bold');}}),
								italic: 	new GTTBBtn(this,{name:"Italic",icon:[3,0],command:function(){this.cmd('Italic');}}),
								underline: 	new GTTBBtn(this,{name:"Underline",icon:[7,0],command:function(){this.cmd('Underline');}}),
								striketh: 	new GTTBBtn(this,{name:"Strikethrough",icon:[6,0],command:function(){this.cmd('Strikethrough');}}),
								ulist: 		new GTTBBtn(this,{name:"Unordered List",icon:[1,0],command:function(){this.cmd('InsertUnorderedList');}}),
								olist: 		new GTTBBtn(this,{name:"Ordered List",icon:[4,0],command:function(){this.cmd('InsertOrderedList');}}),
								hr: 		new GTTBBtn(this,{name:"Horizontal Ruler",icon:[18,0],command:function(){this.cmd('InsertHorizontalRule');}}),
								jleft: 		new GTTBBtn(this,{name:"Justify Left",icon:[23,0],command:function(){this.cmd('JustifyLeft');}}),
								jcenter:	new GTTBBtn(this,{name:"Justify Center",icon:[21,0],command:function(){this.cmd('JustifyCenter');}}),
								jright: 	new GTTBBtn(this,{name:"Justify Right",icon:[24,0],command:function(){this.cmd('JustifyRight');}}),
								indent: 	new GTTBBtn(this,{name:"Indent",icon:[20,0],command:function(){this.cmd('Indent');}}),
								outdent: 	new GTTBBtn(this,{name:"Outdent",icon:[27,0],command:function(){this.cmd('Outdent');}}),
								}
						},
					"switches":{items:{
								swhtml: 	new GTTBBtn(this,{name:"Switch to HTML view",icon:[13,0],command:function(){this.editor().wsToHTML();}},true),
								swvisual: 	new GTTBBtn(this,{name:"Switch to VISUAL view",icon:[5,1],command:function(){this.editor().wsToVisual();}},true),
								swcombined: new GTTBBtn(this,{name:"Switch to COMBO view",icon:[0,2],command:function(){this.editor().wsToCombined();}},true),
								}
						},
					};
	if(!inivals.parent) {alert ('No parent set') ;}
	else {
		if(typeof(inivals.parent)=='string')
			this.oDialog = $$._(inivals.parent);
		else
			this.oDialog = inivals.parent;
	}
	for(var x in inivals)
		this[x] = inivals[x];
	
	function _init (o){
		if(o.state) return;
		if(o.D=document.body){
			$$.wCSS("/gtinx/admin/skins/panel/editor.css");
			$$.wJS(o.DM+"js/base64.js");
			o.state=1;
		}else
			setTimeout( function(){_init(o);}, 13 );
	}
	_init(this);
}

GTEditor.prototype.buildToolBar = function()
{
	this.oToolbar = $$.c('div');
	this.oToolbar.className='GTEditorToolbar ';
	this.oToolbar.style.border="1px solid Silver";
	$$.aC(this.oDialog,this.oToolbar);
	for(var x in this.buttonGroups)
	{
		var bGx = this.buttonGroups[x];
		if(typeof(bGx)=='function')continue;
		bGx.o = $$.c('div');
		bGx.o.className='ui-widget-header ui-corner-all';
		bGx.o.style.display='inline-block';
		$$.aC(this.oToolbar,bGx.o);
		for(var y in bGx.items)
		{
			var bGy = bGx.items[y];
			if(typeof(bGy)=='function')continue;
			bGy.o = $$.c('button');
			bGy.applyTo(bGy.o);
			$$.aC(bGx.o,bGy.o);
		}
	}
	this.checkActiveButtons();
}

GTEditor.prototype.editorFieldsCSS = function (){
	$(this.oEditable).css({width:"99%",height:this.swState==1?"90%":"45%", display: this.swState!=2?"block":'none',"float":"left",background:"White",border:"3px ridge black"});
	$(this.oEditableCode).css({width:"99%",height:this.swState==2?"90%":"45%", display: this.swState!=1?"block":'none',"float":"left",background:"White",border:"3px ridge black"});
}

GTEditor.prototype.editorOnChangeHTML = function (){
	//oEditable.innerHTML = htmlspecialchars_decode(this.innerHTML);
	var html = this.value,pos1=0,pos2=0,post=html,pre,block;
	this.U.O = [];
	//find PHP && components
	while(-1!=(pos1=html.search(/\<\?/))){
		pre = html.substring(0,pos1);
		post = html.substring(pos1+2);
		var btype='php';
		
		pos2 = post.search(/\?\>/);
		if(pos2>=0){
			block = post.substring(0,pos2);
			post = post.substring(pos2+2);
		} else {
			block = post.substring(0,pos2);
			post = '';
		}
		var rx = /<[^>]*$/i;
		if(rx.test(pre)){
			btype = 'phpmix';
			pos1 = pre.lastIndexOf('<');
			block = pre.substring(pos1+1)+"<?"+block;
			pre = html.substring(0,pos1);
			
			pos2 = post.indexOf('>')+block.length+2;
			post = block+"?>"+post; //get block back
			if(pos2>=0){
				block = post.substring(0,pos2);
				post = post.substring(pos2+1);
			} else {
				block = post.substring(0,pos2);
				post = '';
			}
		}else{
			block = block.replace(/^php\s/i,''); //fix <?php ?> variant
			//test if has component
				var ctest = block.replace(/^\s+/i,''),ct1,ct2,cpre,cpost;
				if(-1!=(ct1 = ctest.search(/^\$APP-\>/)))
				{
					cpre=ctest.substring(6);
					if(-1!=(ct1 = cpre.search(/^IncludeComponent/i))){
						cpre=cpre.substring(16);
						btype = 'comp';
						
					}
				}
		}
		// generate code
		var newid = $$.uniqueID();
		this.U.O.push({id:newid,type:btype,code:block,U:this.U,N:this});
		html = pre+'<img src="'+this.U.DM+'/images/gtei_'+btype+'.gif" style="border:0;"  onclick="GTEditor.prototype.objclick.call(this)" title="'+btype+' Block" id="'+newid+'" width="36" height="16" />'+post;
		//alert([pre,block,post]);
		//break;
	}
	
	var htmlParts = [
	{b:/\<!--/, e: /--\>/, bl: 4, el: 3, title:"HTML Comment",name:'htmlc'},
	{b:/\<style/, e: /<\/style>/, bl: 6, el: 8, title:"CSS STYLE",name:'style'},
	{b:/\<script/, e: /<\/script>/, bl: 7, el: 9, title:"Javascript",name:'script'}
	];
	//find html comments
	for(var hx in htmlParts)
		while(-1!=(pos1=html.search(htmlParts[hx].b)))
		{
			pre = html.substring(0,pos1);
			post = html.substring(pos1+htmlParts[hx].bl);
			
			pos2 = post.search(htmlParts[hx].e);
			if(pos2>=0){block = post.substring(0,pos2);post = post.substring(pos2+htmlParts[hx].el);} else {block = post.substring(0,pos2);post = '';}
			
			var newid = $$.uniqueID();
			this.U.O.push({id:newid,type:htmlParts[hx].name,code:block,U:this.U,N:this});
			html = pre+'<img src="'+this.U.DM+'/images/gtei_'+htmlParts[hx].name+'.gif" style="border:0;" onclick="GTEditor.prototype.objclick.call(this)" title="'+htmlParts[hx].title+'" id="'+newid+'" width="36" height="16" />'+post;
		}
	
	this.U.oEditable.contentDocument.body.innerHTML = html;
	
	//map php, components, comments
	for(var x in this.U.O){
		if(typeof this.U.O[x]=='function') continue;
		var oBlock = this.U.O[x]; 
		//$('#'+oBlock.id).attr('title',oBlock.block);
		
		$(this.U.oEditable.contentDocument.body).find('#'+oBlock.id).bind('click',{o:oBlock},this.U._BlockClick);
		$(this.U.oEditable.contentDocument.body).find('#'+oBlock.id).bind('dblclick',{o:oBlock},this.U._BlockDblClick);
		
	}
	
	//$(this.U.oEditable.contentDocument.body).find('img').bind('click',function(){this.U.});
	//map images, tables, embeds, objects
	
	
	
	//
	if(this.U.replaceNode){
		switch(this.U.replaceNode.nodeName){
			case 'TEXTAREA': 
			case 'INPUT': this.U.replaceNode.value = this.U.oEditableCode.value; break;
			default: this.U.replaceNode.innerHTML = this.U.oEditableCode.value;
		}
	}
}
GTEditor.prototype.objclick = function(){
alert(this);
}
var objclick = function(){
alert(this);
}

//////        Block Icons action dispatchers      //////
GTEditor.prototype._BlockClick = function(msg){
	var o = msg.data.o;
	switch(o.type){
		case 'comp': 
			$.ajax({
				type:"POST",
				url: "/gtinx/direct.php?", 
				context: o.U, 
				cache: false, 
				success: o.U._drawComponentProps,
				data: "mod=editlib&act=getcompopts&code="+encodeURIComponent(o.code)+'&id='+o.id,
				error:	o.U._drawComponentPropsFailed,
				dataType:'json'});
		break;
		case 'phpmix': 
		case 'php': 
		case 'htmlc':  
			
	}
}

GTEditor.prototype._drawComponentProps = function(data){
	var l = '';
	for(var x in data) if(typeof data[x]=='string') l += x+' = '+Base64.decode(data[x])+'\n';
	var propDlg = GTPanel.openDialog({width:400,title:'Свойства компонента'});
	propDlg.setContent(l);
}
GTEditor.prototype._BlockDblClick = function(msg){
	var o = msg.data.o;
	//alert("someday i will open editor dialog"); 
	switch(o.type){
		case 'php': break;
		case 'comp': break;
		case 'htmlc': break;
	}
}

/////////////////////////////////////////////////////

GTEditor.prototype.editorOnChangeVisual = function(){
	var pres={php:"<?",comp:"<?",htmlc:"<!--",phpmix:'<'},posts={php:"?>",comp:"?>",htmlc:"-->",phpmix:'>'};
	var code = this.contentDocument.body.innerHTML;
	code = code.replace(/<\?[^?]+\?>|<html[^>]*>|<\/html>|<html\/>|<!DOCTYPE[^>]+>/g, '');
	code = code.replace(/ ?\/>/g, ' />');
	
	for(var x in this.U.O){
		var y = this.U.O[x];
		var patt1=new RegExp("<img ([^>]*)id=\""+y.id+"\"([^>]*)>");
		code = code.replace(patt1,pres[y.type]+y.code+posts[y.type]);
	}
	this.U.oEditableCode.value = code;
	if(this.U.replaceNode){
		switch(this.U.replaceNode.nodeName){
			case 'TEXTAREA': 
			case 'INPUT': this.U.replaceNode.value = code; break;
			default: this.U.replaceNode.innerHTML = code;
		}
	}
	//try to keep scroll
}
GTEditor.prototype._writeContent = function(data) {
	if(!data || data =='')data='&nbsp;';
	this.oEditableCode.value = data;
	var me = this;
	window.setTimeout(function(){me.editorOnChangeHTML.call(me.oEditableCode);},100);
	//(); //make visual version
}
GTEditor.prototype._getPageContent = function(data) {
	//alert(data);
	//return;
	if(data.message)
		alert(Base64.decode(data.message));
	this._writeContent(Base64.decode(data.CONTENT));
	//var me = this;
	//window.setTimeout(function(){me.editorOnChangeHTML.call(me.oEditableCode);},100);
}
function ifronLoad (){
	if(this.U.gtLoaded) return; //avoid double fire in IE
	this.U.gtLoaded = true;
	this.contentDocument.designMode = 'on';
	var me = this;
	this.contentDocument.innerHTML = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' + 
					'<html><head xmlns="http://www.w3.org/1999/xhtml">'+
					'</head><body id="body' + this.U.oEditable.id + '" class="gteContentBody">&nbsp;</body></html>';
	//this.contentDocument.body.onkeyup = this.U.editorOnChangeVisual;
	//$(this.contentDocument.documentElement).bind('change', function(){alert("change");});
	//$(this.contentDocument).bind('layoutcomplete', function(){alert("layoutcomplete");});
	//$(this.contentDocument).bind('select', function(){alert("select");});
	$(this.contentDocument).bind('keyup', function(){me.U.editorOnChangeVisual.call(me)});
	//this.U._writeContent();
	if(this.U.replaceNode)
	switch(this.U.replaceNode.nodeName){
		case 'TEXTAREA': this.U._writeContent(this.U.replaceNode.value); break;
		case 'INPUT': this.U._writeContent(this.U.replaceNode.value); break;
		default: this.U._writeContent(this.U.replaceNode.innerHTML);
	}
	//this.contentDocument.body.onkeypress = this.U.editorOnChangeVisual;
}
GTEditor.prototype.buildEditor = function(){
	
	if(this.state<2) {
		this.buildToolBar();
		this.oEditable = $$.c('iframe','gteditorview');
		this.oEditable.id = 'edifr_'+$$.uniqueID();
		this.oEditable.onload = ifronLoad;
		this.oEditable.U = this;
		this.oEditableCode = $$.c('textarea','gteditorhtml');
		this.oEditableCode.onkeyup = this.editorOnChangeHTML;
		this.oEditableCode.U = this;
		this.editorFieldsCSS();
		$$.aC(this.oDialog,this.oEditable);
		$$.aC(this.oDialog,this.oEditableCode);
		//this.oEditable.contentEditable='true';
		this.oEditable.focus();
	}
	if(this.replaceNode){
		
		this.oDialog.style.height = '400px';
		this.replaceNode.style.display='none';
		this.oDialog.style.display='block';
		
	}else if(this.curname){
		$.ajax({
			url: "/gtinx/direct.php?mod=editlib&act=getpagecnt&fname="+encodeURIComponent(this.curname)+(this.lang?'&lang='+this.lang:''), 
			context: this, 
			cache: false, 
			success:this._getPageContent,
			dataType:'json'});
	}
	this.state=2;
}

GTEditor.prototype.checkActiveButtons = function(){
	for(var x in this.buttonGroups)
	{
		var bGx = this.buttonGroups[x];
		if(typeof(bGx)=='function')continue;
		for(var y in bGx.items)
		{
			var bGy = bGx.items[y];
			if(typeof(bGy)=='function')continue;
			if(!bGy.persistent){
				bGy.o.disabled = this.swState==2?true:false;
			}
		}
	}
}
GTEditor.prototype.wsToCombined = function(){this.swState=3;this.editorFieldsCSS();this.checkActiveButtons();}
GTEditor.prototype.wsToHTML		= function(){this.swState=2;this.editorFieldsCSS();this.checkActiveButtons();}
GTEditor.prototype.wsToVisual	= function(){this.swState=1;this.editorFieldsCSS();this.checkActiveButtons();},
GTEditor.prototype.wrap			= function(n){var o=$$.c(n); o.innerHTML= overwriteWithNode();}
GTEditor.prototype.cmd			= function(n,p){if(!p) { this.oEditable.contentDocument.execCommand(n,false,"");}else{ this.oEditable.contentDocument.execCommand(n,false,p);} this.oEditable.focus();}
GTEditor.prototype.getAncestor 	= function(elem, filter) {while (elem!=this.oEditable) {if (filter(elem)) return elem;elem = elem.parentNode;}return null;}
GTEditor.prototype.createFilter = function(tagName) {return function(elem){ return elem.tagName==tagName; }}
GTEditor.prototype.getEditable 	= function(){return this.oEditable;}
GTEditor.prototype.getToolBar 	= function(){return this.oToolbar;}
GTEditor.prototype.save 		= function(){
	//disable all dialog controls
	
	//do some saving via ajax
	
	$.ajax({
		type:"POST",
		url: "/gtinx/direct.php?", 
		context: this, 
		cache: false, 
		success:this._setPageContent,
		data: "mod=editlib&act=setpagecnt&fname="+encodeURIComponent(this.curname)+(this.lang?'&lang='+this.lang:'')+"&fcnt="+encodeURIComponent(this.oEditableCode.value),
		error:	this._setPageContentFailed,
		dataType:'json'});
	
}
GTEditor.prototype.close = function(){
	if(this.replaceNode){
		switch(this.replaceNode.nodeName){
			case 'TEXTAREA': 
			case 'INPUT': this.replaceNode.value = this.oEditableCode.value; break;
			default: this.replaceNode.innerHTML = this.oEditableCode.value;
		}
		this.oDialog.style.display='none';
		this.replaceNode.style.display='block';
	}
}
	
GTEditor.prototype.open = function(){
	if(this.state){
		this.buildEditor();
	}else alert(this.state);
}

GTEditor.prototype._setPageContent = function(data){
	
	//on success - close
	this.close();
	
	//reload page
	window.location=window.location;
}					
		
GTEditor.prototype._setPageContentFailed = function(r,s,e){
	alert("failed to save");
	//display error
	
	//reenable controls

}			
GTEditor.prototype._drawComponentPropsFailed = function(r,s,e){
	alert("failed to fetch comps");
	//display error
	
	//reenable controls

}	