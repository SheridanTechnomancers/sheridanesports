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
	LeagueAPI::SET_KEY    => 'RGAPI-1304059b-a95f-4262-b3db-252b8e2ba157',
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

 //keeps track of which deltas are relevant
$checked010=false;
$checked1020=false;
$checked2030=false;
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

  //check if this is same data as $gameChampId, if it is no need for this.
	//places each champion id in predefined array
	$champIdNumArr[$j]=$playerMatchData->championId;

	$gameTimeM		= floor(($matchData->gameDuration)/60%60);					//TOTAL MINUTES GAME TIME

  //stores stats per game for each champ
	$goldEarnedArr[$j]= $playerMatchData->stats->goldEarned; 						//GOLD EARNED
	$gameTimeMArr[$j]= floor(($matchData->gameDuration)/60%60);					//TOTAL MINUTES GAME TIME
	$winLossArr[$j]=$playerMatchData->stats->win; //Win/Loss , win =1 and loss=0
	$wardsPlacedArr[$j]	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED
	//KDA determination
	$kills 			= $playerMatchData->stats->kills; 							//KILLS
	$assists 		= $playerMatchData->stats->assists; 						//ASSIST
	$deaths 		= $playerMatchData->stats->deaths; 							//DEATHS
	//CALCULATING KDA
	if($deaths == 0) 															//IF THERE'S NO DEATHS
		$kda 		= $kills + $assists;										//KDA WITHOUT DEATHS
	else 																		//IF DEATHS
		$kda 		= $kills + $assists / $deaths; 								//KDA WITH DEATHS
	//final KDA
	$kdaArr[$j]=$kda;

	$champLvlArr[$j]	= $playerMatchData->stats->champLevel;						//CHAMPION LEVEL
	$firstBloodArr[$j]=$playerMatchData->stats->firstBloodKill; //Got firstblood, yes=1 and no=0
	$ddtcArr[$j]= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS
	/*not working yet
	//CS DELTAs
	if($gameTimeM>=10 && isset($csDelta010Arr[$j])){
		$csDelta010Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['0-10'];	//CS DELTA FOR MINUTES 0-10;
		$checked010=true;
	}
	if($gameTimeM>=20 && isset($csDelta1020Arr[$j])){
		$csDelta1020Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['10-20'];	//CS DELTA FOR MINUTES 10-20
		$checked1020=true;
	}
	if($gameTimeM>=30 && isset($csDelta2030Arr[$j])){
	$csDelta2030Arr[$j]	= $playerMatchData->timeline->creepsPerMinDeltas['20-30'];	//CS DELTA FOR MINUTES 20-30
	$checked2030=true;
}*/
}

