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
	LeagueAPI::SET_KEY    => 'RGAPI-7cf16359-d697-4b3f-ab4f-678be3153176',
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

//initilizations
$champIdNumArr = array_fill(0, 100, -1); //champ id array
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

  //might need to use $gameChampId instead.
	//places each champion id in predefined array
	$champIdNumArr[$j]=$playerMatchData->championId;

  //stores stats per game for each champ
	$goldEarnedArr[$j]= $playerMatchData->stats->goldEarned; 					//GOLD EARNED
	$gameTimeArr[$j]= floor(($matchData->gameDuration));				//TOTAL MINUTES GAME TIME
	$winLossArr[$j]=$playerMatchData->stats->win; 										//Win/Loss , win =1 and loss=0
	$wardsPlacedArr[$j]	= $playerMatchData->stats->wardsPlaced;				//WARDS PLACED

	//KDA determination
	//KDA determination
	$killArr[$j] 		= $playerMatchData->stats->kills; 								//KILLS
	$assistArr[$j] 		= $playerMatchData->stats->assists; 							//ASSIST
	$deathArr[$j] 		= $playerMatchData->stats->deaths; 								//DEATHS

	$champLvlArr[$j]	= $playerMatchData->stats->champLevel;							//CHAMPION LEVEL
	$firstBloodArr[$j]=$playerMatchData->stats->firstBloodKill; 					//Got firstblood, yes=1 and no=0
	$ddtcArr[$j]= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS

	//CS DELTAs, only added if game time is high enough.
	if(($gameTimeArr[$j]/60%60)>=10){
		$csDelta010Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['0-10'];	//CS DELTA FOR MINUTES 0-10;
	}
	if(($gameTimeArr[$j]/60%60)>=20){
		$csDelta1020Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['10-20'];	//CS DELTA FOR MINUTES 10-20
	}
	if(($gameTimeArr[$j]/60%60)>=30){
	$csDelta2030Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['20-30'];	//CS DELTA FOR MINUTES 20-30
  }
}

//calculates the times played for each champ as well as all their stats averaged over the games played with them.
//initializations of variables
$gamesPlayed=1; 			  //keeps track of games played
$champStats;			  	  //stores the stats for each champ in an array,initilized here so we can call it later.
$indexCounter;			   //stores the index of the second array (j loop),since array is dynamic due to cs deltas.
$indexCounterLoop=0; 	 //keeps track of index for $champStats (i loop), since array is dynamic due to duplicate champs.
//keeps track of which deltas to add to $champStats.
$checked010=false;
$checked1020=false;
$checked2030=false;
//iterates through champ id array and stores the games played with them in $champsWithCounts, also calculates stats and stores them in $champStats.
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	//if champ id hasnt been checked yet, start calculation.
	if($champIdNumArr[$i]!=-1){
		$champIdNum=$champIdNumArr[$i];
		$avrgGold=$goldEarnedArr[$i];
		$avrgGameTime=$gameTimeArr[$i];
		$avrgChampLvl=$champLvlArr[$i];
		$winRate=$winLossArr[$i];
		$avrgWardsPlaced=$wardsPlacedArr[$i];
		$avrgKills			= $killArr[$i];
		$avrgAssists		= $assistArr[$i];
		$avrgDeaths			= $deathArr[$i];
		$avrgFirstblood=$firstBloodArr[$i];
		$avrgddtc=$ddtcArr[$i];
		//each CS delta checks if the element at that point exists, possibility it doesnt due to different game time.
		if(isset($csDelta010Arr[$j])){
			$avrgCSDelta010=$csDelta010Arr[$j];
			$checked010=true;
		}
		if(isset($csDelta1020Arr[$j])){
			$avrgCSDelta1020=$csDelta1020Arr[$j];
			$checked1020=true;
		}
		if(isset($csDelta2030Arr[$j])){
			$avrgCSDelta2030=$csDelta2030Arr[$j];
			$checked2030=true;
		}

		//checks to see how many times the champions been played.
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed++;
				//adds the stats from those games to the original value.
				$avrgGold+=$goldEarnedArr[$j];
				$avrgGameTime+=$gameTimeArr[$j];
				$avrgChampLvl+=$champLvlArr[$j];
				$winRate+=$winLossArr[$j];
				$avrgWardsPlaced+=$wardsPlacedArr[$j];
				$avrgKills		+= $killArr[$j];
				$avrgAssists	+= $assistArr[$j];
				$avrgDeaths		+= $deathArr[$j];
				$avrgFirstblood+=$firstBloodArr[$j];
				$avrgddtc+=$ddtcArr[$j];
				//again need to check that the element exists at that point.
				if($checked010){
					$avrgCSDelta010+=$csDelta010Arr[$j];
				}
				if($checked1020){
					$avrgCSDelta1020+=$csDelta1020Arr[$j];
				}
				if($checked2030){
					$avrgCSDelta2030+=$csDelta2030Arr[$j];
				}
				$champIdNumArr[$j]=-1; //change that id to -1 so we dont check it again.
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
		$champStats[$indexCounterLoop][11]	= array('kda'			=> ($avrgKills+$avrgAssists)/$avrgDeaths);
		$champStats[$indexCounterLoop][12]	= array('firstBlood'	=> ($avrgFirstblood/$gamesPlayed)*100 );
		$champStats[$indexCounterLoop][13]	= array('damageDealt'	=> $avrgddtc/$gamesPlayed);
		$champStats[$indexCounterLoop][14]	= array('gamesPlayed'	=> $gamesPlayed);

		if($checked010){
			$champStats[$indexCounterLoop][14+$indexCounter]=array('Average CS delta for 0-10 (m)'=>$avrgCSDelta010/$gamesPlayed );
			$indexCounter++;
	  }
		if($checked1020){
			$champStats[$indexCounterLoop][14+$indexCounter]=array('Average CS delta for 10-20 (m)'=>$avrgCSDelta1020/$gamesPlayed);
			$indexCounter++;
		}
		if($checked2030){
			$champStats[$indexCounterLoop][14+$indexCounter]=array('Average CS delta for 20-30 (m)'=>$avrgCSDelta2030/$gamesPlayed);
	  }

		$gamesPlayed=1; //reset games played before iterates again
		$indexCounterLoop++; //increase loopcounter before iteration
	}
}
/*testing statement
foreach ($champsWithCounts as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}*/
//sorts the associative array $champsWithCounts in descending order with respect to the values.
arsort($champsWithCounts);

