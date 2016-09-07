function GTWizard(inivals){
	this.U = this;
	this.stage=0; /*0 for disabled, 1 for inited, 2 for built, 3 for run*/
	this.stageMax=1; /*0 for disabled, 1 for inited, 2 for built, 3 for run*/
	this.O={};
	this.Ov={};
	this.curname='';
	this.directact='';

	for(var x in inivals) this[x] = inivals[x];
}

GTWizard.prototype.setContent = function(h){ if(this.parent.setContent)this.parent.setContent(h);else this.parent.innerHTML=h; }
GTWizard.prototype.saveSucc = function(){ $(this.parent).dialog('close'); }
GTWizard.prototype.saveFail = function(){ alert("Сохранить информацию не удалось!");}
GTWizard.prototype.save = function(){
	this.saveStage();
	var sData = '';
	this.Ov.dir = this.dir;
	for(var x in this.Ov)
		sData+='&'+x+'='+encodeURIComponent(this.Ov[x]);
	
	$.ajax({type:"POST",url: "/gtinx/direct.php?", context: this, success:this.saveSucc,data: "mod=editlib&act="+this.directact+(this.lang?'&lang='+this.lang:'')+sData,error:	this.saveFail,dataType:'json'});
}
GTWizard.prototype.nextStage = function(){if(this.stage<this.stageMax)this.drawStage(this.stage+1);}
GTWizard.prototype.prevStage = function(){if(this.stage>1)this.drawStage(this.stage-1);}
GTWizard.prototype.open = function(){this.drawStage(1);}
GTWizard.prototype.uIdOf = function(item){
	if(this.O[item]) return this.O[item];
	this.O[item]=$$.uniqueID();
	return this.O[item];
}
GTWizard.prototype.saveStage = function(){
	for(var x in this.O){
		var objid = this.O[x],v,o;
		if(o = $$._(objid)){
			switch(o.nodeName){
				case 'TEXTAREA': v= o.innerHTML; break;
				case 'INPUT': v = (o.type!='checkbox' || o.type=='checkbox' && o.checked)?o.value:''; break;
				case 'SELECT': v = o.options[o.selectedIndex].value; break;
				default: v = o.innerHTML;
			}
			this.Ov[x]=v;
		}
	}
}
GTWizard.prototype.setButtons = function(bt){
	var me = this;
	if(!bt)bt={};
	if(this.stage>1)
		bt["Back"] = function() { me.prevStage(); }
	if(this.stage<this.stageMax)
		bt["Next"] = function() { me.nextStage(); }
	if(this.stage==this.stageMax)
		bt["Save"] = function() { me.save(); }
	
	bt["Cancel"] = function() { $(this).dialog("close"); }
	$(this.parent).dialog('option',{buttons: bt });
}

GTWizard.prototype.drawStage = function(stage){this.setButtons();}
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

function GTWizardNP(inivals){for(var x in inivals) this[x] = inivals[x];}
GTWizardNP.prototype = new GTWizard({directact:'createnewpage'});
GTWizardNP.prototype.saveSucc = function(){ 
//on success - close
	GTWizard.prototype.saveSucc.call(this);
	
	//reload page
	window.location=this.Ov.peditoncreate==''?window.location:this.Ov.pfile;
}

GTWizardNP.prototype.drawStage = function(stage){
	this.saveStage();
	
	switch(stage){
	case 1:
		this.setContent(
			'<b>Создание новой страницы в текущем разделе</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('ptitle')+'" value="Новая страница" /></td></tr>'+
			'<tr><td>Имя нового файла</td><td><input style="width:90%" type="text" id="'+this.uIdOf('pfile')+'" value="new-page.php" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td>META-теги страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('pmeta')+'" value="" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td><label for="'+this.uIdOf('addtomenu')+'">Добавить пункт меню</label></td><td><input type="checkbox" id="'+this.uIdOf('addtomenu')+'" value="1" checked /></td></tr>'+
			'<tr><td><label for="'+this.uIdOf('peditoncreate')+'">Перейти к редактированию</label></td><td><input type="checkbox" id="'+this.uIdOf('peditoncreate')+'" value="1" checked /></td></tr>'+
			'</table>');
	break;
	default: this.setContent( 'err'+stage);
		return;

	}
	this.stage = stage;
	//set buttons
	var bt = {};
	this.setButtons(bt);
}

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

function GTWizardNF(inivals){
	for(var x in inivals) this[x] = inivals[x];
}

