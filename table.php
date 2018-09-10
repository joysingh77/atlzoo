<?php

/* INCLUDE REQUIRED FILES */
require_once(dirname(dirname(__FILE__)).'/ajax-loader.php');

/* GET VARS FROM QUERY */
$selected_collection = sanitize_text_field($_GET['collection']);
$allow_edits = (int)($_GET['allow_edits']);
if(empty($selected_collection)){$selected_collection='objs';}
$offset = sanitize_text_field((int)$_GET['iDisplayStart']);
if(empty($offset)){ $offset=0; }
$limit = sanitize_text_field((int)$_GET['iDisplayLength']);
$sort_direction = sanitize_text_field($_GET['sSortDir_0']);
if($sort_direction=='asc'){ $order_value=-1; }else{ $order_value=1; }
$order_by_id = sanitize_text_field((int)$_GET['iSortCol_0']);
$nonce = sanitize_text_field($_GET['nonce']);
$use_mongo_meantime = sanitize_text_field($_GET['use_mongo_meantime']);
if($use_mongo_meantime!='true'){ $use_mongo_meantime=false; }else{ $use_mongo_meantime=true; }
if($order_by_id==1){
    $order_by = 'type';
}elseif($order_by_id==3){
    $order_by = 'title';
}elseif($order_by_id==4){
    $order_by = 'created';
}elseif($order_by_id==5){
    $order_by = 'updated';
}else{
	$order_by = 'updated';
}




mp_json_nonce_check($nonce,'objects-form');

