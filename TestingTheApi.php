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

$summonerName = "Ig Mythbran"; // HARDCODED SUMMONER NAME 
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase 
/*WHAT YOU CAN GET FROM THE ARRAY 
* print_r($account->getData()); //  Or array of all the data
* $account->id					//SUMMONER ID
* $account->accountId			//THIS IS THE ENCRYPTED ACCOUNTID. USE THIS FOR ALL OTHER REQUESTS
* $account->puuid				//NO IDEA 
* $account->name				//SUMMONER NAME 
* $account->profileIconId		//PROFILE ICON ID. REFERENCE DRAGONAPI FOR THE ICON 
* $account->revisionDate		//NO IDEA
* $account->summonerLevel		//SUMMONER LEVEL 
*/

/*PARAMETERS FOR MATCHLIST
* eventually we will be capturing ID 0 for custom games 
* Needs to be mapid = 1
* USAGE string ($encrypted_account_id, $queue = null, $season = null, 
* $champion = null, int $beginTime = null, int $endTime = null, int $beginIndex = null, int $endIndex = null)
* This gathers a list of games based on account ID. 
*/
$matchlist = $api->getMatchListByAccount($account->accountId); //WORKING

/* WHAT YOU CAN GET FROM THE ARRAY
* print_r($matchlist);		//PRINT THE ARRAY 
* $matchlist->totalGames; 	//TOTAL GAMES PLAYED
* $matchlist->startIndex; 	//START OF GAME COUNTER
* $matchlist->endIndex; 	//END OF GAME COUNTER
*/ 

/*BREAK DOWN THE ARRAY TO HAVE GAME SPECIFIC DATA
* This is to be able to retrieve gameId 
* We can use gameId to get game specific data
*/
foreach($matchlist->matches as $game)
	$gameIds[] = $game->gameId;
	
/* WHAT YOU CAN GET FROM THE ARRAY
* $game->lane;		//LANE PLAYED
* $game->gameId		//GAME IDENTIFICATION
* $game->champion	//WHICH CHAMP WAS PLAYED. SAVED AS AN ID AND NOT A CHAMP NAME. NEED TO REFERENCE DRAGONAPI FOR THIS
* $game->platformId	//WHICH SERVER THE GAME WAS PLAYED ON
* $game->season		//WHICH SEASON THE GAME WAS PLAYED IN. (side note. To get actual season you need to subtract 3 off the value)
* $game->queue		//QUEUE TYPE. SAVED AS AN ID. REFERENCE RIOT API DOCUMENTATION FOR WHAT THEY MEAN 
* $game->role		//ROLE PLAYED. SAVED IN A "DUO_SUPPORT, DUO, BOTTOM" FORMAT 
* $game->timestamp	//GAME TIMESTAMP. NOT SURE HOW TO BREAK THIS ONE DOWN 
* $game->staticData	//NOT SURE TBH. REFERENCE RIOT API DOCUMENTATION 
*/

/*GET SPECIFIC GAME DATA 
* Eventually we need to find a way to gather all 100 games 
* Needs to be compiled together 
* Eventually we'll need to find a way to find champion specific games too
* For now just gather ALL 100 games and compile them together 
*/

$matchData = $api->getMatch($gameIds[0]);
/*WHAT COMES OUT OF THE ARRAY 
* print_r($matchData);		//PRINTING THE MATCH DATA ARRAY 
* $matchData->seasonId		//SEASONID 
* $matchData->queueId		//QUEUEID 
* $matchData->gameId		//GAME ID
* $matchData->gameVersion	//VERSION OF THE GAME. HAS SEASON AND PATCH. use this for season
* $matchData->platformId	//SERVER
* $matchData->gameMode		//GAME MODE (CLASSIC, ARAM, ECT...)
* $matchData->mapid			//WHAT MAP THE GAME WAS PLAYED ON. ID NEEDS TO REFERENCE DRAGONAPI
* $matchData->gameType		//GAME TYPE 
* $matchData->gameDuration	//GAME DURATION 
* $matchData->gameCreation	//CREATION DATE 
* Need to break the array down to access this information below
* participantIdentities		//ARRAY THAT HOLDS INDIVIDUAL PLAYER ACCOUNT STATS 
* $teams					//HOLDS TEAM DATA IN REGARDS TO THE GAME 
* $participants				//THIS IS WHERE MOST OF THE STATS ARE HELD
*/

