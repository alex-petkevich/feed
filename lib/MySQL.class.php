<?
class cMySQL
{
	var $link;
	var $not_escapped = array('NOW()');
	function cMySQL()
	{}

	function Connect()
	{
		$this->link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or Debug(mysql_error()." in cMySQL->Connect",'FATAL');
		mysql_select_db(DB_DB,$this->link);
		mysql_query("SET CHARACTER SET utf8",$this->link);
		mysql_query("SET NAMES utf8",$this->link);
   }

	function Disconnect()
	{
		mysql_close($this->link);
	}

	function Execute($SQL,$Fatal = 'FATAL',$SaveToLog=1)
	{
		$Query = preg_replace("/;.+/",'',$Query);
		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$SQL</b> in cMySQL->Execute");
		if (DBG_SAVE_UPD_QUERIES == 1 && $SaveToLog)
		file_put_content(MAIN_DIR."update.sql","$SQL;");
		$res = mysql_query($SQL,$this->link);
		if (!$res)
		{
			Debug($SQL.": <b>".mysql_error($this->link)."</b> in cMySQL->Execute",$Fatal);
			return 0;
		}
		else
		return $res;
	}

	function Insert($Data,$Table,$NoFieldNames=0,$SaveToLog=1)
	{
		$fnames = array_keys($Data);
		$fvalues = array_values($Data);
		foreach ($fnames as $name => $val)
		$fnames[$name] = '`'.preg_replace("/;.+/",'',$val).'`';
		$Names = implode(",",$fnames);
		foreach ($fvalues as $name => $val)
		if (!in_array(strtoupper($val),$this->not_escapped) && !is_numeric($val))
		$fvalues[$name] = "'".$this->Prepare($val)."'";
		$Values = implode(",",$fvalues);
		$Names = ($NoFieldNames ? "" : "($Names)");
		$SQL = "INSERT INTO `$Table` $Names VALUES($Values)";
		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$SQL</b> in cMySQL->Insert");
		if (DBG_SAVE_UPD_QUERIES == 1 && $SaveToLog)
		file_put_content(MAIN_DIR."update.sql","$SQL;");
		mysql_query($SQL,$this->link) or Debug($SQL.": <b>".mysql_error($this->link)."</b> in cMySQL->Insert",'FATAL');
		return mysql_insert_id($this->link);
	}

	function Update($Data,$Table,$ID,$SaveToLog=1)
	{
		if (is_array($ID))
		{
			$ID_NAME = $ID[NAME];
			$ID_VALUE = $ID[VALUE];
		}
		else
		{
			$ID_NAME = $ID;
			$ID_VALUE = $Data["$ID"];
		}
		$allfields = $this->SelectArray("SHOW FIELDS FROM `$Table`");
		$prevres = $this->SelectHash("SELECT * FROM `$Table` WHERE `".$ID_NAME."`='".$ID_VALUE."'");
		$fnames = array_keys($Data);
		$fvalues = array_values($Data);
		foreach ($fnames as $name => $val)
		$fnames[$name] = '`'.preg_replace("/;.+/",'',$val).'`';
		foreach ($fvalues as $name => $val)
		if (!in_array(strtoupper($val),$this->not_escapped) && !is_numeric($val))
		$fvalues[$name] = "'".$this->Prepare($val)."'";
		$SQLa = array();
		// update new values
		foreach ($fnames as $id => $val)
		{
			$SQLa[] = $val.'='.$fvalues[$id];
		}
		//	updaet old values
		foreach ($allfields as $id => $val)
		{
			if (!in_array($val[Field],array_keys($Data)))
			$SQLa[] = '`'.$val[Field]."`='".$prevres["$val[Field]"]."'";
		}


		$SQL = "UPDATE `$Table` SET ".implode(",",$SQLa)." WHERE ".$ID_NAME."='".$ID_VALUE."'";
		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$SQL</b> in cMySQL->Update");
		if (DBG_SAVE_UPD_QUERIES == 1 && $SaveToLog)
		file_put_content(MAIN_DIR."update.sql","$SQL;");
		mysql_query($SQL,$this->link) or Debug($SQL.": <b>".mysql_error($this->link)."</b> in cMySQL->Update",'FATAL');
		return $ID[VALUE];
	}

