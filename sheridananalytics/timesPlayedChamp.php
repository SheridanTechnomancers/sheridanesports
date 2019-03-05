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
$champsWithGameIds=[];
$goldEarned=[];
$avrgGold=0;
$avrgGameTime=0;
$avrgChampLvl=0;
$avrgDamageDealt=0;
$winRatePercentage=0;
//finds the champ ids for most recent 100 games. Currently only pulls the most recent games due to error if try with 100
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
	//stores champ id with its respective game id
	$champsWithGameIds[$champIdNumArr[$j]] = $gameIds[$j];

	//Avrg stats for player
	// avrgs
	//win rate in percentage

	//avg gold

	$goldEarned[$j]= $playerMatchData->stats->goldEarned; 						//GOLD EARNED
	//avg game time
	$avrgGameTime;
	//avg champ lvl
	$avrgChampLvl;
	//avg damage dealt to champs
	$avrgDamageDealt;

}

//finds the 5 champions played most
//initializations of variables
$gamesPlayed=1;
$champsWithCounts=[];
//iterates through champ id array and stores times champ is played
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	$found=1;
	//if champ id hasnt been checked yet, check it.
	if($champIdNumArr[$i]!=-1){
		$champIdNum=$champIdNumArr[$i];
		echo $champIdNum."<br";
		$goldEarned[$i]+=$goldEarned[$i];
		echo $goldEarned."<br>";
		//checks to see how many times it occurs
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed++;
				$champIdNumArr[$j]=-1;
			}
		}
		//Stores the times played (values) with thier respective champions (keys) in an associative array.
		$champsWithCounts[$champIdNumArr[$i]] = $gamesPlayed;
		$gamesPlayed=1;
	}
}
//sorts the associative array in descending order with respect to the values.
arsort($champsWithCounts);

//stores the top five champions
$topFiveChamps=array_slice($champsWithCounts, 0, 5, true);

//determines what champ were looking at
$iteration=0;
//finds and stores stats for each of the top 5
foreach ($topFiveChamps as $key => $value) {

	$matchData = $api->getMatch($champsWithGameIds[$key]);  //obtains match data using game id for champ.
	foreach($matchData->participantIdentities as $participantIds){
			$participant[] = $participantIds->player;

	}
	foreach($matchData->teams as $teams){
			$team[] = $teams;

	}
	for($i=0; $i<10; $i++){
		if($participant[$i]->accountId == $account->accountId)
			$participantId = $i;
	}
	$playerMatchData = $matchData->participants[$participantId];

$gameTimeM		= floor(($matchData->gameDuration)/60%60);//TOTAL MINUTES GAME TIME
  //getting kda
	$kills 			= $playerMatchData->stats->kills; 							//KILLS
	$assists 		= $playerMatchData->stats->assists; 						//ASSIST
	$deaths 		= $playerMatchData->stats->deaths; 							//DEATHS
	//CALCULATING KDA
	if($deaths == 0) 															//IF THERE'S NO DEATHS
		$kda 		= $kills + $assists;										//KDA WITHOUT DEATHS
	else 																		//IF DEATHS
		$kda 		= $kills + $assists / $deaths; 								//KDA WITH DEATHS

	$csDelta010		= $playerMatchData->timeline->creepsPerMinDeltas['0-10'];	//CS DELTA FOR MINUTES 0-10;
	$csDelta1020	= $playerMatchData->timeline->creepsPerMinDeltas['10-20'];	//CS DELTA FOR MINUTES 10-20
	$csDelta2030	= $playerMatchData->timeline->creepsPerMinDeltas['20-30'];	//CS DELTA FOR MINUTES 20-30
	$wardsPlaced	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED

	if($playerMatchData->stats->firstBloodKill == 1)							//IF GOT FIRST BLOOD
		$firstBlood = "Yes";
	else																		//NO FIRST BLOOD
		$firstBlood = "No";

		$tempArr['KDA']=$kda;
		if($gameTimeM>10){
		$tempArr['CS Delta for 0-10 minutes']=$csDelta010;
	  }
		if($gameTimeM>20){
		$tempArr['CS Delta for 10-20 minutes']=$csDelta1020;
	  }
		if($gameTimeM>30){
		$tempArr['CS Delta for 20-30 minutes']=$csDelta2030;
	  }
		$tempArr['Wards Placed']=$wardsPlaced;
		$tempArr['Got First Blood']=$firstBlood;



	if($iteration==0){
		echo "First Place: ".$key."<br>";
		$firstPlaceStats=$tempArr;

		foreach($firstPlaceStats as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>";
    }
	}
	else if($iteration==1){
		echo "Second Place: ".$key."<br>";
    $secondPlaceStats=$tempArr;

		foreach($secondPlaceStats as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>";
		}
	}
	else if($iteration==2){
		echo "Third Place: ".$key."<br>";
		$thirdPlaceStats=$tempArr;

		foreach($thirdPlaceStats as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>";
		}
	}
	else if($iteration==3){
		echo "Fourth Place: ".$key."<br>";
		$fourthPlaceStats=$tempArr;

		foreach($fourthPlaceStats as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>";
		}
	}
	else{
		echo "Fifth Place: ".$key."<br>";
		$fifthPlaceStats=$tempArr;
		foreach($fifthPlaceStats as $x => $x_value) {
    echo "Key=" . $x . ", Value=" . $x_value;
    echo "<br>";
		}
	}
	$iteration++;
}

/*
//for testing
foreach ($topFiveChamps as $key => $value) {
	echo "Key: ".$key." Value: ".$value."<br>";
}*/
?>
