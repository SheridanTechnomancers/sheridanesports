<?php
//Abdulrahman Khan (lordfireriser | monamoe)
//Untested Version #2


// Searches through a user's recent matches and returns data for games played in the Jungle lane
//Currently only goes through the last 20 matches due to API rate limitations
//This can be changed my changing the value of $numberOfMatches on line 42.

// Data this program returns:
// Win %
// KDA
// Gold Deltas  ( 0-10, 10-20, 20-30)
// Average Gold Earned
// Average Game Time
// Average First Blood
// Average Champion Level
// Average Damage Delt To Champions
// Average Vision Score
// Average Wards Bought
// Average Wards Killed
// Average Wards placed

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
	LeagueAPI::SET_KEY    => 'RGAPI-d06cb91b-d650-4c4c-a0e8-d6de40024a21',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::NORTH_AMERICA,
]);

$summonerName = "Ig Mythbran"; //HARDCODED SUMMONER NAME 
$account = $api->getSummonerByName($summonerName); //WORKING. Needs to get summoner name from somwhere. Probably login dbase 

//gets list of matches from that user
$matchlist = $api->getMatchListByAccount($account->accountId);

//gets the game id for each game, stores in the $gameIds array
foreach($matchlist->matches as $game)
	$gameIds[] = $game->gameId;

//gets the game id for each game, stores in the $gameIds array
foreach($matchlist->matches as $game){
    $gameIds[] = $game->gameId;
}

////gathering the date for the match
$numberOfMatches = 20;
$winLost = array();
$kills = array();
$deaths = array();
$assists = array();
//gold deltas
$goldDelta_0_10 = array();
$numberOfMatchesWithDelta0 = 0;
$goldDelta_10_20 = array();
$numberOfMatchesWithDelta10 = 0;
$goldDelta_20_30 = array();
$numberOfMatchesWithDelta20 = 0;

$goldEarned = array();
$gameTime = array();
$firstBlood = array();
$championLevel = array();
$ddtc = array();
$visionScore = array();
$aveVisionWardsBought = array();
$wardsKilled = array();
$aveWardsPlaced = array();




//counts the number of games played in the JUNGLE LANE
$numberOfScrimsInJUNGLE = 0;
//Looks through every game (size of the $gameIds array)
for($x=0 ; $x<$numberOfMatches; $x++){

	//match data for that one match, starts at zero
	$matchData = $api->getMatch($gameIds[$x]);

	//gets the participants in that match
	foreach($matchData->participantIdentities as $participantIds){
		$participant[] = $participantIds->player;
	}

	//idk what this does ~lordfireriser
	foreach($matchData->teams as $teams){
			$team[] = $teams;
	}

	//looping through the players in the skrim and finding the data for the playerstats with the username we want
	//FINDING OUR PLAYER IN THE SKRIM, sets its value to $participantId
	for($i=0; $i<10; $i++){
		if($participant[$i]->accountId == $account->accountId)
			$participantId = $i;
	}

	//MOVES THE MATCHDATA ARRAY INTO IT'S OWN VARABLE. BREAKING UP THE ARRAY
	$playerMatchData = $matchData->participants[$participantId];

	//DRAGONAPI CALL FOR CHAMPION DATA  
	$champion = $api->getStaticChampion($playerMatchData->championId, true); 

	//name of the champion the player used that game
	$championName = $champion->name;
    //lane played
    $LanePlayed = $playerMatchData->timeline->lane;

    //check if the game was played in the jungle lane
    if( $LanePlayed == "JUNGLE" ){
        //increace the counter
        $numberOfScrimsInJUNGLE = $numberOfScrimsInJUNGLE + 1;
        
        //gathering the data from this single match
        
        //WIN/LOSS 	
        if($playerMatchData->stats->win == 1)
            $winLost[$x] = 1; 
        else{
            $winLost[$x] = 0;
        }

        //first blood
        if($playerMatchData->stats->firstBloodKill == 1)
            $firstBlood[$x] = 1;    //got first blood
        else
            $firstBlood[$x] = 0;    //didnt get first blood
        
        //GOLD DELTAS
        $gameLength = $matchData->gameDuration;
        //if the game was at least 10 minutes long
        if($gameLength >= 600){
            $numberOfMatchesWithDelta0 = $numberOfMatchesWithDelta0 + 1;                    //increace counter
            $goldDelta_0_10[$x] = $playerMatchData->timeline->goldPerMinDeltas['0-10'];     //gold delta 0 - 10 mins
        }
        else{
            $goldDelta_0_10[$x] = 0;
        }

        //if the game was at least 20 minutes long
        if($gameLength >= 1200){
            $numberOfMatchesWithDelta10 = $numberOfMatchesWithDelta10 + 1;                    //increace counter
            $goldDelta_10_20[$x] = $playerMatchData->timeline->goldPerMinDeltas['10-20'];     //gold delta 10 - 20 mins
        }
        else{
            $goldDelta_10_20[$x] = 0;
        }

        //if the game was longer than 30 minutes
        if($gameLength >= 1800){
            $numberOfMatchesWithDelta20 = $numberOfMatchesWithDelta20 + 1;                    //increace counter
            $goldDelta_20_30[$x] = $playerMatchData->timeline->goldPerMinDeltas['20-30'];     //gold delta 20 - 30 mins
        }
        else{
            $goldDelta_20_30[$x] = 0;
        }

        //Data that doesnt require extra math to extract from the api
        $kills[$x] = $playerMatchData->stats->kills;
        $deaths[$x] = $playerMatchData->stats->deaths;
        $assists[$x] = $playerMatchData->stats->assists;
        $goldEarned[$x] = $playerMatchData->stats->goldEarned;
        $gameTime[$x] = $gameLength;
        $ddtc[$x] = $playerMatchData->stats->totalDamageDealtToChampions;
        $championLevel[$x] = $playerMatchData->stats->champLevel;
        $visionScore[$x] = $playerMatchData->stats->visionScore;
        $wardsKilled[$x] = $playerMatchData->stats->wardsKilled;
        $aveWardsPlaced[$x] = $playerMatchData->stats->wardsPlaced;
        $aveVisionWardsBought[$x] = $playerMatchData->stats->visionWardsBoughtInGame;
    }//end of if jungle statement
}//end of for loop

