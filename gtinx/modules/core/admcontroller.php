<?
// controller for user functions

Class CoreAdminPartController {
	var $optionArray = Array("tinyint", "smallint", "mediumint", "int", "integer", "bigint", "char", "varchar", "text", "enum");
	var $attributeArray = Array("", "binary", "unsigned", "unsigned zerofill");
	var $nullArray = Array("not null", "null");

	//var $replace["debuglevel"]=Array("0"=>"Не показывать", "1"=>"Инфо о времени выполнения", "2"=>"+ Скрытый список запросов");

	   function __construct() 
		{		
		//new switch for GET	

//			switch($_POST['act'])
//			{
				/*default:
					$this->ListGroup();
					break;*/
//			}
			$act = passedVal('act');
			define('MODCALL_LINK',"index.php?mod=core&");
			define('ACTCALL_LINK',MODCALL_LINK.'act='.$act.'&');
			switch($act)
			{
				//case 'setpagecnt': echo "foool"; print_r($GLOBALS); die;
				case 'config': $this->alferConf(); break;
			}
			
        }
		
					/*END TYPES*/
		function __destruct()
		{
		}
		
		function alferConf()
		{
			$toDo = passedVal("toDo");
			//print_r($_REQUEST);
			if(passedVal("cancel"))
			{
				$toDo = "";
			}

			switch($toDo)
			{
				case "editConfItemForm":
					$this->editConfItemForm(passedVal("name"));
					break;
				case "deleteConfItem":
					$this->deleteConfItem(passedVal("name"));
					break;
				case "makeDeletingConfItem":
					if($this->makeDeletingConfItem(passedVal("name")))
						$this->mainConfDefPage();
					break;
				case "makeEditingConfItem":
					if($this->makeEditingConfItem(passedVal("conf"), passedVal("old")))
					{
						$this->mainConfDefPage();
					}
					break;
				case "makeEditingConf":
					if(passedVal("add"))
						$this->addConfItemForm();
					elseif(passedVal("migration"))
						$this->migrationDBMultySync();
					else
						if($this->makeEditingConf(passedVal("conf")))
							$this->mainConfDefPage();
					break;
				case "makeAddingConfItem":
					if($this->makeAddingConfItem(passedVal("conf")))
					{
						$this->mainConfDefPage();
					}
					else
					{
						$this->addConfItemForm(passedVal("conf"));
					}
					break;
				default:
					$this->mainConfDefPage();
					break;
			}
		}

			function mainConfDefPage()
			{
				global $DB, $replace;

				//ADDED VERSIONING SUPPORT by Volhv

				$tablesRes = $DB->query("SHOW COLUMNS FROM `g_mainconf`");

				$mainconf = $DB->fetchAssoc($DB->query("SELECT * FROM `g_mainconf` ORDER BY `conf_version` DESC LIMIT 1"));

				$mainconfDescriptionTitle = $DB->fetchAssoc($DB->query("SELECT * FROM `g_mainconf_description` WHERE id='title'"));

				$mainconfDescriptionHint = $DB->fetchAssoc($DB->query("SELECT * FROM `g_mainconf_description` WHERE id='hint'"));

				$mainconfDescriptionShow = $DB->fetchAssoc($DB->query("SELECT * FROM `g_mainconf_description` WHERE id='show'"));

				$mainconfDescriptionReadonly = $DB->fetchAssoc($DB->query("SELECT * FROM `g_mainconf_description` WHERE id='readonly'"));


				$ind = 0;
				$mainConfArray = Array();
				while($tablesRow = $DB->fetchAssoc($tablesRes))
				{
					//$ind++;
					//echo("$ind : "); print_r($tablesRow); echo("<br>");
					$fieldTypeArray = $tablesRow["Type"];
					$fieldTypeArray = str_replace(")", "(", $fieldTypeArray);
					$fieldTypeArray = explode("(", $fieldTypeArray);
					$fieldType = trim($fieldTypeArray[0]);
					//$fieldLen = explode(")", $fieldTypeArray[1]);
					$fieldLen = trim($fieldTypeArray[1]);
					$fieldAttribute = trim($fieldTypeArray[2]);
					if(count(explode(",", $fieldLen))>1)
					{
						$fieldLen = explode(",", str_replace("'", "", $fieldLen));
					}
					//print_r($fieldType); echo("<br>");
					//echo("type: $fieldType len: $fieldLen<br>");
					$mainconfArray[] = Array("name"=>$tablesRow["Field"], "value"=>$mainconf[$tablesRow["Field"]], "type"=>$fieldType, "len"=>$fieldLen, "attribute"=>$fieldAttribute, "null"=>$tablesRow["Null"], "default"=>$tablesRow["Default"], "extra"=>$tablesRow["Extra"], "title"=>$mainconfDescriptionTitle[$tablesRow["Field"]], "hint"=>$mainconfDescriptionHint[$tablesRow["Field"]], "show"=>$mainconfDescriptionShow[$tablesRow["Field"]], "readonly"=>$mainconfDescriptionReadonly[$tablesRow["Field"]]);
				}
				//print_r($mainconfArray);
				//print_r($replace);
				echo("
				<form method=\"POST\" action=\"".ACTCALL_LINK."\">
					<table width=95% cellspacing=1 cellpadding=2>");
				$ind=0;
				foreach($mainconfArray as $i=>$v)
				{
					if($v["show"]==1)
					{
						$ind++;
						$rowClass = "row".(($ind%2)+1);
						echo("
						<tr class=\"$rowClass\">
							<td width=7% align=\"center\">
								<a href=\"".ACTCALL_LINK."&toDo=deleteConfItem&name=$v[name]\">Удалить</a>&nbsp;&nbsp;
								<a href=\"".ACTCALL_LINK."&toDo=editConfItemForm&name=$v[name]\">Изменить</a>
							</td>
							<td width=40%>$v[title]");
						if(trim($v["hint"])!="")
							echo("<small><br>$v[hint]</small>");
						if(trim($v["name"])!="")
							echo("<small style=\"color:#00AA66\"><br>$v[name]</small>");
						echo("</td>
							<td width=53%>");
						if((int)$v["readonly"]==0)
						{
							$v["type"]="";
						}

						switch($v["type"])
						{
							case "smallint": case "mediumint": case "int": case "integer": case "bigint": case "char": case "varchar":
								//$v["value"] = (int) $v["value"];
								if($v["len"]>33) $len = 0; else $len = $v["len"]*12;
								if($len<50 && $len!=0) $len = 50;
								if($len==0) $len = "100%"; else $len = $len."px";
								echo("
								<input type=\"text\" name=\"conf[$v[name]]\" style=\"width:$len\" value=\"$v[value]\">");
								break;
							case "text":
								echo("
								<textarea name=\"conf[$v[name]]\" rows=\"6\" style=\"width:100%\">$v[value]</textarea>");
								break;
							case "enum":
								echo("
								<select name=\"conf[$v[name]]\">");
								foreach($v["len"] as $i1=>$v1)
								{
									if(trim($replace[$v["name"]][$i1])!="") $v2 = $replace[$v["name"]][$i1]; else $v2=$v1;
									if($v1==$v["value"]) $selected = "selected"; else $selected = "";
									echo("
									<option value=\"$v1\" $selected>$v2</option>");
								}
								echo("
								</select>");
								break;
							case "tinyint":
								if(((int) $v["value"])==1) $checked = "checked"; else $checked = "";
								echo("
								<input type=\"hidden\" name=\"conf[$v[name]]\" value=\"0\">
								<input type=\"checkbox\" name=\"conf[$v[name]]\" value=\"1\" $checked>");
								break;
							default:
								echo($v["value"]);
								break;
						}
						echo("
							</td>
						</tr>");
					}
				}
				echo("
						<tr>
							<td colspan=3 align=\"center\">
								<input type=\"submit\" name=\"migration\" value=\"Миграция БД\">
								<input type=\"submit\" name=\"ok\" value=\"Принять\">
								<input type=\"submit\" name=\"add\" value=\"Добавить параметр\">
								<input type=\"hidden\" name=\"toDo\" value=\"makeEditingConf\">
							</td>
						</tr>
					</table>
				</form>");


			}

			function deleteConfItem($name)
			{
				echo("
				<form method=\"POST\" action=\"".ACTCALL_LINK."\">
					<span style=\"color:Red;\"> <h2> Вы уверены, что хотите удалить элемент конфигурации \"$name\"</h2></span><br>
					<input type=\"submit\" name=\"ok\" value=\"Да\">
					<input type=\"submit\" name=\"cancel\" value=\"Нет\">
					<input type=\"hidden\" name=\"name\" value=\"$name\">
					<input type=\"hidden\" name=\"toDo\" value=\"makeDeletingConfItem\">
				</form>");
			}

			function makeDeletingConfItem($name)
			{
				//echo($name);
				global $DB;
				$DB->query("ALTER TABLE `g_mainconf` DROP COLUMN `$name`");
				$DB->query("ALTER TABLE `g_mainconf_description` DROP COLUMN `$name`");
				return true;
			}

			function editConfItemForm($name)
			{
				global $DB;

				$columnRes = $DB->query("SHOW COLUMNS FROM `g_mainconf`");
				while(($columnRow = $DB->fetchAssoc($columnRes)) && $columnRow["Field"]!=$name);

				if(is_array($columnRow))
				{
					$fieldTypeArray = $columnRow["Type"];
					$fieldTypeArray = str_replace(")", "(", $fieldTypeArray);
					$fieldTypeArray = explode("(", $fieldTypeArray);
					$confType = trim($fieldTypeArray[0]);
					//$fieldLen = explode(")", $fieldTypeArray[1]);
					$confLen = trim($fieldTypeArray[1]);
					$confAttribute = trim($fieldTypeArray[2]);
					//echo("confType: $confType confLen: $confLen confAttribute: $confAttribute");
					/*$confType = explode("(", $columnRow["Type"]);
					$confLen = $confType[1];
					$confType = $confType[0];

					$confLen = str_replace(")", "", $confLen);*/

					$confName = $columnRow["Field"];
					$confDefault = $columnRow["Default"];
					$confNull = $columnRow["Null"];
					//echo(" confName: $confName confDefault: $confDefault confNull: $confNull");
					//echo("new: "); print_r($columnRow);

					$confTitle = $DB->qresult("SELECT `$confName` FROM `g_mainconf_description` WHERE id='title'");

					$confHint = $DB->qresult("SELECT `$confName` FROM `g_mainconf_description` WHERE id='hint'");

					$confShow = $DB->qresult("SELECT `$confName` FROM `g_mainconf_description` WHERE id='show'");

					$confReadonly = $DB->qresult("SELECT `$confName` FROM `g_mainconf_description` WHERE id='readonly'");


					//$optionArray = Array("tinyint", "smallint", "mediumint", "int", "integer", "bigint", "char", "varchar", "text");

					//$attributeArray = Array("", "binary", "unsigned", "unsigned zerifill");

					echo("
				<form method=\"POST\" action=\"".ACTCALL_LINK."\">
					<table width=100% cellspacing=1 cellpadding=2>
						<tr class=\"row1\">
							<td>Название</td>
							<td>
								<input type=\"text\" name=\"conf[name]\" value=\"$confName\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td width=20%>Тип</td>
							<td width=80%>
								<select name=\"conf[type]\">");
					foreach($this->optionArray as $v)
					{
						if($v==$confType) $selected="selected"; else $selected="";

						echo("
									<option value=\"$v\" $selected>$v</option>");
					}
					echo("
								</select>
								&nbsp;Длина/содержание <input type=\"text\" name=\"conf[len]\" value=\"$confLen\" style=\"width:100px\">
								&nbsp;Атрибуты
								<select name=\"conf[attribute]\">");
					foreach($this->attributeArray as $v)
					{
						if($v==$confAttribute) $selected="selected"; else $selected="";

						echo("
									<option value=\"$v\" $selected>$v</option>");
					}
					echo("
								</select>
							</td>
						</tr>
						<tr class=\"row1\">
							<td>По умолчанию</td>
							<td>
								<input type=\"text\" name=\"conf[default]\" value=\"$confDefault\" style=\"width:200px\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Ноль</td>
							<td>
								<select name=\"conf[0]\">");
					foreach($this->nullArray as $v)
					{
						if(($v=="null" && $confNull=="YES") || ($v=="not null" && trim($confNull)=="")) $selected="selected"; else $selected="";
						echo("
									<option value=\"$v\" $selected>$v</option>");
					}
					echo("
								</select>
							</td>
						</tr>
						<tr class=\"row1\">
							<td>Title</td>
							<td>
								<input type=\"text\" name=\"conf[title]\" value=\"$confTitle\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Hint</td>
							<td>
								<input type=\"text\" name=\"conf[hint]\" value=\"$confHint\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row1\">
							<td>Показывать</td>
							<td>");
					if($confShow=="1") $checked="checked"; else $checked="";
					echo("
								<input type=\"checkbox\" name=\"conf[show]\" value=\"1\" $checked>
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Изменяемый</td>
							<td>");
					if($confReadonly=="1") $checked="checked"; else $checked="";
					echo("
								<input type=\"checkbox\" name=\"conf[readonly]\" value=\"1\" $checked>
							</td>
						</tr>
							<td colspan=2 align=\"center\">
								<input type=\"submit\" name=\"ok\" value=\"Принять\">
								<input type=\"submit\" name=\"cancel\" value=\"Отмена\">
								<input type=\"hidden\" name=\"toDo\" value=\"makeEditingConfItem\">
								<input type=\"hidden\" name=\"old[name]\" value=\"$confName\">
								<input type=\"hidden\" name=\"old[len]\" value=\"$confLen\">
								<input type=\"hidden\" name=\"old[type]\" value=\"$confType\">
								<input type=\"hidden\" name=\"old[0]\" value=\"$confNull\">
								<input type=\"hidden\" name=\"old[default]\" value=\"$confDefault\">
								<input type=\"hidden\" name=\"old[attribute]\" value=\"$confAttribute\">
							</td>
						</tr>
					</table>
				</form>");
				}
			}

			function makeEditingConfItem($conf, $old)
			{
				global $DB;

				if($old[0]=="YES") $old[0]="null"; else $old[0]="not null";
				print_r($conf);
				//print_r($old);
				//return true;

				if(trim($conf["len"])!="") $conf["len"]=stripslashes("($conf[len])");
				else $conf["len"]="";

				if($conf["type"]!=$old["type"] || $conf["len"]!=$old["len"] || $conf["name"]!=$old["name"] || $conf[0]!=$old[0] || $conf["attribute"]!=$old["attribute"])
				{
					$DB->query("ALTER TABLE `g_mainconf` CHANGE `$old[name]` `$conf[name]` $conf[type]$conf[len] $conf[attribute] $conf[0]");

					$DB->query("ALTER TABLE `g_mainconf_description` CHANGE `$old[name]` `$conf[name]` varchar(255)  not null");
				}

				$DB->query("ALTER TABLE `g_mainconf` ALTER COLUMN `$conf[name]` SET DEFAULT '$conf[default]'");

				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[title]' WHERE `id`='title'");
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[hint]' WHERE `id`='hint'");
				if($conf["show"]!="1")
				{
					$conf["show"]="0";
				}
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[show]' WHERE id='show'");

				if($conf["readonly"]!="1")
				{
					$conf["readonly"]="0";
				}
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[readonly]' WHERE id='readonly'");
				return true;
			}

			function addConfItemForm($conf=NULL)
			{
				//echo("null: "); print_r($nullArray);
				//$nullArray = Array("not null", "null");
				echo("
				<h3> Форма по добавлению нового параметра конфигурации</h3>
				<form method=\"POST\" action=\"".ACTCALL_LINK."\">
					<table width=100% cellspacing=1 cellpadding=2>
						<tr class=\"row1\">
							<td>Название</td>
							<td>
								<input type=\"text\" name=\"conf[name]\" value=\"$conf[name]\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td width=20%>Тип</td>
							<td width=80%>
								<select name=\"conf[type]\">");
				foreach($this->optionArray as $v)
				{
					if($v==$conf["type"]) $selected="selected"; else $selected="";

					echo("
									<option value=\"$v\" $selected>$v</option>");
				}
				echo("
								</select>
								&nbsp;Длина/содержание <input type=\"text\" name=\"conf[len]\" value=\"$conf[len]\" style=\"width:100px\">
								&nbsp;Атрибуты
								<select name=\"conf[attribute]\">");
				foreach($this->attributeArray as $v)
				{
					if($v==$conf["attribute"]) $selected="selected"; else $selected="";

					echo("
									<option value=\"$v\" $selected>$v</option>");
				}
				echo("
								</select>
							</td>
						</tr>
						<tr class=\"row1\">
							<td>По умолчанию</td>
							<td>
								<input type=\"text\" name=\"conf[default]\" value=\"$conf[default]\" style=\"width:200px\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Ноль</td>
							<td>
								<select name=\"conf[0]\">");
				foreach($this->nullArray as $v)
				{
					if(($v=="null" && $conf["null"]=="YES") || ($v=="not null" && trim($conf["null"])=="")) $selected="selected"; else $selected="";
					echo("
									<option value=\"$v\" $selected>$v</option>");
				}
				echo("
								</select>
							</td>
						</tr>
						<tr class=\"row1\">
							<td>Title</td>
							<td>
								<input type=\"text\" name=\"conf[title]\" value=\"$conf[title]\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Hint</td>
							<td>
								<input type=\"text\" name=\"conf[hint]\" value=\"$conf[hint]\" style=\"width:100%\">
							</td>
						</tr>
						<tr class=\"row1\">
							<td>Показывать</td>
							<td>");
				if($conf["show"]=="1" || !is_array($conf)) $checked="checked"; else $checked="";
				echo("
								<input type=\"checkbox\" name=\"conf[show]\" value=\"1\" $checked>
							</td>
						</tr>
						<tr class=\"row2\">
							<td>Редактируемый</td>
							<td>");
				if($conf["readonly"]=="1" || !is_array($conf)) $checked="checked"; else $checked="";
				echo("
								<input type=\"checkbox\" name=\"conf[readonly]\" value=\"1\" $checked>
							</td>
						</tr>
							<td colspan=2 align=\"center\">
								<input type=\"submit\" name=\"ok\" value=\"Принять\">
								<input type=\"submit\" name=\"cancel\" value=\"Отмена\">
								<input type=\"hidden\" name=\"toDo\" value=\"makeAddingConfItem\">
							</td>
						</tr>
					</table>
				</form>");
			}

			function makeAddingConfItem($conf)
			{
				global $DB;

				if(trim($conf["name"])=="" || (($conf["type"]=="varchar" || $conf["type"]=="char") && ((int) $conf["len"]==0)))
				{
					puterr("Неправильно введены параметры");
					return false;
				}

				switch($conf["type"])
				{
					case "tinyint": case "smallint": case "mediumint": case "int": case "integer": case "bigint": case "char": case "varchar":
						if(trim($conf["len"])!="" && (int)$conf["len"]>0)
							$conf["len"]="($conf[len])";
						else $conf["len"]="";
						break;
					case "enum":
						if(trim($conf["len"])!="")
							$conf["len"]=stripslashes("($conf[len])");
						else
							$conf["len"]="";
						break;
				}

				if(trim($conf["default"])!="") $conf["default"]="DEFAULT '$conf[default]'"; else $conf["default"]="";

				$DB->query("ALTER TABLE `g_mainconf` ADD `$conf[name]` $conf[type]$conf[len] $conf[attribute] $conf[default] $conf[0]");

				$DB->query("ALTER TABLE `g_mainconf_description` ADD `$conf[name]` varchar(255) not null");

				//$DB->query("ALTER TABLE `g_mainconf` ALTER COLUMN $conf[name] SET DEFAULT '$conf[default]'");

				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[title]' WHERE `id`='title'");
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[hint]' WHERE `id`='hint'");
				if($conf["show"]!="1")
				{
					$conf["show"]="0";
				}
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[show]' WHERE id='show'");

				if($conf["readonly"]!="1")
				{
					$conf["readonly"]="0";
				}
				$DB->query("UPDATE `g_mainconf_description` SET `$conf[name]`='$conf[readonly]' WHERE id='readonly'");
				return true;
			}

			function makeEditingConf($conf)
			{
				global $DB;
				$cq = $DB->query("SELECT * FROM `g_mainconf` ORDER BY `conf_version` DESC LIMIT 1/*Loading config*/");
				$CONF = $DB->fetchAssoc($cq);
			  //  print_r($conf);
				//ADDED VERSIONING SUPPORT by Volhv

				//construction of new config verion
				$nc_names = $nc_values = '';

				foreach($CONF as $_k=>$_v){
					if((int)$_k !==0) continue;
					if($_k == 'conf_version'){
						if($nc_values) $nc_values.=',';
						 $nc_values .= 'UNIX_TIMESTAMP()';
					}else{
						if($nc_values) $nc_values.=',';
						$nc_values .= isset($conf[$_k])?"'".$conf[$_k]."'":"'$_v'";
					}
					 if($nc_names) $nc_names.=',';
					 $nc_names .= "`$_k`";

				}

				//insert new version, updating changed fields
				//echo "<br><br>INSERT INTO `g_mainconf` ($nc_names) VALUES ($nc_values)<br><br>";
				$DB->query("INSERT INTO `g_mainconf` ($nc_names) VALUES ($nc_values)");

				if($DB->affectedRows()) GTAPP::Raise("YES"); else GTAPP::Raise("NO");
				return true;
			}
			
			function migrationDBMultySync()
			{
				global $DB;
				$LANG=GTAPP::getDeafaultLang();
				$sql="INSERT IGNORE INTO `g_dblock_multy`
					(`TITLE`,`SHORTTEXT`,`FULLTEXT`,`SHORTTEXT_TYPE`,`FULLTEXT_TYPE`,`DBLOCK_ID`,`TYPE_ID`,`SUBTYPE`,`DBLOCK_KEY`,`LANG`)
					SELECT 
					dblock.`TITLE`,dblock.`SHORTTEXT`,dblock.`FULLTEXT`,dblock.`SHORTTEXT_TYPE`,dblock.`FULLTEXT_TYPE`,dblock.`ID`,dblock.`TYPE_ID`,dblock.`SUBTYPE`,dblock.`DBLOCK_KEY`,'".$LANG."'
					FROM `g_dblock` as dblock";
				$DB->Query($sql);
				header("Location: ./?mod=core&act=config");
				die();
			}
}
?>