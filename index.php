<?
	set_time_limit(600);
	session_start();
	include_once('config.php');

	$DB = new cMySQL();
	$DB->Connect();
	$Pages = array('Current'=>$_REQUEST['p']);
	
	auth();

	// ok, valid username & password
	$action = $_REQUEST['action'] ;

	if ($_REQUEST['appl'])
		$_SESSION['app'] = $_REQUEST['appl'];

	if (!$_SESSION['app'] && $action!='admin' && $action != 'backup_now' && $action != 'tree' && $action != 'edit_vals')
	{
		switch ($action)
		{
			case "ch_app":
				if ($_REQUEST['title'])
				{
					$vals = get_values();
					$arr = array(
						'title' => $_REQUEST['title'],	
						'type' => ($_REQUEST['type'] ? $_REQUEST['type'] : 'app'),	
						'parent_id' => (int)($_REQUEST['tid'] ? $_REQUEST['tid'] : $_REQUEST['eid']),
						'appid' => $_REQUEST['appid'],
						'updated' => $_REQUEST['updated'],
						'cname' => $_REQUEST['cname'],	
						'cemail' => $_REQUEST['cemail'],	
						'cpassword' => $_REQUEST['cpassword'],	
						'crep' => $_REQUEST['crep'],	
						'crepemail' => $_REQUEST['crepemail'],	
						'id' => $_REQUEST['id']
					);
					$id = add_value($arr);
					
					if (!$_REQUEST['id'] || ($_REQUEST[ordtab] && $_REQUEST[ord]))
					{
						// calc priority
						$prv = 0;
						$prio = 0;
						$nxt = 0;
						if ($_REQUEST[ordtab])
						foreach($vals as $k=>$v)
						{
							if ($v[id] == $_REQUEST[ordtab])
							{
								$prio = $v[priority];
								if (count($vals)>$k)
									$nxt = $vals[$k+1][priority];
								break;
							}
							$prv = $v[priority];
						}
						if (!$_REQUEST['id'])
							$arr[id] = $id;
						if (!$_REQUEST[ord] || $_REQUEST[ord] == 'after')
							$arr[priority] = ($nxt ? $prio + ceil(($nxt - $prio) / 2) : $id * 100);
						if ($_REQUEST[ord] == 'before')
							$arr[priority] = ($prv ? $prio - ceil(($prio - $prv) / 2) : $id * 90);

						add_value($arr);
					}

					if ($_REQUEST['tid'])
						$_REQUEST['id'] = $_REQUEST['tid'];
					if ($_REQUEST['eid'])
						$_REQUEST['id'] = $_REQUEST['eid'];
					if ($_REQUEST['id'])
					{
						header("Location: ?action=edit_app&id=".$_REQUEST['id']);
						exit;
					}
				}
			break;
			case "del_val":
				del_value($_REQUEST['id']);
				if ($_REQUEST['eid'])
				{
					header("Location: ?action=edit_app&id=".$_REQUEST['eid']);
					exit;
				}
			break;
			case "edit_app":
				unset($_SESSION['app']);
				$app = get_value($_REQUEST['id']);
				$tabs = get_values($app['id']);
				require_once('views/edit_app.php');
				exit;
			break; 
			default:
				$links = $DB->SelectArray("SELECT *,to_days(now())-to_days(updated) as updated from links WHERE  paused=0");
				$alerts = array();
				foreach($links as $k=>$v)
				{
					$qty = $DB->SelectHash("SELECT COUNT(*) as CNT from items WHERE link_id='$v[id]'",'CNT'); 
					if ($qty == 0)
					{
						$app = get_value($v['app']);
						$alerts[] = array('title'=>$app['title'],'updated'=>(int)$v['updated'],'id'=>$v['app'],'cat'=>$v['cat'],'tab'=>$v['tab']);
					}
				}
				uasort($alerts,"cmpAlerts");

		}
		$vals = get_values(0,0,1);
		require_once('views/app.php');
		exit;
	}

	$vals = get_values(0,0,1);

	if ($_REQUEST['save'])
	{
		if (is_array($_REQUEST[conf]))
		foreach($_REQUEST[conf] as $k=>$v)
		{
			if ($k=='pause')
			{
				$paus = get_config('pause');
				$paus = str_replace("{".$_SESSION['app']."}","",$paus);
				if ($v == '1')
					$paus .= "{".$_SESSION['app']."}";
				set_config('pause',$paus );
			}
			else
			{
				set_config($k,$v);
			}
			if($_REQUEST['out_never'])
			{
				set_config('out_purge_period','999999' );
			}
		}
		$config_saved = 1;
	}

	switch($action)
	{
		case "admin":
			require_once("views/admin.php");
			exit;
		break;
		case "tree":
			require_once("views/tree.php");
			exit;
		break;
		case "logout":
			unset($_SESSION['user']);
			require_once('views/login.php');
			exit;
		break;
		case "napp":
			unset($_SESSION['app']);
			$vals = get_values(0,0,1);

				$links = $DB->SelectArray("SELECT *,to_days(now())-to_days(updated) as updated from links WHERE  paused=0");
				$alerts = array();
				foreach($links as $k=>$v)
				{
					$qty = $DB->SelectHash("SELECT COUNT(*) as CNT from items WHERE link_id='$v[id]'",'CNT'); 
					if ($qty == 0)
					{
						$app = get_value($v['app']);
						$alerts[] = array('title'=>$app['title'],'updated'=>(int)$v['updated'],'id'=>$v['app'],'cat'=>$v['cat'],'tab'=>$v['tab']);
					}
				}
				uasort($alerts,"cmpAlerts");

			require_once('views/app.php');
			exit;
		break;
		case "ch_app":
			if ($_REQUEST['title'])
			{
				$arr = array(
					'title' => $_REQUEST['title'],	
					'type' => $_REQUEST['type'],	
					'parent_id' => $_REQUEST['parent'],
					'id' => $_REQUEST['id']
				);
				add_value($arr);
			}
			header("Location: ?action=edit_vals&type=".$_REQUEST['type']."&parent=".$_REQUEST['parent']);
			exit;
		break;
		case "del_val":
			del_value($_REQUEST['id']);
			header("Location: ?action=edit_vals&type=".$_REQUEST['type']."&parent=".$_REQUEST['parent']);
			exit;
		break;
		case "edit_vals":
			$vals = get_values();
			$type = ($_REQUEST['type'] ? $_REQUEST['type'] : "app");
			require('views/vals.php');
			exit;
		break;
 		case "newfeed":
			if (!$_REQUEST['username'])
				$feed_error = 1;
			else
			{
				$feed = array();
				$feed['link'] = $_REQUEST['username'];
				$feed['period'] = (int)$_REQUEST['password']*$_REQUEST['per'];
				$feed['description'] = $_REQUEST['description'];
				$feed['name'] = $_REQUEST['name'];
				$feed['app'] = $_SESSION['app'];
				$feed['type'] = $_REQUEST['type'];
				$feed['tab'] = $_REQUEST['tab'];
				$feed['cat'] = $_REQUEST['cat'];
				$feed['parse_cat'] = $_REQUEST['parse_cat'];
				$feed['is_dir'] = $_REQUEST['is_dir'];
				$feed['sort'] = $_REQUEST['sort'];
				$feed['pubdate'] = $_REQUEST['pubdate'];
				$feed['auto_microformat'] = $_REQUEST['auto_microformat'];
				if (count($_REQUEST["nfeed"]))
					$feed['add_urls'] = implode("|",$_REQUEST["nfeed"]);
				if (add_feed($feed))
					$feed_added = 1;
				else
					$feed_exists = 1;
				if (isset($_REQUEST[parse_now]))
				{
				
				}
			}
		break;
		case "editfeed":
			$feed = get_feed($_REQUEST['fid']);
			if (!$_REQUEST['username'] || !$_REQUEST['password'])
				$feed_error = 1;
			else
			{
				$feed['link'] = $_REQUEST['username'];
				$feed['period'] = $_REQUEST['password'];
				$feed['description'] = $_REQUEST['description'];
				$feed['name'] = $_REQUEST['name'];
				$feed['app'] =($_REQUEST['app'] ? $_REQUEST['app'] : $_SESSION['app']);
				$feed['tab'] = $_REQUEST['tab'];
				$feed['cat'] = $_REQUEST['cat'];
				$feed['type'] = $_REQUEST['type'];
				$feed['parse_cat'] = $_REQUEST['parse_cat'];
				$feed['is_dir'] = $_REQUEST['is_dir'];
				$feed['sort'] = $_REQUEST['sort'];
				$feed['pubdate'] = $_REQUEST['pubdate'];
				$feed['auto_microformat'] = $_REQUEST['auto_microformat'];
				if (count($_REQUEST["nfeed"]))
					$feed['add_urls'] = implode("|",$_REQUEST["nfeed"]);
				if (edit_feed($feed))				
					$feed_added = 1;
				else
					$feed_exists = 1;
				unset($feed);
			}
			if ($_REQUEST['newfeedrel'])
			{
				header("Location: ?fid=".$_REQUEST['fid']."&action=empty");
				exit;
			}
			if ($_REQUEST['newfeedstart'])
			{
				header("Location: ?fid=".$_REQUEST['fid']."&action=parse_now");
				exit;
			}
		break;
		case "edit":
			$feed = get_feed($_REQUEST['fid']);
			$_REQUEST['app'] = ($_REQUEST['app'] ? $_REQUEST['app'] : $feed['app']);
			$_REQUEST['tab'] = ($_REQUEST['tab'] ? $_REQUEST['tab'] : $feed['tab']);
			$_REQUEST['cat'] = ($_REQUEST['cat'] ? $_REQUEST['cat'] : $feed['cat']);
		break;
		case "delete":
			del_feed($_REQUEST['fid']);
		break;
		case "empty":
			empty_feed($_REQUEST['fid']);
			$is_single = $_REQUEST['fid'];
			include_once('./fetch.php');
			header("Location: ?");
			exit;
		break;
		case "pause":
			$feed = get_feed($_REQUEST['fid']);
			$feed['paused'] = !(int)$feed['paused'];
			edit_feed($feed);
			unset($feed);
		break;
		case "search":
			$_SESSION['s'] = $_REQUEST['s'];
		break;
		case "compare":
			$feed = get_feed($_REQUEST['fid']);
			require_once('views/compare.php');
			exit;
		break;
		case "getsrc":
			$feed = get_feed($_REQUEST['fid']);
			$type = $_REQUEST['type'];
			$link = ($type == 'src' ? $feed['link'] : FULL_URL.'out.php?fid='.$feed['id']);
			$link = trim(str_replace("feed:/","http:/",$link));
			$file = @fopen($link,'rt');
			if (!$file)
			{
				die("Can't open $link: connection error");
			}
			while(!feof($file))
				echo nl2br(htmlspecialchars(fgets($file)));
			fclose($file);
			exit;
		break;
		case "purge":
			purge_items();
			if (!$_REQUEST['adm'])
				exit;
		break;
		case "backup_now":
			include_once('./backup.php');
			header("Location: ?action=admin");
			exit;
		break;
		case "parse_now":
			$is_single = $_REQUEST['fid'];
			include_once('./fetch.php');
			header("Location: ?");
			exit;
		break;
		case "add_value":
			if ($_REQUEST['title'] && $_REQUEST['type'])
			{
				$arr = array(
					'title' => $_REQUEST['title'],	
					'type' => $_REQUEST['type'],	
					'parent_id' => $_REQUEST['parent_id'],	
				);
				add_value($arr);
			}
		break;
		case "del_value":
			del_value($_REQUEST['id']);
		break;
	}
	
	require_once('views/main.php');



?>