GTWizardNF.prototype = new GTWizard({directact:'createnewdir',stageMax:2});
GTWizardNF.prototype.saveSucc = function(){ 
	GTWizard.prototype.saveSucc.call(this);
	window.location=this.Ov.peditoncreate==''?window.location:this.Ov.dfile+'/index.php';
}
GTWizardNF.prototype.drawStage = function(stage){
	this.saveStage();
	
	switch(stage){
	case 1:
		this.setContent( 
			'<b>Создание нового раздела</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок раздела</td><td><input style="width:90%" type="text" id="'+this.uIdOf('dtitle')+'" value="Новый раздел" /></td></tr>'+
			'<tr><td>Имя директории</td><td><input style="width:90%" type="text" id="'+this.uIdOf('dfile')+'" value="new-section" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td>META-теги раздела</td><td><input style="width:90%" type="text" id="'+this.uIdOf('dmeta')+'" value="" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td><label for="'+this.uIdOf('addtomenu')+'">Добавить пункт меню</label></td><td><input type="checkbox" id="'+this.uIdOf('addtomenu')+'" value="1" checked /></td></tr>'+
			'</table>');
	break;
	case 2:
		this.setContent(
			'<b>Создание новой страницы в текущем разделе</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок основной страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('ptitle')+'" value="'+this.Ov.dtitle+'" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td>META-теги страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('pmeta')+'" value="'+this.Ov.dmeta+'" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td><label for="'+this.uIdOf('peditoncreate')+'">Перейти к редактированию</label></td><td><input type="checkbox" id="'+this.uIdOf('peditoncreate')+'" value="1" checked /></td></tr>'+
			'</table>');
	break;
	default: return;

	}
	this.stage = stage;
	//set buttons
	var bt = {};
	this.setButtons(bt);
}

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////


function GTWizardFO(inivals){
	for(var x in inivals) this[x] = inivals[x];
}
GTWizardFO.prototype = new GTWizard({directact:'diroptions'});

GTWizardFO.prototype.onGetData = function(data){
	for(var x in data)
		this.Ov[x]=Base64.decode(data[x]);
	this.Ov.dfile = this.curname;
	GTWizard.prototype.open.call(this);
}
GTWizardFO.prototype.open = function(){
	$.ajax({
			url: "/gtinx/direct.php?mod=editlib&act=get"+this.directact+"&dfile="+encodeURIComponent(this.curname)+(this.lang?'&lang='+this.lang:'')+'&rnd='+$$.uncacheID(), 
			context: this, 
			cache: false, 
			success:this.onGetData,
			dataType:'json'});
}

GTWizardFO.prototype.drawStage = function(stage){
	this.saveStage();
	
	switch(stage){
	case 1:
		this.setContent(
			'<b>Изменение свойств раздела</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок раздела</td><td><input style="width:90%" type="text" id="'+this.uIdOf('TITLE')+'" value="'+this.Ov.TITLE+'" /></td></tr>'+
			'<tr><td colspan="2"><hr /></td></tr>'+
			'<tr><td>META-теги раздела</td><td><input style="width:90%" type="text" id="'+this.uIdOf('META')+'" value="'+this.Ov.META+'" /></td></tr>'+
			'</table>');
	break;
	default: return;
	}
	this.stage = stage;
	//set buttons
	var bt = {};
	this.setButtons(bt);
}

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////


function GTWizardPO(inivals){
	for(var x in inivals) this[x] = inivals[x];
}
GTWizardPO.prototype = new GTWizard({directact:'pageoptions'});

GTWizardPO.prototype.drawStage = function(stage){
	this.saveStage();
	
	switch(stage){
	case 1:
		this.setContent(
			'<b>Изменение свойств текущей страницы</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('TITLE')+'" value="'+this.Ov.TITLE+'" /></td></tr>'+
			'<tr><td width="30%">Название страницы <br /><small>(если не равно заголовку)</small></td><td><input style="width:90%" type="text" id="'+this.uIdOf('HEADER')+'" value="'+this.Ov.HEADER+'" /></td></tr>'+
			'<tr><td>META-теги страницы</td><td><input style="width:90%" type="text" id="'+this.uIdOf('META')+'" value="'+this.Ov.META+'" /></td></tr>'+
			'</table>');
	break;
	default: return;

	}
	this.stage = stage;
	//set buttons
	var bt = {};
	this.setButtons(bt);
}

GTWizardPO.prototype.onGetData = function(data){
	for(var x in data){
		this.Ov[x]=Base64.decode(data[x]);
		//alert(this.Ov[x]);
	}
	this.Ov.dfile = this.curname;
	GTWizard.prototype.open.call(this);
}
GTWizardPO.prototype.open = function(){
	$.ajax({
			url: "/gtinx/direct.php?mod=editlib&act=get"+this.directact+"&fname="+encodeURIComponent(this.curname)+(this.lang?'&lang='+this.lang:'')+'&rnd='+$$.uncacheID(), 
			context: this, 
			cache: false, 
			success:this.onGetData,
			dataType:'json'});
}
GTWizardPO.prototype.saveSucc = function(data){ 
	GTWizard.prototype.saveSucc.call(this);
	if(!data.result)
		alert("FAIL");
	else
		window.location=window.location;
}
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////

