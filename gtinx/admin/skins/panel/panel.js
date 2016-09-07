/*
 * framework
*/
function gtLib(_a){
	this.UA = { ie: /MSIE/.test(navigator.userAgent)};
	this.H=document.getElementsByTagName('head')[0];
	this.uniqCounter=0;
}
gtLib.prototype.c = function(_t,_n){var _o=document.createElement(_t);if(_n)_o.className=_n;return _o;}
gtLib.prototype.i = function(_t,_n){var _o=document.createElement(this.UA.ie?'<'+_t+(_n?' name="'+_n+'"':'')+'>':_t);if(!this.UA.ie&&_n)_o.name=_n;return _o;}
gtLib.prototype.aC = function(_p,_c){_p.appendChild(_c);}
gtLib.prototype._ = function (_i){return document.getElementById(_i);}
gtLib.prototype.uniqueID = function(){return "gtuniq"+(++this.uniqCounter);}
gtLib.prototype.atoi = function(str) {return parseInt(str);}
gtLib.prototype.css = function(_o,_a,_b) {if(typeof _o=='string') _o = this._(_o);if(_b){_o.style[_a]=_b;}else{if(typeof _a!='string'){for(var x in _a)_o.style[x]=_a[x];}else return _o.style[_a];}}
gtLib.prototype.wJS = function(ad){var w=this.c("script");w.charset="utf-8";w.src=ad;this.aC(this.H.firstChild,w);}
gtLib.prototype.wCSS = function(p){var A=this.c("link");A.rel="stylesheet";A.href=p;this.H.insertBefore(A,this.H.firstChild);}
gtLib.prototype.uncacheID = function(){var d=new Date(); return d.getTime();}
gtLib.prototype.htmlencode = function (text){var chars=["&", "<", ">", '"', "'"],repl=["&amp;","&lt;","&gt;","&quot;","'"];for (var i=0; i<chars.length; i++){var re=new RegExp(chars[i], "gi");if(re.test(text)){text = text.replace(re, repl[i]);}}return text;}
gtLib.prototype.htmldecode = function (text){var repl=["&","<",">",'"',"'"],chars=["&amp;","&lt;","&gt;","&quot;","'"];for (var i=0; i<chars.length; i++){var re=new RegExp(chars[i],"gi");if(re.test(text)){text = text.replace(re, repl[i]);}}return text;}

var $$ = new gtLib();






function docScroll(){return  self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);}
function docScrollV(){return  self.pageXOffset || (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);}
function __BMscroll(){

	// $_('_footer').style.bottom = px(0-docScroll()); 
}

function atoi(str) {return parseInt(str);}
function $_(id){return document.getElementById(id);}
function GTdialogSetContent(h){
	this.innerHTML = h;
	this.style.backgroundImage='';
}
(function(){
	var U,O={},D,b=document.getElementsByTagName('head')[0],ie=/MSIE/.test(navigator.userAgent),DM='/gtinx/templates/.general/';
	
	function bindEvents(){
		$$._('panelcont').ondblclick = U.switchType;
		$$._('panelmini').ondblclick = U.switchType;
	}
	
	U = 
	{
		openDialog:function(params){
			if(!params)params = {};
			var oDialog = $$.c('div'),myparams={ autoOpen: false, width: 700, height: 500, modal:true,
					buttons:{ "OK": function() { $(this).dialog("close"); }}};
			$$.aC(D,oDialog);
			oDialog.id = 'editor';
			oDialog.title = 'Редактор страницы';
			if(params.title) oDialog.title=params.title;
			if(params){
				for(var x in params){
					if(typeof params[x]!='function')
						myparams[x] = params[x];
				}
			}
			oDialog.style.background='center center no-repeat url(/gtinx/admin/skins/panel/images/dialog-progress.gif)';
			oDialog.setContent = GTdialogSetContent;
			$(oDialog).dialog(myparams);
			$(oDialog).dialog('open');
			return oDialog;
		},
		newFileWizard:function(dir,lang)
		{
			var params={"dir":dir};
			if(lang)params.lang = lang;
			var oDialog = this.openDialog({title: 'Новая страница'});
			params.parent=oDialog;
			params.curname=params.fname;
			var oE = new GTWizardNP(params);
			
			oE.open();
			return oE;
		},
		pageOptionsDialog:function(fname,lang)
		{
			var params={};
			if(lang)params.lang = lang;
			var oDialog = this.openDialog({title: 'Свойства страницы'});
			params.parent=oDialog;
			params.curname=fname;
			var oE = new GTWizardPO(params);
			
			oE.open();
			return oE;
		},
		folderOptionsDialog:function(dir,lang)
		{
			var params={"curname":dir};
			if(lang)params.lang = lang;
			var oDialog = this.openDialog({title: 'Свойства раздела'});
			params.parent=oDialog;
			var oE = new GTWizardFO(params);
			
			oE.open();
			return oE;
		},
		newFolderWizard:function(dir,lang)
		{
			var params={"dir":dir};
			if(lang)params.lang = lang;
			var oDialog = this.openDialog({title: 'Новый раздел'});
			params.parent=oDialog;
			var oE = new GTWizardNF(params);
			
			oE.open();
			return oE;
		},
		/*from abzal first steps in prototype*/
		editMenuWizard:function(dir,lang,menutype)
		{
			var params={"dir":dir,'type':menutype};
			if(lang)params.lang = lang;
			var oDialog = this.openDialog({title: 'Редактировать меню'});
			params.parent=oDialog;
			var oE = new GTWizardMenu(params);
			
			oE.open();
			return oE;
		},
		/*
		 *	openPageEditor
		 *
		 *	@param params:object
		 *	{
		 *		fname
		 *		mode
		 *		replaceNode
		 *		parent
		 *	}
		*/
		openPageEditor:function(params)
		{
			if(!params)params={};
			params.title = 'Edit page';
			var oDialog = this.openDialog({title: 'Редактируем страницу'});
			params.parent=oDialog;
			params.curname=params.fname;
			var oE = new GTEditor(params);
			$(oDialog).dialog('option',{buttons: { "Save": function() { oE.save() }, "Cancel": function() { $(this).dialog("close"); } }});
			oE.open();
			return oE;
		},
		switchType:function(){
			if($$._('panelmini').style.display!='none')
			{
				$.cookie('panelsmall', null);
				$$._('panelmini').style.display='none';
				$$._('panelcont').style.display='block';
				$$._('paneltop').style.display='block';
				$$._('panelmenu').className='panel-big';
			}else{
				$.cookie('panelsmall', '1');
				$$._('panelmini').style.display='block';
				$$._('panelcont').style.display='none';
				$$._('paneltop').style.display='none';
				$$._('panelmenu').className='panel-small';
			}
			self.setTimeout(function(){$('#panelreplacement').css('height',$('#panel').height()+'px');},20);
		},
		ready:function(){
			D=document.body;
			$$.wCSS(DM+"css/ui-lightness/jquery-ui-1.8.6.custom.css");
			$$.wJS(DM+"js/base64.js");
			bindEvents();
			$('<div id="panelreplacement" style="height: '+$('#panel').height()+'px;position: relative;"></div>').insertBefore('#panel');
			if($.cookie('panelsmall')) this.switchType();
			$('#panel').css({'position':'absolute','top':'0px','width':'100%','zIndex':499});
			self.setInterval(function(){$('#panel').css('top',$(document).scrollTop()+'px');},20);
		},
		init:function(){
			if(/loaded|complete/.test(document.readyState)){U.ready();}else{setTimeout(U.init,13);}
		}
	};
	U.init();
	window.GTPanel = U;
})();