//finds the 5 champions played most
//initializations of variables
$gamesPlayed=1;
$champStats;
//iterates through champ id array and stores times champ is played
for ($i=0;$i<sizeof($champIdNumArr);$i++){
	$found=1;
	//if champ id hasnt been checked yet, check it.
	if($champIdNumArr[$i]!=-1){
		$champIdNum=$champIdNumArr[$i];
		$avrgGold=$goldEarnedArr[$i];
		$avrgGameTime=$gameTimeMArr[$i];
		$avrgChampLvl=$champLvlArr[$i];
		$winRate=$winLossArr[$i];
		$avrgWardsPlaced=$wardsPlacedArr[$i];
		$avrgKDA=$kdaArr[$i];
		$avrgFirstblood=$firstBloodArr[$i];
		$avrgddtc=$ddtcArr[$i];
		if($checked010){
			$avrgCSDelta010=$csDelta010Arr[$j];
		}
		if($checked1020){
			$avrgCSDelta1020=$csDelta1020Arr[$j];
		}
		if($checked2030){
			$avrgCSDelta2030=$csDelta2030Arr[$j];
		}

		//checks to see how many times it occurs
		for($j=$i+1;$j<sizeof($champIdNumArr)-$j;$j++){
			if($champIdNum== $champIdNumArr[$j]){
				$gamesPlayed++;
				//average out the stats over the games played.
				$avrgGold+=$goldEarnedArr[$j];
				$avrgGameTime+=$gameTimeMArr[$j];
				$avrgChampLvl+=$champLvlArr[$j];
				$winRate+=$winLossArr[$j];
				$avrgWardsPlaced+=$wardsPlacedArr[$j];
				$avrgKDA+=$kdaArr[$j];
				$avrgFirstblood+=$firstBloodArr[$j];
				$avrgddtc+=$ddtcArr[$j];
				/*
				//add condition if index exists,currently not checked
				if($checked010){
					$avrgCSDelta010=($avrgCSDelta010+$csDelta010Arr[$j])/$gamesPlayed;
				}
				if($checked1020){
					$avrgCSDelta1020=($avrgCSDelta1020+$csDelta1020Arr[$j])/$gamesPlayed;
				}
				if($checked2030){
					$avrgCSDelta2030=($avrgCSDelta2030+$csDelta2030Arr[$j])/$gamesPlayed;
				}*/
				$champIdNumArr[$j]=-1;
			}
		}
		//Stores the times played (values) with thier respective champions (keys) in an associative array.
		$champsWithCounts[$champIdNumArr[$i]] = $gamesPlayed;


		$indexCounter=1;// Keeps track of what index were at for deltas
		//store stats for each champ
		$champStats[$i][0]=$champIdNum;
		$champStats[$i][1]=array('Statistic' => "Average Gold",'Value' => $avrgGold/$gamesPlayed);
		$champStats[$i][2]=array('Statistic' =>"Average Game time (m)" , 'Value'=>$avrgGameTime/$gamesPlayed);
		$champStats[$i][3]=array('Statistic' =>"Average Champion lvl" ,'Value'=>$avrgChampLvl/$gamesPlayed );
		$champStats[$i][4]=array('Statistic' => "Win/Loss Rate (%)", 'Value'=>($winRate/$gamesPlayed)*100);
		$champStats[$i][5]=array('Statistic' => "Average amount of Wards Placed",'Value'=>$avrgWardsPlaced/$gamesPlayed );
		$champStats[$i][6]=array('Statistic' => "Average KDA",'Value'=>$avrgKDA/$gamesPlayed );
		$champStats[$i][7]=array('Statistic' => "Average First Blood (%)",'Value'=>($avrgFirstblood/$gamesPlayed)*100 );
		$champStats[$i][8]=array('Statistic' => "Average Damage Dealt to Champs", 'Value'=>$avrgddtc/$gamesPlayed);
		/*
		if($checked010){
			$champStats[$i][8+$indexCounter]=array('Statistic' =>" Average CS delta for 0-10 (m)" , 'Value'=>$avrgCSDelta010 );
			$indexCounter++;
	  }
		if($checked1020){
			$champStats[$i][8+$indexCounter]=array('Statistic' =>"Average CS delta for 10-20 (m)" , 'Value' =>$avrgCSDelta1020);
			$indexCounter++;
		}
		if($checked2030){
			$champStats[$i][8+$indexCounter]=array('Statistic' =>"Average CS delta for 20-30 (m)", 'Value' =>$avrgCSDelta2030);
	  }*/
		//reset games played before iterates again
		$gamesPlayed=1;
	}
}
//for testing purposes
for($a = 0; $a <= sizeof($champStats); $a++) {
	// b goes up to how many stats we have
  for($b = 0; $b <= 8; $b++) {
    if($b==0){
			echo "Champ: ".$champStats[$a][0]."<br>";
		}
		else {
       foreach ($champStats[$a][$b] as $key => $value) {
       	echo $key.": ".$value."<br>";
       }
			}
		}
		echo "<br>";
}
//sorts the associative array in descending order with respect to the values.
arsort($champsWithCounts);

//stores the top five champions
$topFiveChamps=array_slice($champsWithCounts, 0, 5, true);

/*
//still working on this part.
$champ=1;
$firstChampsStats=[];
$secondChampsStats=[];
$thirdChampsStats=[];
$fourthChampsStats=[];
$fifthChampsStats=[];
//stores top five champs STATS
for ($i=0; $i <5; $i++) {
	foreach ($topFiveChamps as $topChamp => $played) {
		if($topChamp==$champStats[$i][0]){
			for($j=0; $j<8;$j++){
				if($j==0){
					if($champ==1){
						$firstChampsStats['First Champion']=$champStats[$i][0];
					}
					else if($champ==2){
						$secondChampsStats['Second Champion']=$champStats[$i][0];
					}
					else if($champ==3){
						$thirdChampsStats['Third Champion']=$champStats[$i][0];
					}
					else if($champ==4){
						$fourthChampsStats['Fourth Champion']=$champStats[$i][0];
					}
					else{
						$fifthChampsStats['Fifth Champion']=$champStats[$i][0];
					}
				}
				else{
					foreach ($champStats[$i][$j] as $name => $value) {
							if($champ==1){
								$firstChampsStats[$name]= $value;
								echo $firstChampsStats;
							}
							else if($champ==2){
								$secondChampsStats[$name]= $value;
							}
							else if($champ==3){
								$thirdChampsStats[$name]= $value;
							}
							else if($champ==4){
								$fourthChampsStats[$name]= $value;
							}
							else{
								$fifthChampsStats[$name]= $value;
							}
					}
			  }
  		}

		}
	}
		$champ++;
}

//printing for testing purposes.
foreach ($firstChampsStats as $key => $value) {
 echo $key.": ".$value;
 echo "<br>";
}
foreach ($secondChampsStats as $key => $value) {
	echo $key.": ".$value;
  echo "<br>";
}
foreach ($thirdChampsStats as $key => $value) {
	echo $key.": ".$value;
  echo "<br>";
}
foreach ($fourthChampsStats as $key => $value) {
	echo $key.": ".$value;
  echo "<br>";
}
foreach ($fifthChampsStats as $key => $value) {
	echo $key.": ".$value;
  echo "<br>";
}*/
?>