function GTWizardMenu(inivals){
	for(var x in inivals) this[x] = inivals[x];
	this.Ov.type=this.type;
}
GTWizardMenu.prototype = new GTWizard({directact:'editmenu'});

GTWizardMenu.prototype.drawStage = function(stage){

		txt='';
		txt2='';
		var next=0;	
		for(var z in this.Ov.menu)
		{
			var o=this.Ov.menu[z];
			txt+='<tr><td width="30%"><input style="width:90%" type="text" id="'+this.uIdOf('TITLE'+z)+'" value="'+o.NAME+'" /></td>'+
			'<td><input style="width:90%" type="text" id="'+this.uIdOf('URL'+z)+'" value="'+o.URL+'" /></td>'+
			'</td><td width="10%"><input type="button" value="+" id="'+this.uIdOf('dop'+z)+'"></td></tr>'+
			'';
			next++;	
		}
		var I;
		for (I=next+1; I<=next+3; I++)
		{
			txt+='<tr><td width="30%"><input style="width:90%" type="text" id="'+this.uIdOf('TITLE'+I)+'" value="" /></td>'+
			'<td><input style="width:90%" type="text" id="'+this.uIdOf('URL'+I)+'" value="" /></td>'+
			'</td><td width="10%"><input type="button" value="+" id="'+this.uIdOf('dop'+I)+'"></td></tr>';
		}
	this.saveStage();
	switch(stage){
	case 1:
		
		this.setContent(
			'<b>Изменение свойств текущего меню</b>'+
			'<br /><br /><hr />'+
			'<table width="100%" cellspacing="10" border="0">'+
			'<tr><td width="30%">Заголовок</td><td>URL адрес</td><td>доп. опции</td></tr>'
			+txt+
			'</table>');
			
	break;
	default: return;

	}
	for(var i in this.Ov.menu)
	{
	
		setHandler(this.Ov.menu[i],this,i);
	}
	
	this.stage = stage;
	//set buttons
	var bt = {};
	this.setButtons(bt);

}

function setHandler(menuItem,me,i){
	$('#'+me.uIdOf('dop'+i)).bind('click',{a:menuItem},
		function(event){
			
			var s = GTPanel.openDialog({
					width:300,
					title:event.data.a.NAME,
					'parent':me,
					buttons:{ 
						"OK": function() {  
							var se = $$._(me.uIdOf('TARGET'+i));
							me.Ov['TARGET'+i] = se.options[se.selectedIndex].value; 
							$(this).dialog("close"); 
						}
					}
				});
			s.setContent('<table width="80%" cellspacing="10" border="0"><tr><td width="30%">Название</td><td>Свойства</td></tr>'+
			'<tr><td width="30%">Уровень:</td><td><input style="width:90%" type="text" id="'+me.uIdOf('URL'+i)+'" value="'+event.data.a.LEVEL+'" /></td><tr>'+
			'<tr><td width="30%">Метод октрытия:</td><td><select id="'+me.uIdOf('TARGET'+i)+'">'+
			'<option value="'+me.Ov['TARGET'+i]+'">'+me.Ov['TARGET'+i]+'</option>'+
			'<option value="_blank">_blank</option>'+
			'<option value="_self">_self</option>'+
			'<option value="_parent">_parent</option>'+
			'<option value="_top">_top</option>'+
			'</select></td><tr>'+
			'</table>');
		});
}

GTWizardMenu.prototype.onGetData = function(data){
	//alert(data.menu);
	this.Ov.menu=[];
	for(var x in data.menu){
		var tmp = {};
		for(var y in data.menu[x])
		{
			tmp[y]=Base64.decode(data.menu[x][y]);
			this.Ov[y+x] = tmp[y];
			//alert(tmp[y]);
		}
		
		this.Ov.menu.push(tmp);
	}
	//alert(this.Ov.NAME);
	
	this.Ov.filepass = data.filepass;
	this.Ov.dname = this.dir;
	GTWizard.prototype.open.call(this);
}
GTWizardMenu.prototype.open = function(){
	var s = {
			url: "/gtinx/direct.php?mod=editlib&act=get"+this.directact+"&dname="+encodeURIComponent(this.dir)+"&type="+encodeURIComponent(this.type)+(this.lang?'&lang='+this.lang:'')+'&rnd='+$$.uncacheID(), 
			context: this, 
			cache: false, 
			success:this.onGetData,
			dataType:'json'};
	
	$.ajax(s);
}

GTWizardMenu.prototype.saveSucc = function(data){ 
	GTWizard.prototype.saveSucc.call(this);
	if(!data.result)
		alert("FAIL");
	else
		window.location=window.location;
}


