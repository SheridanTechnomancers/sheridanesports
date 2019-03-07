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
//  Initialize the library
$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-b1c70cf8-1118-4467-946d-1ff43b3dd95d',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

$summonerName = "ostrlch"; //HARDCODED SUMMONER NAME 
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase 

/*WHAT YOU CAN GET FROM THE ARRAY 
* print_r($account->getData()); //Or array of all the data
* $account->id					//SUMMONER ID
* $account->accountId			//THIS IS THE ENCRYPTED ACCOUNTID. USE THIS FOR ALL OTHER REQUESTS
* $account->puuid				//NO IDEA 
* $account->name				//SUMMONER NAME 
* $account->profileIconId		//PROFILE ICON ID. REFERENCE DRAGONAPI FOR THE ICON 
* $account->revisionDate		//NO IDEA
* $account->summonerLevel		//SUMMONER LEVEL 
*/

/*PARAMETERS FOR MATCHLISTSolo
* eventually we will be capturing ID 0 for custom games 
* 420 for solo q
* 440 for flex 
* Needs to be mapid = 1
* USAGE string ($encrypted_account_id, $queue = null, $season = null, 
* $champion = null, int $beginTime = null, int $endTime = null, int $beginIndex = null, int $endIndex = null)
* This gathers a list of games based on account ID. 
* string $encrypted_account_id, $queue = null, $season = null, $champion = null, int $beginTime = null, int $endTime = null, int $beginIndex = null, int $endIndex = null
*/
$matchlistSolo = $api->getMatchListByAccount($account->accountId, 420); //WORKING
//$matchlistSolo = $api->getMatchListByAccount($account->accountId, 440);

//print_r($matchlistSolo);
/* WHAT YOU CAN GET FROM THE ARRAY
* print_r($matchlistSolo);		//PRINT THE ARRAY 
* $matchlistSolo->totalGames; 	//TOTAL GAMES PLAYED
* $matchlistSolo->startIndex; 	//START OF GAME COUNTER
* $matchlistSolo->endIndex; 	//END OF GAME COUNTER
*/ 

/*BREAK DOWN THE ARRAY TO HAVE GAME SPECIFIC DATA
* This is to be able to retrieve gameId 
* We can use gameId to get game specific data
*/
//FOR SOLO Q 
foreach($matchlistSolo->matches as $game){
	$gameIds[] = $game->gameId;
	$gameChampId[] = $game->champion; 
}
//print_r($matchlistSolo);

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

$matchData = $api->getMatch($gameIds[27]);
//print_r($matchData->participantIdentities[0]->player->accountId);
print_r($matchData);
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
* +participantIdentities	//ARRAY THAT HOLDS INDIVIDUAL PLAYER ACCOUNT STATS 
* +teams					//HOLDS TEAM DATA IN REGARDS TO THE GAME 
* +participants				//THIS IS WHERE MOST OF THE STATS ARE HELD
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

/*THIS BREAKS DOWN THE TEAMS ARRAY
* Team arrays give you team specific data  
* This can be anything regarding the game 
* linked to a specific team 
*/
foreach($matchData->teams as $teams){
		$team[] = $teams;

}