/* CONNECT TO MONGO */
$mp = mongopress_load_mp();
$default_options = $mp->options();
$m = mongopress_load_m();
$db = $m->$default_options['db_name'];
$users = $db->$default_options['user_names'];
$objs = $db->$default_options['obj_col'];
$slugs = $db->$default_options['slug_col'];
if($selected_collection=='objs'){
    $total_objects = $objs->count();
    if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
foreach($all_objects as $obj) {
    foreach($obj['_id'] as $key => $this_mongo_id){}
    	$this_type = $obj['type'];
    	$this_slug_id = $obj['slug_id'];
    	$this_slug_mongo_id = new MongoId($this_slug_id);
    	$this_slug_array = $slugs->findOne(array("_id"=>$this_slug_mongo_id));
    	$this_slug = $this_slug_array['slug'];
    	$this_title = $obj['title'];
    if($use_mongo_meantime){
		$this_created = mingo_meantime($obj['created'], false, 'd / M / Y');
		$this_updated = mingo_meantime($obj['updated'], false, 'd / M / Y');
    }else{
        $this_created = $obj['created'];
        $this_updated = $obj['updated'];
    }
    if($default_options['skip_htaccess']){
        $this_object_url = $default_options['root_url'].'?obj='.$this_mongo_id;
    }else{
        /* TODO: BUILD THIS OUT AS A FUNCTION */
        $this_object_url = $default_options['root_url'].$mp->get_slug_from_obj_id($this_mongo_id);
    }
    	$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
	//$this_action_set = '<a href="'.$this_object_url.'" class="view-object" '.mp_get_attr_filter('table.php','a',$this_object_url,'','view-object','data-mongo-id="'.$this_mongo_id.'" data-form="object" target="_blank"').'>'.__('VIEW').'</a>';
    if($allow_edits){
        $this_action_set.= '<a href="#" class="edit-object" title="'.__('edit this object').'" '.mp_get_attr_filter('table.php','a','#','','edit-object','data-mongo-id="'.$this_mongo_id.'" data-form="object"').'>'.__('EDIT').'</a>';
    }
    	$this_action_set.= '<a href="#" class="delete-object" title="'.__('delete this object').'" '.mp_get_attr_filter('table.php','a','#','','delete-object','data-mongo-id="'.$this_mongo_id.'"  data-form="object"').'>'.__('DELETE').'</a>';
    /* LAST MINUTE CHANGES */
		$this_slug = '<a href="'.$this_object_url.'" title="'.__('click to view object').'">'.$this_slug.'</a>';
		$this_slug = apply_filters('mp_object_slug_in_table', $this_slug, $this_mongo_id);
	if($allow_edits){
		$this_title = '<a href="#" class="edit-object" title="'.__('click to edit object').'" '.mp_get_attr_filter('table.php','a','#','','edit-object','data-mongo-id="'.$this_mongo_id.'" data-form="object"').'>'.$this_title.'</a>';
	}else{
		$this_title = $this_title;
	}
		$this_title = apply_filters('mp_object_title_in_table', $this_title, $this_mongo_id);
		$output['aaData'][] = array($checkbox, $this_type, $this_slug, $this_title, $this_created, $this_updated, $this_action_set);
	}
	mp_json_send($output);
} elseif($selected_collection=='habitat'){
	$objs = $db->habitat;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}
		$this_title = $obj['title'];
		$this_created = $obj['created'];
		$this_updated = $obj['updated'];
		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
		if($allow_edits){
        	$this_action_set.= '<a href="#" class="edit-object" title="'.__('edit this habitat').'" '.mp_get_attr_filter('table.php','a','#','','edit-object','data-mongo-id="'.$this_mongo_id.'" data-form="object"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a href="#" class="delete-object" title="'.__('delete this habitat').'" '.mp_get_attr_filter('table.php','a','#','','delete-habitat','data-mongo-id="'.$this_mongo_id.'"  data-form="object"').'>'.__('DELETE').'</a>';
			$output['aaData'][] = array($checkbox,$this_title, $this_created, $this_updated, $this_action_set);
	}

	mp_json_send($output);
}elseif($selected_collection=='species'){
	$objs = $db->species;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
		if($_REQUEST[''])
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
    $all_objects = $all_objects->sort( array('name' => 1));
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}
		$name = '<a href="'.$default_options['root_url'].'admin/edit-species?id='.$this_mongo_id.'">'.$obj['name'].'</a>';
		$genus = $obj['genus'];
		$genus = $obj['genus'];
		if($obj['venomous'] == 1){
			$venomous = 'venomous';
		} else{
			$venomous = "Non venomous";
		}
		$url = str_replace(' ','-',$obj['name']).'.html';
		$last_modify = $obj['last_modify'];
		$this_created = mingo_meantime($obj['created'], false, 'd / M / Y');
		$this_updated = mingo_meantime($obj['updated'], false, 'd / M / Y');
		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
		if($allow_edits){
        	$this_action_set.= '';
    	}
    		$this_action_set.= '<a href="#" class="delete-species" title="'.__('delete this species').'" '.mp_get_attr_filter('table.php','a','#','','delete-habitat','data-mongo-id="'.$this_mongo_id.'"  data-form="species_list"').'>'.__('DELETE').'</a>';
			$output['aaData'][] = array($name, $genus, $last_modify , $this_updated , $this_action_set);
	}

	mp_json_send($output);

}elseif($selected_collection=='dashboard_species'){
	$objs = $db->species;
	$total_objects = 10;
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit(10);
    }else{
        $all_objects = $objs->find()->sort(array("updated"=> -1))->limit(10);
    }
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => 1,
    "aaData" => array()
	);
	$all_objects = $all_objects->sort( array('updated' => -1));
	
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}
		$name = $obj['name'];
		$genus = $obj['genus'];
		$genus = $obj['genus'];
		if($obj['venomous'] == 1){
			$venomous = 'venomous';
		} else{
			$venomous = "Non venomous";
		}
		$url = str_replace(' ','-',$obj['name']).'.html';
		$last_modify = $obj['last_modify'];
		$this_created = mingo_meantime($obj['created'], false, 'd / M / Y');
		$this_updated = mingo_meantime($obj['updated'], false, 'd / M / Y');
	
		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
		if($allow_edits){
        	$this_action_set.= '<a href="'.$default_options['root_url'].'admin/view-species?id='.$this_mongo_id.'"><img src="http://sites.amd.com/Style%20Library/Images/AMD/search_icon.png" style="width: 12px; margin-bottom: -8px;"></a><a href="'.$default_options['root_url'].'admin/edit-species?id='.$this_mongo_id.'" class="edit-species" title="'.__('edit this species').'" '.mp_get_attr_filter('table.php','a','#','','edit-species','data-mongo-id="'.$this_mongo_id.'" data-form="object"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a href="#" class="delete-object" title="'.__('delete this species').'" '.mp_get_attr_filter('table.php','a','#','','delete-habitat','data-mongo-id="'.$this_mongo_id.'"  data-form="object"').'>'.__('DELETE').'</a>';
			$output['aaData'][] = array($name, $last_modify , $this_updated);
	}

	mp_json_send($output);

}elseif($selected_collection=='tablets'){
	$order_value = "tablet_id";
	$objs = $db->tablets;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
	if(isset($_GET['sort'])){
		
	} else {
		$all_objects = $all_objects->sort( array('tablet_id' => 1));
	}	
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}

		$imei = $obj['imei'];
		$model = $obj['model'];
		
		if(isset($obj['update'])){
			$updated = $obj['update'];
		} else {
			$updated = 0;
		}
		if(isset($obj['autoupdate'])){
			if($obj['autoupdate'] == 1)
			{
				$updated = $obj['update'];
				$checkbox = '<input type="checkbox" checked name="delete" value="delete" class="autoupdate" data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist" />';
			} else {
				$updated = 0;
				$checkbox = '<input type="checkbox" name="delete" value="delete" class="autoupdate" data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist" />';
			}
		} else {
			$updated = 0;
			$checkbox = '<input type="checkbox" name="delete" value="delete" class="autoupdate" data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist" />';
		}
		
		$time1 = time();
		$time2 = $obj['accessed'];
		$diff_seconds = $time1 - $time2;
		$obj['last_access'] = round($diff_seconds/60);
		
		$inputSeconds = $diff_seconds;
		$secondsInAMinute = 60;
		$secondsInAnHour  = 60 * $secondsInAMinute;
		$secondsInADay    = 24 * $secondsInAnHour;
		$secondsInMonth = 30 * $secondsInADay;
		$secondsInYear = 12 * $secondsInMonth;
		
		// extract years
		$years = floor($inputSeconds / $secondsInYear);
		
		// extract months
		$monthSeconds = $inputSeconds % $secondsInYear;
		$months = floor($monthSeconds / $secondsInMonth);
		
		// extract days
		$daySeconds = $monthSeconds % $secondsInMonth;
		$days = floor($daySeconds / $secondsInADay);
		
		// extract hours
		$hourSeconds = $daySeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);
		
		// extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);
		
		// extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);
		
		$collection = $db->interval;
		$interval = $collection->find();
		foreach($interval as $time){
			foreach($time['_id'] as $key => $value){
				$id = $value;
			}
		}
		
		if($obj['last_access'] > $time['interval'] + 5) {
			if($years > 0) {
				if($years == 1) {
					$last_min = '<span style="color:red;">'.$years.' Year ago';
				}else{
					$last_min = '<span style="color:red;">'.$years.' Years ago';
				}				
			}
			elseif($months > 0) {
				if($months == 1) {
					$last_min = '<span style="color:red;">'.$months.' Month ago';
				}else{
					$last_min = '<span style="color:red;">'.$months.' Months ago';
				}
			}
			elseif($days > 0) {
				if($days == 1) {
					$last_min = '<span style="color:red;">'.$days.' Day ago';
				}else{
					$last_min = '<span style="color:red;">'.$days.' Days ago';
				}
			}
			elseif($hours > 0) {
				if($hours == 1) {
					$last_min = '<span style="color:red;">'.$hours.' Hour ago';
				}else{
					$last_min = '<span style="color:red;">'.$hours.' Hours ago';
				}
			}else{
				$last_min = '<span style="color:red;">'.$minutes.' Minutes ago</span>';
			}
		}else {
			$last_min = '<span style="color:green;">'.$minutes.' Minutes ago</span>';
		}
		
		$id = '<a href="#" class="edit-tablet-popup" title="edit this tablet" data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist">'.$obj['tablet_id'].'</a>'; 
		$this_action_set = '';
		if($allow_edits){
        	//$this_action_set.= '<a href="#" class="edit-tablet" title="'.__('edit this tablet').'" '.mp_get_attr_filter('table.php','a','#','','edit-tablet','data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a href="#" class="delete-tablet" title="'.__('delete this tablet').'" '.mp_get_attr_filter('table.php','a','#','','delete-tablet','data-mongo-id="'.$this_mongo_id.'"  data-form="tabletslist"').'>'.__('DELETE').'</a>';
    		if($obj['model'] != "virtual")
		{
			$output['aaData'][] = array($checkbox,$id, $imei , $model,$obj['location'],$updated,$last_min ,$this_action_set);
		} else {
			$output['aaData'][] = array($checkbox,$id, $imei , $model,$obj['location'],$updated,'-' ,$this_action_set);
		}	
	}

	mp_json_send($output);

}elseif($selected_collection=='dashbord_tablets'){
	$order_value = "tablet_id";
	$objs = $db->tablets;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
	if(isset($_GET['sort'])){
		
	} else {
		$all_objects = $all_objects->sort( array('accessed' => -1));
	}
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}

		$imei = $obj['imei'];
		$model = $obj['model'];
		
		if(isset($obj['update'])){
			$updated = $obj['update'];
		} else {
			$updated = 0;
		}
		
	$time1 = time();
		$time2 = $obj['accessed'];
		$diff_seconds = $time1 - $time2;
		$obj['last_access'] = round($diff_seconds/60);
		
		$inputSeconds = $diff_seconds;
		$secondsInAMinute = 60;
		$secondsInAnHour  = 60 * $secondsInAMinute;
		$secondsInADay    = 24 * $secondsInAnHour;
		$secondsInMonth = 30 * $secondsInADay;
		$secondsInYear = 12 * $secondsInMonth;
		
		// extract years
		$years = floor($inputSeconds / $secondsInYear);
		
		// extract months
		$monthSeconds = $inputSeconds % $secondsInYear;
		$months = floor($monthSeconds / $secondsInMonth);
		
		// extract days
		$daySeconds = $monthSeconds % $secondsInMonth;
		$days = floor($daySeconds / $secondsInADay);
		
		// extract hours
		$hourSeconds = $daySeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);
		
		// extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);
		
		// extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);
		
		$collection = $db->interval;
		$interval = $collection->find();
		foreach($interval as $time){
			foreach($time['_id'] as $key => $value){
				$id = $value;
			}
		}
		
		if($obj['last_access'] > $time['interval']) {
			if($years > 0) {
				if($years == 1) {
					$last_min = '<span style="color:red;">'.$years.' Year ago';
				}else{
					$last_min = '<span style="color:red;">'.$years.' Years ago';
				}				
			}
			elseif($months > 0) {
				if($months == 1) {
					$last_min = '<span style="color:red;">'.$months.' Month ago';
				}else{
					$last_min = '<span style="color:red;">'.$months.' Months ago';
				}
			}
			elseif($days > 0) {
				if($days == 1) {
					$last_min = '<span style="color:red;">'.$days.' Day ago';
				}else{
					$last_min = '<span style="color:red;">'.$days.' Days ago';
				}
			}
			elseif($hours > 0) {
				if($hours == 1) {
					$last_min = '<span style="color:red;">'.$hours.' Hour ago';
				}else{
					$last_min = '<span style="color:red;">'.$hours.' Hours ago';
				}
			}else{
				$last_min = '<span style="color:red;">'.$minutes.' Minutes ago</span>';
			}
		}else {
			$last_min = '<span style="color:green;">'.$minutes.' Minutes ago</span>';
		}
		
		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist" />';
		$this_action_set = '';
		if($obj['model'] != "virtual")
		{
		if($allow_edits){
        	$this_action_set.= '<a href="#" class="edit-tablet" title="'.__('edit this tablet').'" '.mp_get_attr_filter('table.php','a','#','','edit-tablet','data-mongo-id="'.$this_mongo_id.'" data-form="tabletslist"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a href="#" class="delete-tablet" title="'.__('delete this tablet').'" '.mp_get_attr_filter('table.php','a','#','','delete-tablet','data-mongo-id="'.$this_mongo_id.'"  data-form="tabletslist"').'>'.__('DELETE').'</a>';
    		if($obj['last_access'] > $time['interval']){	
				$output['aaData'][] = array($obj['tablet_id'], $obj['location'],$last_min ,$this_action_set);
			}
		}	
	}

	mp_json_send($output);

}elseif($selected_collection=='map'){
	$order_value = "tablet_id";
	$objs = $db->tablets;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
	$all_objects = $all_objects->sort( array('tablet_id' => 1));
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}

		$tablet_id = $obj['tablet_id'];
		$name = $obj['commonname'];
		$description = $odj['description'];
		$url = $obj['url'];
		$views = $obj['views'];
		if($obj['last_access'] > 10){
			$last_min = '<span style="color:red;">'.$obj['last_access'].' Minutes ago</span>';
		} else {
			$last_min = '<span style="color:green;">'.$obj['last_access'].' Minutes ago</span>';
		}

		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
		if($allow_edits){
        	$this_action_set.= '<a href="#" class="edit-object" title="'.__('edit this habitat').'" '.mp_get_attr_filter('table.php','a','#','','edit-object','data-mongo-id="'.$this_mongo_id.'" data-form="object"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a href="#" class="delete-object" title="'.__('delete this habitat').'" '.mp_get_attr_filter('table.php','a','#','','delete-habitat','data-mongo-id="'.$this_mongo_id.'"  data-form="object"').'>'.__('DELETE').'</a>';
			$output['aaData'][] = array($checkbox,$tablet_id, $name,'this is test description' ,$this_action_set);
	}

	mp_json_send($output);

} elseif($selected_collection=='user'){
		$objs = $db->user;
	$total_objects = $objs->count();
	if((!empty($order_by))&&(!empty($order_value))){
        $all_objects = $objs->find()->sort(array($order_by => $order_value))->limit($limit)->skip($offset);
    }else{
        $all_objects = $objs->find()->limit($limit)->skip($offset);
    }
    $these_objects = $all_objects->count();
	$output = array(
    "sEcho" => (int)($_GET['sEcho']),
    "iTotalRecords" => $total_objects,
    "iTotalDisplayRecords" => $these_objects,
    "aaData" => array()
	);
	foreach($all_objects as $obj) {
		foreach($obj['_id'] as $key => $this_mongo_id){}

		$tablet_id = $obj['tablet_id'];
		$name = $obj['name'];
		$email = $obj['email'];
		$un = $obj['un'];
		$views = $obj['views'];
		if($obj['last_access'] > 10){
			$last_min = '<span style="color:red;">'.$obj['last_access'].' Minutes ago</span>';
		} else {
			$last_min = '<span style="color:green;">'.$obj['last_access'].' Minutes ago</span>';
		}

		$checkbox = '<input type="checkbox" name="delete" value="delete" class="delete-me" data-mongo-id="'.$this_mongo_id.'" data-form="object" />';
		$this_action_set = '';
		if($allow_edits){
        	$this_action_set.= '<a  href="#" class="edit-user" title="'.__('edit this user').'" '.mp_get_attr_filter('table.php','a','#','','edit-object','data-mongo-id="'.$this_mongo_id.'" data-form="users"').'>'.__('EDIT').'</a>';
    	}
    		$this_action_set.= '<a onclick="delete_user("'.$this_mongo_id.'");" href="#" class="delete-user" title="'.__('delete this user').'" '.mp_get_attr_filter('table.php','a','#','','delete-habitat','data-mongo-id="'.$this_mongo_id.'"  data-form="users"').'>'.__('DELETE').'</a>';
			$output['aaData'][] = array($checkbox,$name, $email , $un ,$this_action_set);
	}

	mp_json_send($output);
}


