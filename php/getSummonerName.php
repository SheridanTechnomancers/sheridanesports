<?php
	//  Include all required files
	require_once __DIR__  . "/WhatComposerGave/vendor/autoload.php";
	use RiotAPI\LeagueAPI\LeagueAPI;
	use RiotAPI\LeagueAPI\Definitions\Region;
	
	$api = new LeagueAPI([
	//  Your API key, you can get one at https://developer.riotgames.com/
	LeagueAPI::SET_KEY    => 'RGAPI-fb607fcb-d955-42c2-9bcf-a7abab386bf1',
	//  Target region (you can change it during lifetime of the library instance)
	LeagueAPI::SET_REGION => Region::EUROPE_EAST,
]);

$summoner = $api->getSummonerByName('I am TheKronnY');

echo $summoner->id;             //  KnNZNuEVZ5rZry3I...
echo $summoner->puuid;          //  rNmb6Rq8CQUqOHzM...
echo $summoner->name;           //  I am TheKronnY
echo $summoner->summonerLevel;  //  69

print_r($summoner->getData());  //  Or array of all the data


	
?>


