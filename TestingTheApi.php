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

$summonerName = "TSM Bjergsen";

//  And now you are ready to rock!
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase 
//print_r($account->getData());  //  Or array of all the data


//PARAMETERS FOR MATCHLIST
//eventually we will be capturing ID 0 for custom games 
//Needs to be mapid = 1

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

$playerMatchData = $matchData->participants[$participantId];

//print_r($playerMatchData);

//$playerMatchData->stats-> WHERE MOST DATA IS KEPT
//PERKS = RUNES BTW 
//GAME STAT VARIABLES 
$kills 			= $playerMatchData->stats->kills; 							//KILLS
$assists 		= $playerMatchData->stats->assists; 						//ASSIST
$deaths 		= $playerMatchData->stats->deaths; 							//DEATHS
																			//CALCULATING KDA
if($deaths == 0) 															//IF THERE'S NO DEATHS
$kda 			= $kills + $assists;										//KDA WITHOUT DEATHS 
else 																		//IF DEATHS 
$kda 			= $kills + $assists / $deaths; 								//KDA WITH DEATHS 
$cs 			= $playerMatchData->stats->totalMinionsKilled;				//CS 
$ltsl 			= $playerMatchData->stats->longestTimeSpentLiving;			//LONGEST TIME SPENT LIVING 
$visionScore 	= $playerMatchData->stats->visionScore; 					//VISION SCORE
//$mDDTG 		= $playerMatchData->stats->magicDamageDealtToChampions;		//MAGIC DAMAGE DEALT TO CHAMPS (NOT USED RN)
//$dDTO 		= $playerMatchData->stats->damageDealtToObjectives;			//DAMAGE DEALT TO OBJECTIVES (NOT USED RN) 
//$tTCCD		= $playerMatchData->stats->totalTimeCrowdControlDealt;		//TOTAL CC TIME DEALT (NOT USED RN) 
//$dDTT			= $playerMatchData->stats->damageDealtToTurrets;			//DAMAGE DEALT TO TURRETS(NOT USED RN )
$jungleCS		= $playerMatchData->stats->neutralMinionsKilled; 			//JUNGLE CS 
//$pDDTC		= $playerMatchData->stats->physicalDamageDealtToChampions;	//PHYSICAL DAMAGE DEALT TO CHAMPS (NOT USED RN) 
//lMultiKill	= $playerMatchData->stats->largestMutliKill;				//LARGEST MUTLIKILL (NOT USED RN) 
$wardsKilled	= $playerMatchData->stats->wardsKilled;						//WARDS KILLED
//$largestCrit	= $playerMatchData->stats->largestCriticalStrike;			//LARGEST CRITICAL STRIKE (NOT USED RN) 
//$lKillingSpree= $playerMatchData->stats->largestKillingSpree;				//LARGEST KILLING SPREE (NOT USED RN)
//$tripleKills 	= $playerMatchData->stats->tripleKills;						//TRIPLE KILLS (NOT USED RN)
//$quadraKills	= $playerMatchData->stats->quadraKills; 					//QUADRA KILLS (NOT USED RN)
//$doubleKills 	= $playerMatchData->stats->doubleKills;						//DOUBLE KILLS (NOT USED RN)
//$pentaKills	= $playerMatchData->stats->pentaKills; 						//PENTA KILLS (NOT USED RN) 
//$magicDmgDealt= $playerMatchData->stats->magicDamageDealt;				//MAGIC DAMAGE DEALT (NOT USED RN) 
//$item1		= $playerMatchData->stats->item0;							//ITEM 1 (NOT USED RN BUT NEEDS TO BE)
//$item2		= $playerMatchData->stats->item1; 							//ITEM 2 (NOT USED YET BUT NEEDS TO BE)
//$item3		= $playerMatchData->stats->item2; 							//ITEM 3 (NOT USED YET BUT NEEDS TO BE)
//$item4		= $playerMatchData->stats->item3;							//ITEM 4 (NOT USED RN BUT NEEDS TO BE)
//$item5		= $playerMatchData->stats->item4; 							//ITEM 5 (NOT USED YET BUT NEEDS TO BE)
//$item6		= $playerMatchData->stats->item5; 							//ITEM 6 (NOT USED YET BUT NEEDS TO BE)
//$trinket		= $playerMatchData->stats->item6; 							//trinket????(NOT USED YET BUT NEEDS TO BE)
//$selfMitgDmg	= $playerMatchData->stats->damageSelfMitigated;				//SELF DAMAGE MITIGATED (NOT USED RN)
//$mgcDmgTkn	= $playerMatchData->stats->magicalDamageTaken;				//MAGIC DAMAGE TAKEN(NOT USED RN) 
//$fInhibKill	= $playerMatchData->stats->firstInhibitorKill;				//FIRST INHIBITOR KILL(NOT USED RN) 
//$truedmgtkn	= $playerMatchData->stats->trueDamageTaken; 				//TRUE DAMAGE TAKEN(NOT USED RN)
//$goldSpent	= $playerMatchData->stats->goldSpent; 						//GOLD SPENT(NOT USED RN)
//$truedmgdealt	= $playerMatchData->stats->trueDamageDealt;					//TRUE DAMAGE DEALT (NOT USED RN)
//$tDmgTaken	= $playerMatchData->stats->totalDamageTaken;				//TOTAL DAMAGE TAKEN(NOT USED RN) 
//$physicDmgDlt	= $playerMatchData->stats->physicalDamageDealt;				//PHYSICAL DAMAGE DEALT (NOT USED RN) 
//$tDDTC		= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS (NOT USED RN)
//$physicDmgTkn	= $playerMatchData->stats->physicalDamageTaken;				//PHYSICAL DAMAGE TAKEN (NOT USED RN) 
$results = "";																//GAME RESULTS VARIABLE
if($playerMatchData->stats->win == 1)										//WIN CODE 
	$results = "Win"; 														//WIN 
