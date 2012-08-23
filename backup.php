<?
	chdir('/home/d0ma1ns/public_html/feed/');
	include_once('config.php');

	$DB = new cMySQL();
	$DB->Connect();

	$folder = get_config('backup_to');
	if (!file_exists($folder))
		mkdir($folder);
	$newfold = date("Y_m_d");
	$folder = $folder.$newfold.'/' ;
	@mkdir($folder);
	copyalldir(FULL_PATH.'images',$folder.'images');

	// backup tables
  $tables = $GLOBALS['DB']->Show("SHOW TABLE STATUS");
  {
			 $iQuoted = 1;
			 $date = date("m/d/Y");
			 $res =  "########################################\n";
			 $res .= "#        Created by backuper           \n";
			 $res .= "#        (C) Petkevich Alexander              \n";
			 $res .= "#        mrdoggy@tut.by                       \n";
			 $res .=  "#########################################\n\n";


			 $sDump = $res;
			 $file = $folder."dump_".time().".sql";
			 $fdump = fopen($file,"wt");
			 fputs($fdump,$sDump);
			 foreach($tables as $vc)
			 {
				 $v = $vc['Name'];
						{
								  fputs($fdump,"#\n");
								  fputs($fdump,"#        Table structure for table '$v'\n");
								  fputs($fdump,"#\n\n");
								  fputs($fdump,"DROP TABLE IF EXISTS `".$v."`;\n");
								  fputs($fdump,GetTableStruct($v).";\n\n");
						}

						{
								  fputs($fdump,"#\n");
								  fputs($fdump,"#        Dumping data for table '$v'\n");
								  fputs($fdump,"#\n\n");
								  $rData = $GLOBALS['DB']->SelectRequest("SELECT * FROM `".$v.'`');
								  while($Data = $GLOBALS['DB']->SQLNext($rData))
								  {
											 $mvals = array();
											 foreach($Data as $k=>$vl)
											 {
													if (is_numeric($k))
															$mvals[] = '"'.str_replace('"','\"',$vl).'"';
											 }
											 $sVals = implode(",",$mvals);
											 fputs($fdump,"INSERT INTO `".$v."` VALUES($sVals);\n");
								  }
								  fputs($fdump,"\n");
						}
			 }
			 fclose($fdump);
  }
	set_config('last_backup',date('Y-m-d H:i:s'));
?>