//OUTPUT VARIBLES
$winRate = 0;                      //deafult values
$KDA = 0;                          //deafult values
$averageGoldEarned = 0;            //deafult values
$averageGameTime = 0;              //deafult values
$averageFirstBlood = 0;            //deafult values
$averageDamageDeltToChampions = 0; //deafult values
$averageChampionLevel = 0;         //deafult values
$averageVisionScore = 0;           //deafult values
$averageWardsKilled = 0;           //deafult values
$averageWardsPlaced = 0;           //deafult values
$averageVisionWardsBought = 0;     //deafult values

//GOLD DELTAS
$averageDelta_0_10 = 0;
$averageDelta_10_20 = 0;
$averageDelta_20_30 = 0;

//EXTRA VARIABLES
$averageKills = 0;                 //deafult values
$averageDeaths = 0;                //deafult values
$averageAssists = 0;               //deafult values

//extracting data from the arrays
//Winning %
foreach( $winLost as $value ) {
    $winRate = $winRate + $value;
 }

//Average Gold Earned
foreach( $goldEarned as $value ) {
    $averageGoldEarned = $averageGoldEarned + $value;
 }

//Average Game Time
foreach( $gameTime as $value ) {
    $averageGameTime = $averageGameTime + $value;
 }

//Average First Blood
foreach($firstBlood as $value ){
    $averageFirstBlood = $averageFirstBlood + $value;
}

//Average Kills
foreach( $kills as $value ) {
    $averageKills = $averageKills + $value;
 }

//Average Assists
foreach( $assists as $value ) {
    $averageAssists = $averageAssists + $value;
 }

//Average Deaths
foreach( $deaths as $value ) {
    $averageDeaths = $averageDeaths + $value;
 }

//Average Damage Dealt To Champions
foreach( $ddtc as $value ) {
    $averageDamageDeltToChampions = $averageDamageDeltToChampions + $value;
 }

//Average Champion Level
foreach( $championLevel as $value ) {
    $averageChampionLevel = $averageChampionLevel + $value;
 }

//Vision Score
foreach( $visionScore as $value ) {
    $averageVisionScore = $averageVisionScore + $value;
 }

//Wards Killed
foreach( $wardsKilled as $value ) {
    $averageWardsKilled = $averageWardsKilled + $value;
 }

//Wards Placed
foreach( $aveWardsPlaced as $value ) {
    $averageWardsPlaced = $averageWardsPlaced + $value;
 }

//Wards Bought
foreach( $aveVisionWardsBought as $value ) {
    $averageVisionWardsBought = $averageVisionWardsBought + $value;
 }


//Gold Delta 0 - 10
foreach( $goldDelta_0_10 as $value ) {
    $averageDelta_0_10 = $averageDelta_0_10 + $value;
}