else																		//LOSS CODE
	$results = "Loss";														//Loss
//$tDmgDealt	= $playerMatchData->stats->totalDamageDealt; 				//TOTAL DAMAGE DEALT(NOT USED RN) 
$wardsPlaced 	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED 
$firstBlood = "";															//FIRST BLOOD VARIABLE
if($playerMatchData->stats->firstBloodKill == 1)							//YES FIRST BLOOD CODE
	$firstBlood = "Yes"; 													//FIRST BLOOD 
else																		//NO FIRST BLOOD CODE
	$firstBlood = "No";														//FIRST BLOOD
$turretKills	= $playerMatchData->stats->turretKills;						//TURRET KILLS
$goldEarned 	= $playerMatchData->stats->goldEarned;						//GOLD EARNED 
//$killingSprees= $playerMatchData->stats->killingSprees;					//KILLING SPREE (NOT USED RN)
//$fTowerAssist	= $playerMatchData->stats->firstTowerAssist;				//FIRST TOWER ASSIST (NOT USED RN)
//$fTowerKill	= $playerMatchData->stats->firstTowerKill; 					//FIRST TOWER KILL (NOT USED RN) 
$champLvl		= $playerMatchData->stats->champLevel; 						//CHAMPION LEVEL 
//$inhibitorKill= $playerMatchData->stats->inhibitorKills; 					//INHIBITOR KILLS (NOT USED RN) 
$visionBought	= $playerMatchData->stats->visionWardsBoughtInGame;			//VISION WARDS BOUGHT 
//$totalHeal 	= $playerMatchData->stats->totalHeal; 						//TOTAL HEAL(NOT USED RN) 
//$timeCCOthers	= $playerMatchData->stats->timeCCingOthers					//TIME CCING OTHERS

//PRINT VALUES
//SUMMONER NAME 
print_r($summonerName);
echo "</br>";
echo "</br>";
echo "</br>";

//GAME STUFF
//KDA
print "KDA: $kda";
echo "</br>";

//LONGEST TIME SPENT LIVING 
print "Longest Time Spent Living : $ltsl";
echo "</br>";

//WIN/Loss
print "Results: $results";
echo "<br>";

//CHAMPION LEVEL
print "Champion Level: $champLvl";
echo "<br>";

//TURRET KILLS
print "Turret Kills: $turretKills";
echo "<br>";

//FIRST BLOOD
print "First Blood: $firstBlood";
echo "<br>";

//GOLD EARNED 
print "Gold Earned: $goldEarned";
echo "<br>";

//CS STUFF
//CS
print "CS: $cs";
echo "</br>";

//JUNGLE CS 
if($jungleCS != 0){
	print "Jungle CS: $jungleCS";
	echo "<br>";
}


//VISION STUFF 
//VISION SCORE
print "Vision Score: $visionScore";
echo "</br>";

//WARDS KILLED
print "Wards Killed : $wardsKilled";
echo "<br>";

//VISION WARDS BOUGHT 
print "Vision Wards Bought : $visionBought";
echo "<br>";

//WARDS PLACED 
print "Wards Placed: $wardsPlaced";
echo "<br>";



?>