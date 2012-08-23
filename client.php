<?
	set_time_limit(600);
	session_start();
	include_once('config.php');
	$client = 1;

	$DB = new cMySQL();
	$DB->Connect();
	$Pages = array('Current'=>$_REQUEST['p']);
	
	$action = $_REQUEST['action'] ;
	$vals = get_values();

	if ($_REQUEST['appl'])
		$_SESSION['c_app'] = $_REQUEST['appl'];

	if (!$_SESSION['c_app'])
	{
		require_once('views/app.php');
		exit;
	}

	if ($action ==  "napp")
	{
		unset($_SESSION['c_app']);
		require_once('views/app.php');
		exit;
	}

	if ($action == "viewrec")
	{
		$id = $_REQUEST['id'];
		$item = get_item($id);
		$c_tab = $_REQUEST['c_tab'];
		$items = get_items($item['link_id']);
		foreach($items as $k=>$v)
		{
			if ($v[id] != $id)
				$prev = $v[id];
			else
			{
				if (count($items) >= $k+1)
					$next = $items[$k+1][id];
				break;
			}
		}

		require_once('views/viewrec.php');
		exit;
	}

	if ($action == "viewapp")
	{
		$feeds = get_feeds($_SESSION['c_app']);
		$c_tab = $_REQUEST['c_tab'];
		if (!$c_tab)
			foreach($vals as $k=>$v)
				if ($v['parent_id'] == $_SESSION['c_app'] && !$c_tab)
					$c_tab = $v['id'];
		require_once('views/viewapp.php');
		exit;
	}


	if ($action == 'text')
		require_once('views/client_text.php');
	else
		require_once('views/client.php');
?>