//Gold Delta 10 - 20
foreach( $goldDelta_10_20 as $value ) {
    $averageDelta_10_20 = $averageDelta_10_20 + $value;
}

//Gold Delta 20 - 30
foreach( $goldDelta_20_30 as $value ) {
    $averageDelta_20_30 = $averageDelta_20_30 + $value;
}




if ($numberOfScrimsInJUNGLE == 0) {
    print "could not find any games";
} else {       //found at least 1 game
    //CALCULATE AVERAGES
    $winRate = ($winRate / $numberOfScrimsInJUNGLE) * 100;
    $averageGoldEarned = $averageGoldEarned / $numberOfScrimsInJUNGLE;
    $averageGameTime = $averageGameTime / $numberOfScrimsInJUNGLE;
    $averageFirstBlood = $averageFirstBlood / $numberOfScrimsInJUNGLE;
    $averageDamageDeltToChampions = $averageDamageDeltToChampions / $numberOfScrimsInJUNGLE;
    $averageChampionLevel = $averageChampionLevel / $numberOfScrimsInJUNGLE;
    $averageVisionScore = $averageVisionScore / $numberOfScrimsInJUNGLE;
    $averageWardsKilled = $averageWardsKilled / $numberOfScrimsInJUNGLE;
    $averageWardsPlaced = $averageWardsPlaced / $numberOfScrimsInJUNGLE;
    $averageVisionWardsBought = $averageVisionWardsBought / $numberOfScrimsInJUNGLE;

    //KDA
    if ($averageDeaths == 0){
        $averageDeaths = 1;
    }
    $KDA = (($averageKills + $averageAssists) / $averageDeaths) / $numberOfScrimsInJUNGLE;

    //GOLD DELTAS
    //if a the program doesnt find a game longer than 10 minutes, then $numberOfMatchesWithDelta0.
    //set that value to 1 to avoid the "NAN" output you get when dividing by 0
    if($numberOfMatchesWithDelta0 == 0)
        $numberOfMatchesWithDelta0 = 1;
    if($numberOfMatchesWithDelta10 == 0)
        $numberOfMatchesWithDelta10 = 1;
    if($numberOfMatchesWithDelta20 == 0)
        $numberOfMatchesWithDelta20 = 1;
    $averageDelta_0_10 = $averageDelta_0_10 / $numberOfMatchesWithDelta0;       //average Gold Delta 0-10
    $averageDelta_10_20 = $averageDelta_10_20 / $numberOfMatchesWithDelta10;    //average Gold Delta 10-20
    $averageDelta_20_30 = $averageDelta_20_30 / $numberOfMatchesWithDelta20;    //average Gold Delta 20-30

    //Extra calculations for the average game times
    $averageHours = floor(($matchData->gameDuration)/3600);                     //TOTAL HOURS GAME TIME 
    $averageMinutes = floor(($matchData->gameDuration)/60%60);                  //TOTAL MINUTES GAME TIME 
    $averageSecounds = floor(($matchData->gameDuration)%60);                    //TOTAL SECONDS GAME TIME



    //PRINT VALUES
    //Basic Information
    print " \"$summonerName\" played $numberOfScrimsInJUNGLE games in the Jungle Lane. <br> ";
    print " - - - - - - - - - - - - - - - - - <br>";

    //Winrate
    print "Winrate : $winRate% <br> <br>";

    //KDA
    print "KDA : $KDA <br> <br>";

    //Gold Deltas
    print "Gold Deltas: <br> ";
    print "0mins - 10mins : $averageDelta_0_10 <br> 10mins - 20mins : $averageDelta_10_20 <br> 20mins - 30mins : $averageDelta_20_30 <br> <br> "; 

    //Average Gold Earned
    print "Average Gold Earned : $averageGoldEarned <br> <br>";

    //Average Game Time
    print "Average Game Time : $averageHours h : $averageMinutes m : $averageSecounds s <br> <br>";

    //Average First Blood
    print "Average First Blood : $averageFirstBlood <br> <br>";

    //Average Champion Level
    print "Average Champion Level : $averageChampionLevel <br> <br>";

    //Average Damage Delt To Champions
    print "Average Damage Delt To Champions : $averageDamageDeltToChampions <br> <br>";

    //Average Vision Score
    print "Average Vision Score : $averageVisionScore <br> <br>";

    //Average Wards Bought
    print "Average Wards Bought : $averageVisionWardsBought <br> <br>";

    //Average Wards Killed
    print "Average Wards Killed : $averageWardsKilled <br> <br>";

    //Average Wards placed
    print "Average Wards placed : $averageWardsPlaced <br> <br>";


 }
 

?>