/*BREAKING DOWN THE PARTICIPANTIDENTITIES ARRAY OUT OF MATCHDATA
* This is needed to be able to access the participantIdentities array
* Can use the accountId to confirm that we're pulling the correct account
*/
foreach($matchData->participantIdentities as $participantIds){
		$participant[] = $participantIds->player;

}

/*WHAT THE ARRAY HAS
* USAGE** NEED TO DECLARE A POSITION IN THE ARRAY TO GATHER DATA 
* $participant[]->currentPlatformId	//SERVER
* $participant[]->summonerName		//SUMMONER NAME
* $participant[]->matchHistoryUri	//URI FOR THE PLAYER MATCH HISTORY ON THE RIOT SITE
* $partitipant[]->platformId		//PRETTY MUCH SAME THING AS CURRENTPLATFORMID
* $participant[]->currentAccountId	//ACCOUNTID
* $participant[]->profileIcon		//PROFILE ICON ID. MATCH WITH DRAGONAPI
* $participant[]->summonerId		//SPECIFIC ACCOUNT ID 
* $participant[]->accountId			//ACCOUNT ID
*/


/*THIS FINDS THE CORRECT USER OUT OF THE TEAM OF 5 
* This is done by comparing the accound IDs
* Basically if it matches, it's the correct summoner
* Also stores it into a new var named participantId
* This is used later on in the program 
* You can use this code to retrieve anything from the participant array above
*/
for($i=0; $i<10; $i++){
	if($participant[$i]->accountId == $account->accountId)
		$participantId = $i;
}