	function Set($Data,$Table,$ID)
	{
		if (is_array($ID))
		{
			$ID_NAME = $ID[NAME];
			$ID_VALUE = $ID[VALUE];
		}
		else
		{
			$ID_NAME = $ID;
			$ID_VALUE = $Data["$ID"];
		}
		$iMode = 1;
		if (!$ID[VALUE])
		$iMode = 0;
		else
		{
			$res = $this->SelectHash("SELECT COUNT(*) as CNT FROM `$Table` WHERE `".$ID_NAME."`='".$ID_VALUE."'");
			$iMode = $res['CNT'];
		}
		if ($iMode)
		$iID = $this->Update($Data,$Table,$ID);
		else
		$iID = $this->Insert($Data,$Table);
		return $iID;

	}

	function SelectArray($Query,$StartRow=-1,$MaxRows=-1,$SingleKey='')
	{
//		if (DBG_MESSAGES == "0")
			$Query = preg_replace("/;.+/",'',$Query);
		$Result = array();
		$SQLlim = "";
		if ($StartRow>=0)
		$SQLlim = " LIMIT $StartRow";
		if ($MaxRows>=0)
		{
			if (!strlen($SQLlim))
			$SQLlim = " LIMIT $MaxRows";
			else
			$SQLlim .= ",$MaxRows";
		}
		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$Query</b> in cMySQL->SelectArray");
		$res = mysql_query($Query.$SQLlim,$this->link);
		if (!$res)
		{
			Debug($Query.$SQLlim.": <b>".mysql_error($this->link)."</b> in cMySQL->SelectArray",'FATAL');
			return 0;
		}
		else
		{
			while($row = mysql_fetch_array($res))
			{
				if ($SingleKey)
					$Result[] = $row["$SingleKey"];
				else
					$Result[] = $row;
			}
		}
		mysql_free_result($res);
		return $Result;
	}

	function SelectArrayPaging($Query,&$Pages,$iMaxRows,$ShowPages = 8,$CountQuery="")
	{
			$Query = preg_replace("/;.+/",'',$Query);
		if (!$iMaxRows)
		return "";
      if (!$Pages)
         return $this->SelectArray($Query);
		$Pages['Current'] = ($Pages['Current'] ? $Pages['Current'] : 0);
		$iCurRec = $Pages['Current'] * $iMaxRows;
		$res = $this->SelectArray($Query,$iCurRec,$iMaxRows);
		preg_match("/(from.+)(limit|order|group)?$/i",$Query,$Match);
		preg_match("/select\s+(distinct[\s+|\s*\(\s*].+?)[\s|,]/i",$Query,$ExMatch);
		if ($ExMatch[1])
			$sExpr = str_replace("(","",$ExMatch[1]);
		else
			$sExpr='*';
		$SQL = ($CountQuery ? $CountQuery : "SELECT COUNT($sExpr) as CNT ".$Match[1]);
		$Count = $this->SelectHash($SQL);
		$Pages['CountRecs'] = $Count['CNT'];
		if ($Pages['CountRecs'] > $iMaxRows)
		{
			$EndPage = ceil($Pages['CountRecs'] / $iMaxRows) - 1;
			$iBeginViewed = ($Pages['Current'] - $ShowPages >= 0 ? $Pages['Current'] - $ShowPages : 0);
			$iEndViewed = ($Pages['Current'] + $ShowPages <= $EndPage ? $Pages['Current'] + $ShowPages : $EndPage);
			$Pages['Listing'] = array();
			foreach (range($iBeginViewed,$iEndViewed) as $v)
			   $Pages['Listing'][] = array('Num'=>$v,'Show'=>$v+1);
			if ($EndPage > $Pages['Current'])
			   $Pages['Next'] = $Pages['Current'] + 1;
			if ($Pages['Current'] > 0)
			   $Pages['Prev'] = $Pages['Current'] - 1;
		}

		return $res;
	}

