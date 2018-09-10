<?php

/* INCLUDE REQUIRED FILES */
require_once(dirname(dirname(__FILE__)).'/ajax-loader.php');

/* COLLECT VARS */
$imei = $_GET['imei'];

$mp = mongopress_load_mp();
    $default_options = $mp->options();
    $m = mongopress_load_m();
    $db = $m->$default_options['db_name'];
       $tablets = $db->tablets;	

$tabletsitem = $db->tablets->findOne(array(
						'imei' => $imei
					));
			
			
			if($tabletsitem['autoupdate'] == 1){		
				$update = $tablets->update(array("imei" => $imei), array('$set' => array('accessed'=> time())));
			}
$tabletsitem = $db->tablets->findOne(array(
						'imei' => $imei
					));


/* RETURN OBJECT */
mp_json_send($tabletsitem);