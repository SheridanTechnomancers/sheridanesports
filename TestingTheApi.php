<?php
//  Include all required files
require_once __DIR__  . "/dependencies/vendor/autoload.php";

use RiotAPI\Exceptions\GeneralException;
use RiotAPI\Objects\ChampionInfo;
use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\DataDragonAPI\DataDragonAPI;
use RiotAPI\DataDragonAPI\Definitions\Map;

DataDragonAPI::initByCdn();

//  Initialize the library
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-b25b32d4-2b4e-4907-87ad-b24303c097a5',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

//  And now you are ready to rock!
$account = $api->getSummonerByName("OSTRlCH");
print_r($account->getData());  //  Or array of all the data

//eventually we will be capturing ID 0 for custom games 
//Needs to be mapid = 1
$matchlist = $api->getMatchListByAccount('iYZAPyUiS5Cz53XHI1hiWJVv7akAkBFPeDgLgoAz76kOQQ', 420, null, null, null, null, null, null); 
//print_r($matchlist);

$matches = $api->getMatch('2972162655');
print_r($matches[seasonId]["11"]);


//[gameId] => 2974142280
//( string $encrypted_account_id, $queue = null, $season = null, $champion = null, int $beginTime = null, int $endTime = null, int $beginIndex = null, int $endIndex = null )
?>