	function SelectHash($Query,$SingleKey = '')
	{
//			$Query = preg_replace("/;.+/",'',$Query);
	if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$Query</b> in cMySQL->SelectHash");

		$res = mysql_query($Query,$this->link);
		if (!$res)
		{
			Debug($Query.": <b>".mysql_error($this->link)."</b> in cMySQL->SelectHash",'FATAL');
			return 0;
		}
		else
		$Result = mysql_fetch_assoc($res);
		if (!$Result)
		$Result = array();
		mysql_free_result($res);
		return ($SingleKey!='' ? $Result[$SingleKey] : $Result);
	}

	function SelectRequest($SQL)
	{
		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$SQL</b> in cMySQL->SelectRequest");
		$res = mysql_query($SQL,$this->link);
		if (!$res)
		{
			Debug($SQL.": <b>".mysql_error($this->link)."</b> in cMySQL->SelectRequest",'FATAL');
			return 0;
		}
		else
		return $res;
	}

	function SQLNext($res)
	{
		return mysql_fetch_array($res);
	}

	function Show($SQL)
	{
		$Result = array();

		if (DBG_SHOW_QUERIES == 1)
		Debug("<b>$Query</b> in cMySQL->Show");
		$res = mysql_query($SQL,$this->link);
		if (!$res)
		{
			Debug($Query.$SQLlim.": <b>".mysql_error($this->link)."</b> in cMySQL->Show",'FATAL');
			return 0;
		}
		else
		{
			while($row = mysql_fetch_array($res))
			{
				$Result[] = $row;
			}
		}
		mysql_free_result($res);
		return $Result;
	}

	function Prepare($value)
	{
//		if (!get_magic_quotes_gpc())
//		$value = addslashes(preg_replace("/;.+/",'',$value));
		return mysql_real_escape_string($value,$this->link);
	}

	function CreateTable($Table)
	{
		$Fields = array();
		$prim = '';
		$Indexes = array();
		usort($Table[Fields],"fsort");
		foreach($Table[Fields] as $k=>$v)
		{
			$size = "";
			$def = "";
			$unsig = "";
			$notnull = "";
			$autoinc = "";
			if ($v[Length]!='')
			$size = "(".$v[Length].")";
			if ($v["Default"]!='')
			{
				if (!in_array($v[Type],array('DATE','DATETIME','TIMESTAMP','TIME','TINYBLOB','TINYTEXT','BLOB','TEXT','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT')))
				$def = "DEFAULT '".$v["Default"]."'";
			}
			if ($v[IsPrimary]!='')
			$prim = ",PRIMARY KEY(`".$v[Name]."`)";
			if ($v[IsUnsigned]!='')
			$unsig = "UNSIGNED";
			if ($v[IsIndex]!='')
			$Indexes[] = "INDEX (`$v[Name]`)";
			if ($v[IsNotNull]!='')
			$notnull = "NOT NULL";
			if ($v[IsAutoincrement]!='')
			$autoinc = "AUTO_INCREMENT";

			$Fields[] = "`$v[Name]` $v[Type] $size $unsig $def $notnull $autoinc";
		}

		$sFields = implode(",",$Fields);
		$sFields .= "$prim";
		if (count($Indexes))
		$sFields .= ",".implode(",",$Indexes);

		if ($Table[Comment]!='')
		$Comment = "COMMENT = \"$Table[Comment]\"";
		$SQL = "CREATE TABLE `$Table[Name]` ($sFields)  $Comment TYPE=$Table[Type]";
		$this->Execute($SQL);
	}

