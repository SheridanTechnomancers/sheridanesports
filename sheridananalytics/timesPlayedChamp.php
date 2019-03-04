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
	LeagueAPI::SET_KEY    => 'RGAPI-ee10da3e-3e8c-4d6c-87ea-1e48bafa9d77',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

$summonerName = "scottlu"; //HARDCODED SUMMONER NAME
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase

//needed initializations
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420); //WORKING
foreach($matchlistSolo->matches as $game){
	if($game->lane == 'MID'){
		$gameIds[] = $game->gameId;
		$gameChampId[] = $game->champion;
	}
}

//champ id array
$champIdNumArr = array_fill(0, 100, -1);
//top five used champ ids
$topFiveChamps=array_fill(0,5,"null");
//the amount of times the champs were used
$timesChampsPlayed=array_fill(0,5,0);

//finds the champ ids for most recent 100 games. Currently only pulls te most recent games due to error if try witth 100
for ($j=0; $j<51; $j++){
	$matchData = $api->getMatch($gameIds[$j]);
	foreach($matchData->participantIdentities as $participantIds){
		$participant[] = $participantIds->player;
	}

	for($i=0; $i<10; $i++){
		if($participant[$i]->accountId == $account->accountId)
		$participantId = $i;
	}

	//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
	$playerMatchData = $matchData->participants[$participantId];

	//places each champion id in predefined array
	$champIdNumArr[$j]=$playerMatchData->championId;
}

//finds the 5 champions played most
//initializations of variables
$count=1;
$gamesPlayed=0;
//iterates through champ id array and stores times champ is played
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	$found=1;
	//if champ id hasnt been checked yet, check it.
	if($champIdNumArr[$i]!=-1){
		$champIdNum=$champIdNumArr[$i];

		//checks to see how many times it occurs
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed=$count++;
				$champIdNumArr[$j]=-1;
			}
		}

		//finds top 5 champs played.
			for ($k=0;$k<sizeof($topFiveChamps);$k++){
				if($gamesPlayed>$timesChampsPlayed[$k] && $found==1){
					$topFiveChamps[$k]=$champIdNum;
					$timesChampsPlayed[$k]=$gamesPlayed;
					$found=0;
				}
		}
		$count=1;
	}
}
?>
