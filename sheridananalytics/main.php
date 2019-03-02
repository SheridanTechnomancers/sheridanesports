<!--
	THIS WILL DO ALL THE API CALLS WE NEED. WILL NOT PRINT ANYTHING OUT 
	BASICALLY USE THIS TO GET THE AVERAGES, ALL DATA WE NEED 
	ONLY PASS THE AVERAGES OF EACH CHAMP. NOTHING GAME DATA WISE 
-->

<?php

//  Include all required files
require_once __DIR__  . "/../dependencies/vendor/autoload.php";

use RiotAPI\Exceptions\GeneralException;
use RiotAPI\Objects\ChampionInfo;
use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\DataDragonAPI\DataDragonAPI;
use RiotAPI\DataDragonAPI\Definitions\Map;

DataDragonAPI::initByCdn();
//EVAN'S CALLBACK CODE dWUxOnl1Yjh0eWJGYjF0RndrX3FwVEVwVHcuYXloaWlsT1NULXFXM1Z5WkNXaXd3dw%3D%3D
//  Initialize the library
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-d70baa70-ae11-47ec-b055-f8271e6b0776',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

//TAKING THINGS FROM INDEX.PHP 
$summonerName = $_POST['uname'];		//USERNAME 
$ROLE = $_POST['role'];

$account = $api->getSummonerByName($summonerName);
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420);

?>