	function AlterTable($table,$Field,$command="CHANGE")
	{
		$size = "";
		$def = "";
		$unsig = "";
		$notnull = "";
		$autoinc = "";
		if ($Field[Length]!='')
		$size = "(".$Field[Length].")";
		if ($Field["Default"]!='')
		{
			if (!in_array($Field[Type],array('DATE','DATETIME','TIMESTAMP','TIME','TINYBLOB','TINYTEXT','BLOB','TEXT','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT')))
			$def = "DEFAULT '".$Field["Default"]."'";
		}
		if ($Field[IsUnsigned]!='')
		$unsig = "UNSIGNED";
		$iEx = 0;
		$iExPrimary = 0;
		$res = $this->Show("SHOW KEYS FROM `$table`");
		foreach($res as $v)
		{
			if ($v['Key_name']==$Field['Name'])
			$iEx = 1;
			if ($v['Key_name']=='PRIMARY' && $v['Column_name']==$Field['Name'])
			$iExPrimary = 1;
		}
		if(!$Field[IsIndex])
		{
			$Field[IsAutoincrement] = "";
			$DropIndex = 1;
		}
		if ($Field[IsNotNull]!='')
		$notnull = "NOT NULL";
		if ($Field[IsAutoincrement]!='')
		$autoinc = "AUTO_INCREMENT";
		if (!$Field['OldName'] && $command=='CHANGE')
		$command = "ADD";
		switch($command)
		{
			case "CHANGE":
			$SQL = "ALTER TABLE `$table` CHANGE `$Field[OldName]` `$Field[Name]` $Field[Type] $size $unsig $def $notnull $autoinc";
			break;
			case "ADD":
			$SQL = "ALTER TABLE `$table` ADD `$Field[Name]` $Field[Type] $size $unsig $def $notnull $autoinc";
			break;
			case "RENAME":
			$SQL = "ALTER TABLE `$table` RENAME `$Field[Name]`";
			break;
			case "DROP":
			$SQL = "ALTER TABLE `$table` DROP `$Field[Name]`";
			break;
		}
		$this->Execute($SQL);

		if ($Field[IsIndex] && !$iEx)
		$this->Execute("CREATE ".($Field['IsUnique'] ? "UNIQUE":"")." INDEX `".$Field['Name']."` ON `$table`(`".$Field['Name']."`)");
		if ($DropIndex && $iEx)
		$this->Execute("DROP INDEX `".$Field['Name']."` ON `$table`");
		if ($Field[IsPrimary]!=''&&!$iExPrimary)
		$this->Execute("ALTER TABLE `$table` ADD PRIMARY KEY (`".$Field['Name']."`)");
		elseif(!$Field[IsPrimary]&&$iExPrimary)
		$this->Execute("ALTER TABLE `$table` DROP PRIMARY KEY");
	}

	function GetFieldInfo($sTable,$sField)
	{
		$Res = array();
		$Fld = $GLOBALS['DB']->Show("SHOW FIELDS FROM `$sTable`");
		$Key = $GLOBALS['DB']->Show("SHOW KEYS FROM `$sTable`");
		$keys = array();
		foreach($Key as $v)
		if (!isset($keys[$v['Column_name']]))
		$keys[$v['Column_name']] = array('NAME'=>$v['Key_name'],'NON_UNIQUE'=>$v['Non_unique'],'IS_INDEX'=>$v['Seq_in_index']);
		foreach($Fld as $val)
		{
			if($val['Field'] == $sField)
			{
				$Res['Name'] = $val['Field'];
				preg_match("/^(\w+)\(?([^)]+)?\)?\s*(unsigned)?$/",$val['Type'],$matches);
				list($dummy,$type,$size,$unsigned) = $matches;
				$Res['Type'] = strtoupper($type);
				$Res['Length'] = $size;
				$Res['IsAutoincrement'] = ($val['Extra'] == 'auto_increment' ? 1 :0);
				$Res['IsUnsigned'] = ($unsigned ? 1:0);
				$Res['IsNotNull'] = ($val['Null'] == 'YES' ? 0 : 1);
				$Res['Default'] = $val['Default'];
				if (isset($keys[$Res['Name']]))
				{
					$Res['IsIndex'] = $keys[$Res['Name']]['IS_INDEX'];
					$Res['IsUnique'] = !$keys[$Res['Name']]['IS_UNIQUE'];
					$Res['IsPrimary'] = ($keys[$Res['Name']]['NAME'] == 'PRIMARY' ? 1 : 0);
				}
			}
		}
		return $Res;
	}

