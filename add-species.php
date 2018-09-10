<?php
require_once(dirname(dirname(__FILE__)).'/ajax-loader.php');

	$mp = mongopress_load_mp();
	$default_options = $mp->options();
	$m = mongopress_load_m();
	$db = $m->$default_options['db_name'];
	$db_name = $default_options['db_name'];
	$collection = 'species';
if ($db->system->namespaces->findOne(array('name'=>$db_name.$collection)) === null){
	  $collection = $db->createCollection("species");
	  $collection = $db->species;         
}else{
	  $collection = $db->species;
}

if($_REQUEST['mp']['mongo_id'] != ''){
	$document = $_REQUEST['sp'];
	if(isset($document['ssp'])){
		$document['ssp'] = 1;
	} else{
		$document['ssp'] = 0;
	}	
	$document['updated'] = time();
	//$document['last_modify'] = getusername();
	$document['positions'] = $_REQUEST["positions"];	
	$document['image_positions'] = $_REQUEST["image_positions"];
	$document['toxininfo'] = trim($document["toxininfo"]);		
	$mongo_id = new MongoId($_REQUEST['mp']['mongo_id']);				
	
	$update = $collection->update(array("_id" => $mongo_id), array('$set' => $document));			
	updatetablets($_REQUEST['mp']['mongo_id']);
	$success = array("message"=>"Species Updated Successfully");
	//print_r($document);
	echo json_encode($success);
} else {
	$species = $collection->find(array('name'=>$_REQUEST['name']));
	if(count($species) > 0){		
		$document = $_REQUEST['sp'];		
		$document['positions'] = $_REQUEST["positions"];
		$document['image_positions'] = $_REQUEST["image_positions"];
		//$document['last_modify'] = getusername();
		$document['updated'] = time();
		$document['_id'] = new MongoId();				

		$insert = $collection->insert($document);
		$uploads = $db->uploads;
		$update_img = $uploads->find(array('sno'=>strval($document['sno'])));
		foreach ($document['_id'] as $key=>$value){
			$id = $value;	
		}
		foreach($update_img as $img){			
			$update = $uploads->update(array("_id" => $img['_id']), array('$set' => array('species'=>$id)));			
		}	
		$success = array("message"=>"Species Inserted Successfully");
		echo "<pre>";
		print_r($document);
		exit;
		echo json_encode($success);	
	} else {
		echo "Record Already there";
	}
	
}


function updatetablets($id)
{
		$mp = mongopress_load_mp();
		$default_options = $mp->options();
		$m = mongopress_load_m();
		$db = $m->$default_options['db_name'];	
		$map = $db->map;
		$tablets = $db->tablets;
		
		$tablet_map = $mp->arrayed($map->find(array('species'=>$id)));	
					
		foreach($tablet_map as $map_t)
		{					
    		$item = $db->tablets->findOne(array(
						'imei' => $map_t['imei']
					));
			
			print_r($item);
			if($item['autoupdate'] == 1){		
				$update = $tablets->update(array("imei" => $map_t['imei']), array('$set' => array('update'=> 1)));
                                $update = $tablets->update(array("imei" => $map_t['imei']), array('$set' => array('accessed'=> time())));
			}
			
		}	
}

function getusername()
{
		$mp = mongopress_load_mp();
		return $mp->get_current_user();	
}
?>

