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
	LeagueAPI::SET_KEY    => 'RGAPI-ee10da3e-3e8c-4d6c-87ea-1e48bafa9d77',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

//TAKING THINGS FROM INDEX.PHP
//$summonerName = $_POST['uname'];		//USERNAME
//$ROLE = $_POST['role'];

$account = $api->getSummonerByName('McGaw');
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420);

foreach($matchlistSolo->matches as $game){
	$gameIds[] = $game->gameId;
	$gameChampId[] = $game->champion;
}

//champ id array
$champIdNumArr = array_fill(0, 100, -1);
//finds the champ ids for most recent 100 games. Currently only pulls te most recent games due to error if try witth 100
for ($j=0; $j<50; $j++){
	$matchData = $api->getMatch($gameIds[$j]);
	foreach($matchData->participantIdentities as $participantIds){
		$participant[] = $participantIds->player;
	}

	for($i=0; $i<10; $i++){
		if($participant[$i]->accountId == $account->accountId)
		$participantId = $i;
	}

	//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
	//THIS SECTION WORKS IN PULLING THE CORRECT GAME STATS INTO $playerMatchData. -Matt
	$playerMatchData = $matchData->participants[$participantId];

	//places each champion id in predefined array
	//THIS IS PULLING THE CORRECT CHAMPION IDS. - Matt
	$champIdNumArr[$j]=$playerMatchData->championId;
}

//finds the 5 champions played most
//initializations of variables
$gamesPlayed=1;//SHOULD STORE HOW MANY OF EACH CHAMP GAMES PLAYED
$champsWithCounts=[]; //Should store each champ with the amount of times theyve been played in an associative array.
//iterates through champ id array and stores times champ is played
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	$found=1;
	//if champ id hasnt been checked yet, check it.
	/*
	*	Checks the array for the -1 flag. If it's not set champ hasn't been counted
	*	Create a new Var to store unchecked champ ID
	*	iterates through the rest of the array and finds other champ ids
	*	Stores them in a var. NEED TO KEEP ALL VALUES SEPERATE and set -1 flag for counted
	*
	*/
	if($champIdNumArr[$i]!=-1){
		$champIdNum=$champIdNumArr[$i];

		//checks to see how many times the champ id occurs
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed++; //NEEDS TO STORE EXACT AMOUNTS FOR EACH CHAMP ID
				$champIdNumArr[$j]=-1;
			}
		}
		//Stores the times played (value) with thier respective champion id (key) in an associative array.
		$champsWithCounts[$champIdNumArr[$i]] = $gamesPlayed;
		$gamesPlayed=1;
	}
}
//Sorts the associative array in descending order, according to its values.
arsort($champsWithCounts);

//stores the top five champions
$topFiveChamps=array_slice($champsWithCounts, 0, 5, true);

for($i=0; $i<5; $i++){
	//print_r("$topFiveChamps[$i]<br>");
	$champion = $api->getStaticChampion($topFiveChamps[$i], true);
	print_r($champion->name);
	print("<br>");

}

?>