/*WHAT THE ARRAY HAS 
***** USAGE : HAS 2 ARRAYS INSIDE. ONE FOR RED TEAM AND ONE FOR BLUE $team[1/2]
* $team[]->firstDragon						//FIRST DRAGON BOOLEAN 
* $team[]->firstInhibitor					//WHICH  TEAM GOT THE FIRST INHIB 
* $team[]->baronKills						//HOW MANY BARON KILLS
* $team[]->firstRiftHerald					//RIFT HERALD KILL
* $team[]->firstBaron						//FIRST BARON KILL
* $team[]->riftHeraldKills					//UHH PROBABLY WONT BE USED. CAN ONLY HAVE 1 RIFT HERALD
* $team[]->firstBlood						//FIRST BLOOD 
* $team[]->teamId							//WHICH SIDE YOU'RE ON. 100 FOR BLUE 200 FOR RED 
* $team[]->firstTower						//WHICH TEAM TOOK FIRST TOWER BOOLEAN 
* $team[]->vilemawKills						//NOT IN SUMMONERS RIFT
* $team[]->inhibitorKills					//HOW MANY INHIBS EACH TEAM TOOK 
* $team[]->towerKills						//HOW MANY TOWERS EACH TEAM TOOK 
* $team[]->dominionVictoryScore				//NOT IN SUMMONERS RIFT. ACTUALLY LEGACY NOT IN THE GAME 
* $team[]->win								//WIN OR LOSS 
* $team[]->dragonKills						//DRAGON KILLS 
/////$team[]->bans[]->						//BANS 0-4 FOR EACH TEAM. THIS IS CHAMPION BAN
* $team[]->bans[]->pickTurn					//BAN TURN 
% $team[]->bans[]->championId				//WHICH CHAMP WAS BANNED 
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
* $playerMatchData->timeline->goldPerMinDeltas[' ']				//GOLD PER MINUTE DELTAS. USE WITH [' '] TO GATHER SPECIFIC TIME DATA  
* ['10-20']														//10-20 MINUTES. MUST USE WITH COMMENT TAGS 
* ['0-10']														//0-10 MINUTES. MUST USE WITH COMMENT TAGS 
* ['20-30']														//20-30 MINUTES. MUST USE WITH COMMENT TAGS 
* $playerMatchData->timeline->xpDiffPerMinDeltas[' ']			//NOT USED 
* $playerMatchData->timeline->creepsPerMinDeltas[' ']			//CS PER MINUTE DELTAS. USE WITH [' '] TO GATHER SPECIFIC TIME DATA  
* ['10-20']														//10-20 MINUTES. MUST USE WITH COMMENT TAGS 
* ['0-10']														//0-10 MINUTES. MUST USE WITH COMMENT TAGS 
* ['20-30']														//20-30 MINUTES. MUST USE WITH COMMENT TAGS 
* $playerMatchData->timeline->xpPerMinDeltas[' ']				//XP PER MINUTE DELTAS. USE WITH [' '] TO GATHER SPECIFIC TIME DATA  
* ['10-20']														//10-20 MINUTES. MUST USE WITH COMMENT TAGS 
* ['0-10']														//0-10 MINUTES. MUST USE WITH COMMENT TAGS 
* ['20-30']														//20-30 MINUTES. MUST USE WITH COMMENT TAGS 
* $playerMatchData->timeline->role								//ROLE PLAYED 
* $playerMatchData->timeline->damageTakenDiffPerMinDeltas		//NOT USED 
* $playerMatchData->timeline->damageTakenPerMinDeltas[' ']		//DAMAGE TAKEN PER MINUTE DELTAS. USE WITH [' '] TO GATHER SPECIFIC TIME DATA  
* ['10-20']														//10-20 MINUTES. MUST USE WITH COMMENT TAGS 
* ['0-10']														//0-10 MINUTES. MUST USE WITH COMMENT TAGS 
* ['20-30']														//20-30 MINUTES. MUST USE WITH COMMENT TAGS 
*/

//DRAGONAPI CALL FOR CHAMPION DATA  
$champion = $api->getStaticChampion($playerMatchData->championId, true); 
print_r($playerMatchData->timeline->role);

//REQUIRED VARIABLES
////GAME/PLAYER INFORMATION 
$season			= ($matchData->gameVersion) ; 								//SEASON 
$totalGames 	= $matchlistSolo->totalGames; 									//TOTAL GAMES PLAYED ON THE ACCOUNTID
$gameTimeH		= floor(($matchData->gameDuration)/3600);					//TOTAL HOURS GAME TIME 
$gameTimeM		= floor(($matchData->gameDuration)/60%60);					//TOTAL MINUTES GAME TIME 
$gameTimeS		= floor(($matchData->gameDuration)%60);						//TOTAL SECONDS GAME TIME 
$gameTime		= "$gameTimeH : $gameTimeM : $gameTimeS";					//GAME DURATION

////GAME STATS 
$kills 			= $playerMatchData->stats->kills; 							//KILLS
$assists 		= $playerMatchData->stats->assists; 						//ASSIST
$deaths 		= $playerMatchData->stats->deaths; 							//DEATHS
//CALCULATING KDA
if($deaths == 0) 															//IF THERE'S NO DEATHS
	$kda 		= $kills + $assists;										//KDA WITHOUT DEATHS 
else 																		//IF DEATHS 
	$kda 		= $kills + $assists / $deaths; 								//KDA WITH DEATHS 
