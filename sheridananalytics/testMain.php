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
//  Initialize the library //    RGAPI-b1c70cf8-1118-4467-946d-1ff43b3dd95d  RGAPI-1304059b-a95f-4262-b3db-252b8e2ba157
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-1304059b-a95f-4262-b3db-252b8e2ba157',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

//TAKING THINGS FROM INDEX.PHP
//$summonerName = $_POST['uname'];		//USERNAME
//$ROLE = $_POST['role'];

$account = $api->getSummonerByName('ostrlch');
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420);
//champ id array
$champIdNumArr = array_fill(0, 100, -1);

 //keeps track of which deltas are relevant
$checked010=false;
$checked1020=false;
$checked2030=false;

foreach($matchlistSolo->matches as $game){
	$gameIds[] = $game->gameId;
	//$gameChampId[] = $game->champion;
}

//$matchData = $api->getMatch($gameIds[]);
//print_r($matchData);

//finds the champ ids for most recent 100 games. Currently only pulls te most recent games due to error if try witth 100
for ($j=0; $j<50; $j++){

	$matchData = $api->getMatch($gameIds[$j]);

	for($i=0; $i<10; $i++){
		if($matchData->participantIdentities[$i]->player->accountId == $account->accountId){
			$participantId = $i;
			break;
		}
	}

	//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
	$playerMatchData	= $matchData->participants[$participantId];

	//places each champion id in predefined array
	$champIdNumArr[$j]	= $playerMatchData->championId;

	$gameTimeM			= floor(($matchData->gameDuration)/60%60);					//TOTAL MINUTES GAME TIME
	
	//stores stats per game for each champ
	$goldEarnedArr[$j]	= $playerMatchData->stats->goldEarned; 						//GOLD EARNED
	$gameTimeMArr[$j]	= floor(($matchData->gameDuration)/60%60);					//TOTAL MINUTES GAME TIME
	$winLossArr[$j]		= $playerMatchData->stats->win; 							//Win/Loss , win =1 and loss=0
	$wardsPlacedArr[$j]	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED	
	//KDA determination
	$kills				= $playerMatchData->stats->kills; 							//KILLS
	$assists 			= $playerMatchData->stats->assists; 						//ASSIST
	$deaths 			= $playerMatchData->stats->deaths; 							//DEATHS
	//CALCULATING KDA
	if($deaths == 0) 															//IF THERE'S NO DEATHS
		$kda 			= $kills + $assists;										//KDA WITHOUT DEATHS
	else 																		//IF DEATHS
		$kda 			= $kills + $assists / $deaths; 								//KDA WITH DEATHS
	//final KDA
	$kdaArr[$j]			= $kda;

	$champLvlArr[$j]	= $playerMatchData->stats->champLevel;						//CHAMPION LEVEL
	$firstBloodArr[$j]	= $playerMatchData->stats->firstBloodKill; 					//Got firstblood, yes=1 and no=0
	$ddtcArr[$j]		= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS

	
}//print_r($playerMatchData);

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
		$champIdNum 		= $champIdNumArr[$i];
		$avrgGold			= $goldEarnedArr[$i];
		$avrgGameTime		= $gameTimeMArr[$i];
		$avrgChampLvl		= $champLvlArr[$i];
		$winRate			= $winLossArr[$i];
		$avrgWardsPlaced	= $wardsPlacedArr[$i];
		$avrgKDA			= $kdaArr[$i];
		$avrgFirstblood		= $firstBloodArr[$i];
		$avrgddtc			= $ddtcArr[$i];
		if($checked010){
			$avrgCSDelta1020= $csDelta010Arr[$i];
		}
		if($checked1020){
			$avrgCSDelta1020= $csDelta1020Arr[$i];
		}
		if($checked2030){
			$avrgCSDelta2030= $csDelta2030Arr[$i];
		}

		//checks to see how many times the champ id occurs
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed++; //NEEDS TO STORE EXACT AMOUNTS FOR EACH CHAMP ID
				$avrgGold		+= $goldEarnedArr[$j];
				$avrgGameTime	+= $gameTimeMArr[$j];
				$avrgChampLvl	+= $champLvlArr[$j];
				$winRate		+= $winLossArr[$j];
				$avrgWardsPlaced+= $wardsPlacedArr[$j];
				$avrgKDA		+= $kdaArr[$j];
				$avrgFirstblood	+= $firstBloodArr[$j];
				$avrgddtc		+= $ddtcArr[$j];

				$champIdNumArr[$j]=-1;
				
			}
		}
		//Stores the times played (value) with thier respective champion id (key) in an associative array.
		$champsWithCounts[$champIdNumArr[$i]] 	= $gamesPlayed;
		$champStats[$i][0]						= $champIdNum;
		$champStats[$i][1]						= array('Statistic' => "Average Gold",'Value' => $avrgGold/$gamesPlayed);
		$champStats[$i][2]						= array('Statistic' =>"Average Game time (m)" , 'Value'=>$avrgGameTime/$gamesPlayed);
		$champStats[$i][3]						= array('Statistic' =>"Average Champion lvl" ,'Value'=>$avrgChampLvl/$gamesPlayed );
		$champStats[$i][4]						= array('Statistic' => "Win/Loss Rate (%)", 'Value'=>($winRate/$gamesPlayed)*100);
		$champStats[$i][5]						= array('Statistic' => "Average amount of Wards Placed",'Value'=>$avrgWardsPlaced/$gamesPlayed );
		$champStats[$i][6]						= array('Statistic' => "Average KDA",'Value'=>$avrgKDA/$gamesPlayed );
		$champStats[$i][7]						= array('Statistic' => "Average First Blood (%)",'Value'=>($avrgFirstblood/$gamesPlayed)*100 );
		$champStats[$i][8]						= array('Statistic' => "Average Damage Dealt to Champs", 'Value'=>$avrgddtc/$gamesPlayed);
		
		$gamesPlayed=1;
	}
}
//Sorts the associative array in descending order, according to its values.
arsort($champsWithCounts);

//stores the top five champions
$topFiveChamps=array_slice($champsWithCounts, 0, 5, true);

for($i = 0; $i < 5; $i++){
	//print_r($topFiveChamps[0]);
	for($j = 0; $j < 8; $j++){
		$championName = $api->getStaticChampion($champStats[$i][0], true);
		print_r($championName->name);
		print_r($champStats[$i][$j]);
		echo "<br>";
	}




}

/*foreach($topFiveChamps as $champ=>$gamesPlayed){
	//print_r("$topFiveChamps[$i]<br>");
	$champion = $api->getStaticChampion($champ, true);
	print_r($champion->name);
	echo "Games Played: ". $gamesPlayed;
	print("<br>");

}*/

?>