	function GetIdentFields($sTable)
	{
		$Fields = array();
		$Unique = "";
		$Key = $GLOBALS['DB']->Show("SHOW KEYS FROM `$sTable`");
		if (count($Key))
		foreach($Key as $v)
		{
			if ($v['Key_name'] == "PRIMARY" || $v['Non_unique'] == 0)
			$Unique = $v['Column_name'];
			$Fields[] = $v['Column_name'];
		}
		else
		{
			$Fld = $GLOBALS['DB']->Show("SHOW FIELDS FROM `$sTable`");
			foreach($Fld as $v)
			$Fields[] = $v['Field'];
		}
		return ($Unique ? $Unique : $Fields);

	}


	/* parse array into SQL query
	can be:
	array(val => array(1,2,3,4,5))										: parsed into VAL IN (1,2,3,4,5)
	array(val => array(from=>45),val => array(to=>45))				: VAL <=45 AND VAL => 45
	arary(val => array(like=>'foo'))										: VAL LIKE '%foo%'
	arary(val => array('!like'=>'foo'))									: VAL NOT LIKE '%foo%'
	array(val => array(not=>45))											: VAL NOT 45
	array(val => array(not=>array(1,2,3,4,5)))						: VAL NOT IN (1,2,3,4,5)
	array(val => array(SQL=>" Name='Text' OR NickName='Text'"))	: ready SQL query

	*/
	function PrepareSQLFilter($Params,$Cond = 'AND')
	{
		$SQL = "";
		if(count($Params))
		foreach($Params as $k=>$v)
		{
			if (is_array($v))
			{
				$In = array();
				foreach($v as $kl=>$vl)
				{
					switch((string)$kl)
					{
						case "from":
						$SQL .= " $Cond $k >= '$vl'";
						break;
						case "to":
						$SQL .= " $Cond $k <= '$vl'";
						break;
						case "not":
						if (is_array($vl))
						{
							$NIn = array();
							foreach($vl as $vn)
							$NIn[] = $vn;
							$SQL .= " $Cond $k NOT IN ('".implode("','",$NIn)."')";
						}
						else
						$SQL .= " $Cond $k != '$vl'";
						break;
						case "like":
						$SQL .= " $Cond $k LIKE '%$vl%'";
						break;
						case "!like":
						$SQL .= " $Cond $k NOT LIKE '%$vl%'";
						break;
						case "SQL":
						$SQL .= " $Cond ($vl)";
						break;
						default:
						$In[] = $vl;
					}
				}
				if (count($In))
				$SQL .= " $Cond $k IN ('".implode("','",$In)."')";
			}
			else
			$SQL .= " $Cond $k='$v'";
		}
		return $SQL;
	}
}

function fsort($a,$b)
{
	if ($a[Priority] == $b[Priority]) return 0;
	return ((int)$a[Priority] < (int)$b[Priority]) ? -1 : 1;
}

//        Parse text to SQL array
function ParseSQLFromText($text)
{
	$SQLres = array();
	$SQLClear = "";
	$sharp = 0;
	$star = 0;
	for ($i = 0;$i<strlen($text);$i++)
	{
		if ($text{$i} == "#" && !$sharp)
		$sharp = 1;
		if ($text{$i} == "\n" && $sharp)
		$sharp = 0;
		if (!$star && $text{$i}.$text{$i+1} == "/*")
		$star = 1;
		if (!$sharp && !$star)
		$SQLClear .= $text{$i};
		if ($star && $text{$i-1}.$text{$i} == "*/")
		$star = 0;
	}
	$strings = array();
	$string = "";
	$squote = 0;
	$dquote = 0;
	for ($i = 0;$i<strlen($SQLClear);$i++)
	{
		if (!$squote && $SQLClear{$i} == "'" && $SQLClear{$i-1}!='\\')
		$squote = 1;
		elseif($squote && $SQLClear{$i} == "'" && $SQLClear{$i-1}!='\\')
		$squote = 0;
		if (!$dquote && $SQLClear{$i} == '"' && $SQLClear{$i-1}!='\\')
		$dquote = 1;
		elseif($dquote && $SQLClear{$i} == '"' && $SQLClear{$i-1}!='\\')
		$dquote = 0;
		if ($SQLClear{$i} == ';' && !$squote && !$dquote)
		{
			$string = trim($string);
			if ($string != '')
			$strings[] = $string;
			$string = "";
		}
		else
		$string .= $SQLClear{$i};
	}
	return $strings;
}
?>