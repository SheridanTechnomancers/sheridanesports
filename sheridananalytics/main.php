<!--
	THIS WILL DO ALL THE API CALLS WE NEED. WILL NOT PRINT ANYTHING OUT
	BASICALLY USE THIS TO GET The AVERAGES, ALL DATA WE NEED
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
	LeagueAPI::SET_KEY    => 'RGAPI-b668f3f5-c118-401f-aabd-5c3143e86008',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

//TAKING THINGS FROM INDEX.PHP
$summonerName 		= 'Misleading';		//USERNAME
//print_r($_POST['uname']);
//$ROLE = $_POST['role'];
$account 			= $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase
$matchlistSolo	 	= $api->getMatchListByAccount($account->accountId, 420);
$champIdNumArr	 	= array_fill(0, 100, -1);
//initializations of variables
$gamesPlayed		= 1;	//keeps track of games played
$champStats;				//stores the stats for each champ in an array,initilized here so we can call it later.
$indexCounter;			   	//stores the index of the second array (j loop),since array is dynamic due to cs deltas.
$indexCounterLoop	= 0; 	//keeps track of index for $champStats (i loop), since array is dynamic due to duplicate champs.
//keeps track of which deltas to add to $champStats.
$checked010 		= false;
$checked1020 		= false;
$checked2030 		= false;

//stores top five champs STATS in thier respective arrays.
//Initialization
$champ 				= 1; 		//keeps track of what placement the champ were looking at is on the list of top five.
$firstChampStats	= [];		//stores all information for the champ in first place.
$secondChampStats	= [];		//stores all information for the champ in second place.
$thirdChampStats	= [];		//stores all information for the champ in third place.
$fourthChampStats	= [];		//stores all information for the champ in fourth place.
$fifthChampStats	= [];		//stores all information for the champ in fifth place.
//iterates through $champStats, and for each champ that matches the top five we take the values stored and move them to its corresponding placement.


//needed initializations
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420); //WORKING
foreach($matchlistSolo->matches as $game){
		$gameIds[] = $game->gameId;
		//$gameChampId[] = $game->champion;
}

//finds the champ ids for most recent 100 games. Currently only pulls the most recent games due to error if try with 100
for ($j=0; $j<51; $j++){
	$matchData = $api->getMatch($gameIds[$j]);
	$counter=0; //counter to prevent participant array from appending instead of overwriting
	foreach($matchData->participantIdentities as $participantIds){
		$participant[$counter] = $participantIds->player;
		$counter++;
	}

	for($i=0; $i<10; $i++){
		if($participant[$i]->accountId == $account->accountId)
			$participantId = $i;
	}
	//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
	$playerMatchData = $matchData->participants[$participantId];

  //might need to use $gameChampId instead.
	//places each champion id in predefined array
	$champIdNumArr[$j] 	= $playerMatchData->championId;

  //stores stats per game for each champ
	$goldEarnedArr[$j]	= $playerMatchData->stats->goldEarned;						//GOLD EARNED
	$gameTimeArr[$j]	= $matchData->gameDuration;									//TOTAL MINUTES GAME TIME
	$winLossArr[$j]		= $playerMatchData->stats->win; 							//Win/Loss , win =1 and loss=0
	$wardsPlacedArr[$j]	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED

	//KDA determination
	//KDA determination
	$killArr[$j] 		= $playerMatchData->stats->kills; 							//KILLS
	$assistArr[$j] 		= $playerMatchData->stats->assists; 						//ASSIST
	$deathArr[$j] 		= $playerMatchData->stats->deaths; 							//DEATHS

	$champLvlArr[$j]	= $playerMatchData->stats->champLevel;						//CHAMPION LEVEL
	$firstBloodArr[$j]	= $playerMatchData->stats->firstBloodKill; 					//Got firstblood, yes=1 and no=0
	$ddtcArr[$j]		= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS

	//CS DELTAs, only added if game time is high enough.
	if(($gameTimeArr[$j]/60%60) >= 10){ // NEED TO BE DONE DIFFERENTLY
		$csDelta010Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['0-10'];	//CS DELTA FOR MINUTES 0-10;
	}
	if(($gameTimeArr[$j]/60%60) >= 20){
		$csDelta1020Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['10-20'];	//CS DELTA FOR MINUTES 10-20
	}
	if(($gameTimeArr[$j]/60%60) >= 30){
	$csDelta2030Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['20-30'];		//CS DELTA FOR MINUTES 20-30
  }
}


