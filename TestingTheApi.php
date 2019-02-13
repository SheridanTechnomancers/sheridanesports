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
//EVAN'S CALLBACK CODE dWUxOnl1Yjh0eWJGYjF0RndrX3FwVEVwVHcuYXloaWlsT1NULXFXM1Z5WkNXaXd3dw%3D%3D
//  Initialize the library
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-68acb819-e350-466d-a62c-2712a63def54',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

$summonerName = "TAGUP Mythbran";

//  And now you are ready to rock!
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase 
//print_r($account->getData());  //  Or array of all the data

//PRINT THE SUMMONER NAME 
print_r($summonerName);
echo "</br>";
echo "</br>";
echo "</br>";
//PARAMETERS FOR MATCHLIST
//eventually we will be capturing ID 0 for custom games 
//Needs to be mapid = 1

//Matchlist prints out a 4d array 
$matchlist = $api->getMatchListByAccount($account->accountId); //WORKING
//print_r($matchlist);

//print_r($matchlist->lane);

$totalGames = $matchlist->totalGames;

//print "totalGames: $totalGames";

//$gameId = $matches->gameId;

foreach($matchlist->matches as $game)
	$gameIds[] = $game->gameId;

/*for($i=0; $i<$totalGames; $i++){
	print($gameIds[$i]);
	echo "</br>";
}
*/

$matchData = $api->getMatch($gameIds[0]);

//print_r($matchData);
$season = ($matchData->seasonId);
//print "Season: $season";

//participantIdentities I NEED THIS TO GET THE PARTICIPANTID. IT NEEDS TO BE LINKED TO THE SUMMONER NAME OR ACCOUNTID
//print_r($matchData);
foreach($matchData->participantIdentities as $participantIds){
		$participant[] = $participantIds->player;

}
$participantId = 0;
for($i=0; $i<10; $i++){
	if($participant[$i]->accountId == $account->accountId)
		$participantId = $i;
	//print_r($participant[$i]->summonerName);
	//echo "</br>";
}
//print"participantId: $participantId";

$playerMatchData = $matchData->participants[$participantId-1];

//print_r($playerMatchData);

//KDA
$kda = ($playerMatchData->stats->kills + $playerMatchData->stats->assists) / ($playerMatchData->stats->deaths);
print "KDA: $kda";
echo "</br>";
//CS
$cs = $playerMatchData->stats->totalMinionsKilled;
print "CS: $cs";

?>