<?php
//  Include all required files
require_once __DIR__  . "/dependencies/vendor/autoload.php";

use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\DataDragonAPI\DataDragonAPI;

DataDragonAPI::initByCdn();

//  Initialize the library
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-c65166bf-abd8-49d4-993c-8e2e4bfbcfe7',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::EUROPE_EAST,
]);

//  And now you are ready to rock!
$champion = $api->getLeagueMaster(RANKED_SOLO_5x5);

print_r($champion->getData());  //  Or array of all the data
?>