//iterates through champ id array and stores the games played with them in $champsWithCounts, also calculates stats and stores them in $champStats.
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	//if champ id hasnt been checked yet, start calculation.
	if($champIdNumArr[$i]!=-1){
		$champIdNum 		= $champIdNumArr[$i];
		$avrgGold			= $goldEarnedArr[$i];
		$avrgGameTime		= $gameTimeArr[$i];
		$avrgChampLvl		= $champLvlArr[$i];
		$winRate			= $winLossArr[$i];
		$avrgWardsPlaced	= $wardsPlacedArr[$i];
		$avrgKills			= $killArr[$i];
		$avrgAssists		= $assistArr[$i];
		$avrgDeaths			= $deathArr[$i];
		$avrgFirstblood		= $firstBloodArr[$i];
		$avrgddtc			= $ddtcArr[$i];
		//each CS delta checks if the element at that point exists, possibility it doesnt due to different game time.
		if(isset($csDelta010Arr[$j])){
			$avrgCSDelta010 = $csDelta010Arr[$j];
			$checked010 = true;
		}
		if(isset($csDelta1020Arr[$j])){
			$avrgCSDelta1020 = $csDelta1020Arr[$j];
			$checked1020 = true;
		}
		if(isset($csDelta2030Arr[$j])){
			$avrgCSDelta2030 = $csDelta2030Arr[$j];
			$checked2030 = true;
		}

		//checks to see how many times the champions been played.
		for($j=$i+1;$j<sizeof($champIdNumArr)-$i;$j++){
			if($champIdNum == $champIdNumArr[$j]){
				$gamesPlayed++;
				//adds the stats from those games to the original value.
				$avrgGold		+= $goldEarnedArr[$j];
				$avrgGameTime	+= $gameTimeArr[$j];
				$avrgChampLvl	+= $champLvlArr[$j];
				$winRate		+= $winLossArr[$j];
				$avrgWardsPlaced+= $wardsPlacedArr[$j];
				$avrgKills		+= $killArr[$j];
				$avrgAssists	+= $assistArr[$j];
				$avrgDeaths		+= $deathArr[$j];
				$avrgFirstblood	+= $firstBloodArr[$j];
				$avrgddtc		+= $ddtcArr[$j];
				//again need to check that the element exists at that point.
				if($checked010){
					$avrgCSDelta010 	+= $csDelta010Arr[$j-1];
				}
				if($checked1020){
					$avrgCSDelta1020	+= $csDelta1020Arr[$j-1];
				}
				if($checked2030){
					$avrgCSDelta2030	+= $csDelta2030Arr[$j-1];
				}
				$champIdNumArr[$j] =- 1; //change that id to -1 so we dont check it again.
			}
		}

		//Stores the times played (values) with thier respective champions (keys) in an associative array.
		$champsWithCounts[$champIdNum] = $gamesPlayed;

		$indexCounter=1; //needed for reset to 1 ech loop since each array is dynamic.
		//store stats for each champ in a multidimensional array.
		$champStats[$indexCounterLoop][0]	= array('champId'		=> $champIdNum);
		$champStats[$indexCounterLoop][1]	= array('gold'			=> $avrgGold/$gamesPlayed);
		$champStats[$indexCounterLoop][2]	= array('gameTimeH'		=> ($avrgGameTime/$gamesPlayed)/3600%24);
		$champStats[$indexCounterLoop][3]	= array('gameTimeM'		=> ($avrgGameTime/$gamesPlayed)/60%60);
		$champStats[$indexCounterLoop][4]	= array('gameTimeS'		=> ($avrgGameTime/$gamesPlayed)%60);
		$champStats[$indexCounterLoop][5]	= array('level'			=> $avrgChampLvl/$gamesPlayed);
		$champStats[$indexCounterLoop][6]	= array('winRatio'		=> ($winRate/$gamesPlayed)*100);
		$champStats[$indexCounterLoop][7]	= array('wardsPlaced'	=> $avrgWardsPlaced/$gamesPlayed);
		$champStats[$indexCounterLoop][8]	= array('kills'			=> $avrgKills/$gamesPlayed);
		$champStats[$indexCounterLoop][9]	= array('deaths'		=> $avrgDeaths/$gamesPlayed);
		$champStats[$indexCounterLoop][10]	= array('assists'		=> $avrgAssists/$gamesPlayed);
		$champStats[$indexCounterLoop][12]	= array('firstBlood'	=> ($avrgFirstblood/$gamesPlayed)*100 );
		$champStats[$indexCounterLoop][13]	= array('damageDealt'	=> $avrgddtc/$gamesPlayed);
		$champStats[$indexCounterLoop][14]	= array('gamesPlayed'	=> $gamesPlayed);

		if($avrgDeaths == 0){
			$champStats[$indexCounterLoop][11]	= array('kda'		=> $avrgKills+$avrgAssists);
		}
		elseif($avrgDeaths!=0){
			$champStats[$indexCounterLoop][11]	= array('kda'		=> ($avrgKills+$avrgAssists)/$avrgDeaths);
		}

		if($checked010){
			$champStats[$indexCounterLoop][14+$indexCounter] = array('csDelta010'	=>$avrgCSDelta010/$gamesPlayed );
			$indexCounter++;
	  }
		if($checked1020){
			$champStats[$indexCounterLoop][14+$indexCounter] = array('csDelta1020'	=>$avrgCSDelta1020/$gamesPlayed);
			$indexCounter++;
		}
		if($checked2030){
			$champStats[$indexCounterLoop][14+$indexCounter] = array('csDelta2030'	=>$avrgCSDelta2030/$gamesPlayed);
	  }

		$gamesPlayed = 1;	 //reset games played before iterates again
		$indexCounterLoop++; //increase loopcounter before iteration
	}
}