//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
$playerMatchData = $matchData->participants[$participantId]; 
/*YOU CAN GET A LOT FROM THIS ARRAY BE PREPARED FOR A LOT OF LINES 
/////////UNDER THE STATS ARRAY 
//DAMAGE STATS
* $playerMatchData->stats->magicDamageDealtToChampions			//MAGIC DAMAGE DEALT TO CHAMPS 
* $playerMatchData->stats->damageDealtToObjectives				//DAMAGE DEALT TO OBJECTIVES
* $playerMatchData->stats->physicalDamageDealtToChampions		//PHYSICAL DAMAGE DEALT TO CHAMPS
* $playerMatchData->stats->largestCriticalStrike				//LARGEST CRIT 
* $playerMatchData->stats->damageDealtToTurrets					//DAMAGE DEALT TO TURRETS
* $playerMatchData->stats->magicDamageDealt						//MAGIC DAMAGE DEALT OVERALL
* $playerMatchData->stats->damageSelfMitigated					//SELF MITIGATED DAMAGE 
* $playerMatchData->stats->magicalDamageTaken					//MAGIC DAMAGE TAKEN
* $playerMatchData->stats->trueDamageDealt						//TRUE DAMAGE DEALT 
* $playerMatchData->stats->trueDamageTaken						//TRUE DAMAGE TAKEN
* $playerMatchData->stats->totalDamageTaken						//TOTAL DAMAGE TAKEN 
* $playerMatchData->stats->physicalDamageDealt					//PHYSICAL DAMAGE DEALT OVERALL
* $playerMatchData->stats->totalDamageDealtToChampions			//TOTAL DAMAGE DEALT TO CHAMPS 
* $playerMatchData->stats->physicalDamageTaken					//PHYSICAL DAMAGE TAKEN 
* $playerMatchData->stats->totalDamageDealt						//TOTAL DAMAGE DEALT 
* $playerMatchData->stats->trueDamageDealtToChampions			//TRUE DAMAGE DEALT TO CHAMPS

//MINION STATS
* $playerMatchData->stats->neutralMinionsKilled					//JUNGLE MINIONS KILLED
* $playerMatchData->stats->neutralMinionsKilledTeamJungle		//TEAM JUNGLE MINIONS KILLED 
* $playerMatchData->stats->neutralMinionsKilledEnemyJungle		//ENEMY JUNGLE MINIONS KILLED
* $playerMatchData->stats->totalMinionsKilled					//CS  

//CC STATS
* $playerMatchData->stats->totalTimeCrowdControlDealt			//TOTAL CC TIME DEALT
* $playerMatchData->stats->timeCCingOthers						//TIME SPENT CCING OTHERS 

//KILL STATS
* $playerMatchData->stats->killingSprees						//KILLING SPREES
* $playerMatchData->stats->firstBloodAssist 					//FIRSTBLOOD ASSIST
* $playerMatchData->stats->firstBloodKill						//FIRST BLOOD BOOLEAN 
* $playerMatchData->stats->kills								//KILLS
* $playerMatchData->stats->assists								//ASSISTS
* $playerMatchData->stats->deaths								//DEATHS	
* $playerMatchData->stats->largestMultiKill						//LARGEST MULTIKILL
* $playerMatchData->stats->largestKillingSpree					//LARGEST KILLING SPREE
* $playerMatchData->stats->doubleKills							//DOUBLE KILLS 
* $playerMatchData->stats->tripleKill							//TRIPLE KILLS
* $playerMatchData->stats->quadraKills							//QUADRA KILLS
* $playerMatchData->stats->pentaKills							//PENTA KILLS	

//WARD STATS
* $playerMatchData->stats->visionScore							//VISIONSCORE
* $playerMatchData->stats->wardsKilled							//WARDS KILLED 
* $playerMatchData->stats->wardsPlaced							//WARDS PLACED 
* $playerMatchData->stats->visionWardsBoughtInGame				//VISION WARDS BOUGHT 

//MISC GAME STATS
* $playerMatchData->stats->LongestTimeSpentLiving				//LONGEST TIME SPENT LIVING 
* $playerMatchData->stats->goldSpent							//GOLD SPENT
* $playerMatchData->stats->participantId						//SUMMONER GAME ID LINKING ACCOUNT TO STATS 
* $playerMatchData->stats->win									//WIN LOSS. 1 = WIN 0 = LOSS 
 * $playerMatchData->stats->goldEarned							//GOLD EARNED 
* $playerMatchData->stats->champLevel							//CHAMP LVL 
* $playerMatchData->stats->totalHeal							//TOTAL HEALING DONE 

//STRUCTURE STATS 
* $playerMatchData->stats->inhibitorKills						//INHIBITOR KILLS 
* $playerMatchData->stats->firstInhibitorAssist					//FIRST INHIB KILL ASSIST 
* $playerMatchData->stats->firstInhibitorKill					//FIRST INHIBITOR KILL
* $playerMatchData->stats->turretKills							//TURRET KILLS
* $playerMatchData->stats->firstTowerAssist						//FIRST TOWER ASSIST 
* $playerMatchData->stats->firstTowerKill						//FIRST TOWER KILL 

//ITEMS
* $playerMatchData->stats->item6								//ITEM IN SLOT 7
* $playerMatchData->stats->item4								//ITEM IN SLOT 5
* $playerMatchData->stats->item5								//ITEM IN SLOT 6
* $playerMatchData->stats->item2								//ITEM IN SLOT 3
* $playerMatchData->stats->item3								//ITEM IN SLOT 4
* $playerMatchData->stats->item0								//ITEM IN SLOT 1
* $playerMatchData->stats->item1								//ITEM IN SLOT 2 

//PERKVARIABLES (RUNE STATS)
* $playerMatchData->stats->perkPrimaryStyle						//PRIMARY RUNE PATH 
* $playerMatchData->stats->perk0								//PRIMARY KEYSTONE RUNE
* $playerMatchData->stats->perk1								//PARIMARY PATH RUNE
* $playerMatchData->stats->perk1Var1							//POST GAME RUNE STATS (NOT SURE ON THIS ONE)
* $playerMatchData->stats->perk1Var3							//POST GAME RUNE STATS (SAME ^^)
* $playerMatchData->stats->perk1Var2							//SAME ^^ 
* $playerMatchData->stats->perk2								//PRIMARY PATH RUNE
* $playerMatchData->stats->perk2Var1							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk2Var2							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk2Var3							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk3								//PRIMARY PATH RUNE 
* $playerMatchData->stats->perk3Var1							//POST GAME RUNE STATS
* $playerMatchData->stats->perk3Var3							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk3Var2							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk5								//SECONDARY PATH RUNE
* $playerMatchData->stats->perk5Var3							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk5Var2							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk5Var1							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk4								//SECONDARY PATH RUNE
* $playerMatchData->stats->perk4Var1							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk4Var2							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perk4Var3							//POST GAME RUNE STATS (NOT SURE)
* $playerMatchData->stats->perkSubStyle							//SECONDARY RUNE PATH 
* $playerMatchData->stats->perk0Var1							//POST GAME RUNE STATS 
* $playerMatchData->stats->perk0Var2							//POST GAME RUNE STATS 
* $playerMatchData->stats->perk0Var3							//POST GAME RUNE STATS 

//USELESS VARIABLES
* $playerMatchData->stats->playerScore9							//NOT USED 
* $playerMatchData->stats->playerScore8							//NOT USED 
* $playerMatchData->stats->playerScore1							//NOT USED 
* $playerMatchData->stats->playerScore0							//NOT USED 
* $playerMatchData->stats->playerScore3							//NOT USED 
* $playerMatchData->stats->playerScore2							//NOT USED 
* $playerMatchData->stats->playerScore5							//NOT USED
* $playerMatchData->stats->playerScore4							//NOT USED 
* $playerMatchData->stats->playerScore7							//NOT USED 
* $playerMatchData->stats->playerScore6							//NOT USED 
* $playerMatchData->stats->totalScoreRank						//DOESN'T LOOK LIKE THIS IS USED
* $playerMatchData->stats->nodeNeutralizeAssist					//I THINK THIS IS FOR ANOTHER GAME MODE. PROBABLY WONT BE USED 
* $playerMatchData->stats->nodeCapture							//NOT USED 
* $playerMatchData->stats->totalUnitsHealed						//TOTAL UNITS HEALED. KINDA USELESS STAT
* $playerMatchData->stats->teamObjective						//DOESN'T LOOK LIKE IT'S USED 
* $playerMatchData->stats->nodeNeutralize						//FROM A DIFFERENT GAME MODE? MAYBE SKARNER STAT? 
* $playerMatchData->stats->combatPlayerScore					//I THINK FROM A DIFFERENT GAME MODE
* $playerMatchData->stats->sightWardsBoughtInGame				//LOL RIP SIGHT WARDS... :( 
* $playerMatchData->stats->totalPlayerScore						//NO IDEA WHAT THIS IS BUT NOT USED
* $playerMatchData->stats->objectivePlayerScore					//FOR A DIFFERENT GAME MODE 
* $playerMatchData->stats->altarsNeutralized					//NOT SUMMONERS RIFT 
* $playerMatchData->stats->unrealKills							//????????????????????????????? 
* $playerMatchData->stats->nodeCaptureAssist					//NOT FOR SUMMONERS RIFT
* $playerMatchData->stats->altarsCaptured						//NOT SUMMONERS RIFT


////IN THE ROOT ARRAY 
* $playerMatchData->participantId								//SUMMONER PARTICIPANT ID 
* $playerMatchData->runes 										//RUNES NOT USED. Legacy
* $playerMatchData->teamId										//TEAM IDENTIFICATION. 100 FOR BLUE 200 FOR RED 
* $playerMatchData->championId									//CHAMPION ID. REFERENCE DRAGONAPI
* $playerMatchData->spell1Id									//SUMMONER SPELL 1 ID 
* $playerMatchData->spell2Id									//SUMMONER SPELL 2 ID 
* $playerMatchData->highestAchievedSeasonTier					//HIGHEST ACHIEVED PREVIOUS SEASON RANK 									


////TIMELINE DATA 
* $playerMatchData->timeline->lane								//WHAT LANE WAS PLAYED 
* $playerMatchData->timeline->participantId						//PARTICIPANT ID 
* $playerMatchData->timeline->csDiffPerMinDeltas				//NOT USED 
* $playerMatchData->timeline->goldPerMinuteDeltas				//NEED TO LOOK UP MORE INFO ON THIS 
* 10-20
* 0-10
* 20-30
* $playerMatchData->timeline->xpDiffPerMinDeltas				//NOT USED 
* $playerMatchData->timeline->creepsPerMinDeltas				//NEED TO LOOK UP MORE INFO ON THIS 
* 10-20
* 0-10
* 20-30
* $playerMatchData->timeline->xpPerMinDeltas
* 10-20
* 0-10
* 20-30
* $playerMatchData->timeline->role								//ROLE PLAYED 
* $playerMatchData->timeline->damageTakenDiffPerMinDeltas		//NOT USED 
* $playerMatchData->timeline->damageTakenPerMinDeltas			//NEED MORE INFO ON THIS 
* 10-20
* 0-10
* 20-30
*/

//$playerMatchData->stats-> WHERE MOST DATA IS KEPT
//PERKS = RUNES BTW 
//GAME STAT VARIABLES
$season			= ($matchData->gameVersion) ; 								//SEASON 
$totalGames 	= $matchlist->totalGames; 									//TOTAL GAMES PLAYED ON THE ACCOUNTID
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
print_r($participant[$participantId]->summonerName);
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