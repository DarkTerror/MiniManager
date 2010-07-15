<?php

// page header, and any additional required libraries
require_once 'tab_lib.php';

//##########################################################################################
//Delete character soap & above method
function del_char($guid, $realm)
{
	global 	$characters_db, $realm_db,
			$user_lvl, $user_id,
			$tab_del_user_characters, $tab_del_pet,$remote_soap,$soap_enable;

	$sqlr = new SQL;
	$sqlc = new SQL;
	$sqlr->connect($realm_db['addr'], $realm_db['user'], $realm_db['pass'], $realm_db['name']);
	$sqlc->connect($characters_db[$realm]['addr'], $characters_db[$realm]['user'], $characters_db[$realm]['pass'], $characters_db[$realm]['name']);

	$query = $sqlc->query('SELECT account, online 
							FROM characters 
							WHERE guid = '.$guid.' LIMIT 1');
	$owner_acc_id = $sqlc->result($query, 0, 'account');

	$owner_gmlvl = $sqlr->result($sqlr->query('SELECT gmlevel 
												FROM account 
												WHERE id = '.$owner_acc_id.''), 0);

	if ( ($user_lvl > $owner_gmlvl) || ($owner_acc_id == $user_id) )
	{
		if ($sqlc->result($query, 0, 'online'));
		else
		{
		  // soap_enable (1 =on, 2=off) -- edit config.dist.php
			switch ($soap_enable) 
			{
				case 1:
					
					$name = $sqlc->result($sqlc->query('SELECT name FROM characters WHERE guid = '.$guid.''), 0);
					$command = "character erase ".$name;

					$client = new SoapClient(NULL,
					array(
						"location" => "http://".$remote_soap[0].":".$remote_soap[1]."/",
						"uri" => "urn:MaNGOS",
						"style" => SOAP_RPC,
						"login" => $remote_soap[2],
						"password" => $remote_soap[3],
					));
						try
							{
							$result = $client->executeCommand(new SoapParam($command, "command"));
							$message = "delete";
	
							}
							catch(Exception $e)
							{
							$message = "ERROR delete ";
							}
							return $message."<br />";	
									
				break;
    
				case 0:
					//Delete above method pending repair
						
			
				break;
				
			}
		
		}
	}
  return false;
}

//##########################################################################################
//Delete Account - return array(deletion_flag , number_of_chars_deleted)
function del_acc($acc_id)
{
	global 	$characters_db, $realm_db,
			$user_lvl, $user_id,
			$tab_del_user_realmd, $tab_del_user_char, $tab_del_user_characters, $tab_del_pet, $remote_soap, $soap_enable;

	$del_char = 0;

	$sqlc = new SQL;
	$sqlr = new SQL;
	$sqlr->connect($realm_db['addr'], $realm_db['user'], $realm_db['pass'], $realm_db['name']);

	$query = $sqlr->query('SELECT gmlevel, active_realm_id 
							FROM account 
							WHERE id ='.$acc_id.'');

	$gmlevel = $sqlr->result($query, 0, 'gmlevel');

	if ( ($user_lvl > $gmlevel)||($acc_id == $user_id) )
	{
		if ($sqlr->result($query, 0, 'active_realm_id'));
		else
		{
		
			switch ($soap_enable) 
			{
				case 1:
					
					//$name_acc = $sqlc->result($sqlc->query('SELECT username FROM account WHERE id = '.$acc_id.''), 0);
					//$command = "account delete ".$name;
					$command = "account delete ".$acc_id;	
					$client = new SoapClient(NULL,
					array(
						"location" => "http://".$remote_soap[0].":".$remote_soap[1]."/",
						"uri" => "urn:MaNGOS",
						"style" => SOAP_RPC,
						"login" => $remote_soap[2],
						"password" => $remote_soap[3],
					));
						try
							{
							$result = $client->executeCommand(new SoapParam($command, "command"));
							$message = "delete";
	
							}
							catch(Exception $e)
							{
							$message = "ERROR delete ";
							}
							return $message."<br />";	
									
				break;
    
				case 0:
					//Delete above method pending repair
						
			
				break;
			}
		
		}
	}
	return array(false, $del_char);
}


//##########################################################################################
//Delete Guild
function del_guild($guid, $realm)
{
	global $characters_db, $tab_del_guild;

	require_once 'data_lib.php';

	$sqlc = new SQL;
	$sqlc->connect($characters_db[$realm]['addr'], $characters_db[$realm]['user'], $characters_db[$realm]['pass'], $characters_db[$realm]['name']);

	//clean data inside characters.data field
	while ($guild_member = $sqlc->result($sqlc->query('SELECT guid FROM guild_member WHERE guildid = '.$guid.' '),0))
	{
		$data = $sqlc->result($sqlc->query('SELECT data FROM characters WHERE guid = '.$guild_member.' '),0);
		$data = explode(' ', $data);
		$data[CHAR_DATA_OFFSET_GUILD_ID] = 0;
		$data[CHAR_DATA_OFFSET_GUILD_RANK] = 0;
		$data = implode(' ', $data);
		$sqlc->query('UPDATE characters SET data = '.$data.' WHERE guid = '.$guild_member.' ');
	}

	$sqlc->query('DELETE FROM item_instance WHERE guid IN (SELECT item_guid FROM guild_bank_item WHERE guildid ='.$guid.')');

	foreach ($tab_del_guild as $value)
	$sqlr->query('DELETE FROM '.$value[0].' WHERE '.$value[1].' = '.$guid.' ');

	if ($sqlc->affected_rows())
		return true;
	else
		return false;

}


//##########################################################################################
//Delete Arena Team
function del_arenateam($guid, $realm)
{
	global	$characters_db,
			$tab_del_arena;

	$sqlc = new SQL;
	$sqlc->connect($characters_db[$realm]['addr'], $characters_db[$realm]['user'], $characters_db[$realm]['pass'], $characters_db[$realm]['name']);

	foreach ($tab_del_arena as $value)
	$sqlr->query('DELETE 
					FROM '.$value[0].' 
					WHERE '.$value[1].' = '.$guid.'');

	if ($sqlc->affected_rows())
		return true;
	else
		return false;

}


?>