//stores the top five champions from $champsWithCounts
$topFiveChamps=array_slice($champsWithCounts, 0, 5, true);
/*
foreach ($topFiveChamps as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}*/

//stores top five champs STATS in thier respective arrays.
//Initialization
$champ=1; 								//keeps track of what placement the champ were looking at is on the list of top five.
$firstChampsStats=[];			//stores all information for the champ in first place.
$secondChampsStats=[];		//stores all information for the champ in second place.
$thirdChampsStats=[];			//stores all information for the champ in third place.
$fourthChampsStats=[];		//stores all information for the champ in fourth place.
$fifthChampsStats=[];			//stores all information for the champ in fifth place.
//iterates through $champStats, and for each champ that matches the top five we take the values stored and move them to its corresponding placement.

foreach ($topFiveChamps as $topFive => $value) {
	for ($i=0; $i <sizeof($champStats); $i++){
		foreach ($champStats[$i][0] as $array=> $champion) {
			if($topFive==$champion){
				//adds the placement of the champion to the array
				if($champ==1){
						$firstChampsStats['Placement']='First';
					}
					else if($champ==2){
						$secondChampsStats['Placement']='Second';
					}
					else if($champ==3){
						$thirdChampsStats['Placement']='Third';
					}
					else if($champ==4){
						$fourthChampsStats['Placement']='Fourth';
					}
					else{
						$fifthChampsStats['Placement']='Fifth';
					}
				//adds the rest of their stats to thier respective array
				for ($j=0; $j <count($champStats[$i])  ; $j++) {
					foreach ($champStats[$i][$j] as $statistic => $number) {
						if($champ==1){
								$firstChampsStats[$statistic]=$number;
						}
						else if($champ==2){
							$secondChampsStats[$statistic]=$number;
						}
						else if($champ==3){
							$thirdChampsStats[$statistic]=$number;
						}
						else if($champ==4){
							$fourthChampsStats[$statistic]=$number;
						}
						else{
							$fifthChampsStats[$statistic]=$number;
						}
					}
				}
			$champ++;
  		}
		}
	}
}

//PRINT STATEMENTS FOR TESTING
foreach ($firstChampsStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
foreach ($secondChampsStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
foreach ($thirdChampsStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
foreach ($fourthChampsStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
echo "<br>";
foreach ($fifthChampsStats as $key => $value) {
	echo $key.": ".$value;
	echo "<br>";
}
?>