$cs 			= $playerMatchData->stats->totalMinionsKilled;				//CS 
$jungleCS		= $playerMatchData->stats->neutralMinionsKilled; 			//JUNGLE CS 
$csDelta010		= $playerMatchData->timeline->creepsPerMinDeltas['0-10'];	//CS DELTA FOR MINUTES 0-10;
$csDelta1020	= $playerMatchData->timeline->creepsPerMinDeltas['10-20'];	//CS DELTA FOR MINUTES 10-20
$csDelta2030	= $playerMatchData->timeline->creepsPerMinDeltas['20-30'];	//CS DELTA FOR MINUTES 20-30
$ltsl 			= $playerMatchData->stats->longestTimeSpentLiving;			//LONGEST TIME SPENT LIVING 
$visionScore 	= $playerMatchData->stats->visionScore; 					//VISION SCORE
$wardsKilled	= $playerMatchData->stats->wardsKilled;						//WARDS KILLED
$wardsPlaced	= $playerMatchData->stats->wardsPlaced;						//WARDS PLACED 
$visionBought	= $playerMatchData->stats->visionWardsBoughtInGame;			//VISION WARDS BOUGHT 
$ddtc			= $playerMatchData->stats->totalDamageDealtToChampions;		//TOTAL DAMAGE DEALT TO CHAMPS 
$killingSpree	= $playerMatchData->stats->killingSprees;					//KILLING SPREES 
$goldEarned		= $playerMatchData->stats->goldEarned; 						//GOLD EARNED 
//WIN/LOSS 	
if($playerMatchData->stats->win == 1)										//WON GAME 
	$results 	= "Win"; 
else
	$results 	= "Loss;";													//LOST GAME 
$champLvl		= $playerMatchData->stats->champLevel;						//CHAMPION LEVEL 
if($playerMatchData->stats->firstBloodKill == 1)							//IF GOT FIRST BLOOD
	$firstBlood = "Yes";
else																		//NO FIRST BLOOD 
	$firstBlood = "No";
$goldDelta010	= $playerMatchData->timeline->goldPerMinDeltas['0-10']; 	//GOLD PER MINUTE DELTA MINUTES 0-10
$goldDelta1020	= $playerMatchData->timeline->goldPerMinDeltas['10-20'];	//GOLD PER MINUTE DELTA MINUTES 10-20
$goldDelta2030	= $playerMatchData->timeline->goldPerMinDeltas['20-30'];	//GOLD PER MINUTE DELTA MINUTES 20-30
$cspermin		= round($cs/$gameTimeM, 2);									//CS PER MINUTE 
$champName 		= $champion->name; 											//CHAMPION NAME 
if($playerMatchData->teamId	== 100)											//BLUE SIDE 
	$teamSide 	= "Blue"; 
elseif($playerMatchData->teamId == 200)										//RED SIDE
	$teamSide 	= "Red";
else																		//ERROR 
	$teamSide 	= "Error"; 
$matchIdArray[] = 0;														//INITIALIZING THE MATCHID ARRAY
$champIdNum[] = 0; 
for($i=0; $i<100; $i++)														//STORING 100 LATEST GAMES
	$matchIdArray[$i] = $gameIds[$i];										//GAME IDS STORED IN MATCHIDARRAY

//TESTCODE
//for($i=0; $i<100; $i++){
	//print("$gameChampId[$i]<br>");
	//print(($api->getStaticChampion($gameChampId[$i], true))->name);
	//echo "<br>";
//}


/*

//PRINT VALUES
//SUMMONER NAME 
print "$summonerName | Played: $champName <br>";

//GAME STUFF
//GAME INFORMATION 
print "Season: $season | Team Side: $teamSide | Game Duration: $gameTime<br><br>";

//KDA
print "KDA: $kills/$deaths/$assists |$kda </br>";

//CS DELTAS 
print "CS Deltas: 0-10: $csDelta010 | 10-20: $csDelta1020 | 20-30: $csDelta2030 </br>";

//GOLD DELTAS 
print "Gold Deltas: 0-10: $goldDelta010 | 10-20: $goldDelta1020 | 20-30: $goldDelta2030 </br>";

//KILLING SPREE
print "Killing Spree: $killingSpree <br>";

//LONGEST TIME SPENT LIVING 
print "Longest Time Spent Living : $ltsl </br>";

//WIN/Loss
print "Results: $results <br>";

//CHAMPION LEVEL
print "Champion Level: $champLvl <br>";

//FIRST BLOOD
print "First Blood: $firstBlood <br>";

//GOLD EARNED 
print "Gold Earned: $goldEarned <br>";

//CS STUFF
//CS
print "CS: $cs </br>";

//JUNGLE CS 
if($jungleCS != 0)
	print "Jungle CS: $jungleCS <br>";

//CS PER MINUTE 
print "CS/Min: $cspermin <br>";

//VISION STUFF 
//VISION SCORE
print "Vision Score: $visionScore </br>";

//WARDS KILLED
print "Wards Killed : $wardsKilled <br>";

//VISION WARDS BOUGHT 
print "Vision Wards Bought : $visionBought <br>";

//WARDS PLACED 
print "Wards Placed: $wardsPlaced<br>";

//DAMAGE STUFF 
print "Damage Dealt to Champs: $ddtc <br>";*/


?>