foreach ($champsWithCounts as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
//sorts the associative array $champsWithCounts in descending order with respect to the values.
arsort($champsWithCounts);

//stores the top five champions from $champsWithCounts
$topFiveChamps = array_slice($champsWithCounts, 0, 5, true);
/*
foreach ($topFiveChamps as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}*/
/*
//stores top five champs STATS in thier respective arrays.
//Initialization
$champ=1; 								//keeps track of what placement the champ were looking at is on the list of top five.
$firstChampStats=[];			//stores all information for the champ in first place.
$secondChampStats=[];		//stores all information for the champ in second place.
$thirdChampStats=[];			//stores all information for the champ in third place.
$fourthChampStats=[];		//stores all information for the champ in fourth place.
$fifthChampStats=[];			//stores all information for the champ in fifth place.
//iterates through $champStats, and for each champ that matches the top five we take the values stored and move them to its corresponding placement.
*/
foreach ($topFiveChamps as $topFive => $value) {
	for ($i=0; $i <sizeof($champStats); $i++){
		foreach ($champStats[$i][0] as $array => $champion) {
			if($topFive == $champion){
				//adds the placement of the champion to the array
				if($champ == 1){
					$firstChampStats['Placement'] 	= 'First';
				}
				else if($champ == 2){
					$secondChampStats['Placement'] = 'Second';
				}
				else if($champ == 3){
					$thirdChampStats['Placement']	= 'Third';
				}
				else if($champ == 4){
					$fourthChampStats['Placement'] = 'Fourth';
				}
				else{
					$fifthChampStats['Placement'] 	= 'Fifth';
				}
				//adds the rest of their stats to thier respective array
				for ($j=0; $j <count($champStats[$i])  ; $j++) {
					foreach ($champStats[$i][$j] as $statistic => $number) {
						if($champ == 1){
							$firstChampStats[$statistic]	= $number;
						}
						else if($champ == 2){
							$secondChampStats[$statistic]	= $number;
						}
						else if($champ == 3){
							$thirdChampStats[$statistic]	= $number;
						}
						else if($champ == 4){
							$fourthChampStats[$statistic]	= $number;
						}
						else{
							$fifthChampStats[$statistic]	= $number;
						}
					}
				}
			$champ++;
  		}
		}
	}
}

//PRINT STATEMENTS FOR TESTING
$champ1 = $api->getStaticChampion($firstChampStats['champId'], true);
print($champ1->name);
foreach ($firstChampStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
$champ2 = $api->getStaticChampion($secondChampStats['champId'], true);
print($champ2->name);
foreach ($secondChampStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
$champ3 = $api->getStaticChampion($thirdChampStats['champId'], true);
print($champ3->name);
foreach ($thirdChampStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
$champ4 = $api->getStaticChampion($fourthChampStats['champId'], true);
print($champ4->name);
foreach ($fourthChampStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
$champ5 = $api->getStaticChampion($fifthChampStats['champId'], true);
print($champ5->name);
foreach ($fifthChampStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
?>
