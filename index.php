<?php

require_once ('model/SportData.php');

//$sportData = new SportData();
//$sportData->updateLeagues(); //yM
//$sportData->updateCountries(); //y1
//$sportData->updateSeasons(); //y1 only for subscribed leagues
//$sportData->updateTeams(); //y1 only for subscribed leagues
//$sportData->updateMatches(); //yM only for subscribed leagues
//$sportData->updateRounds(); //y1 only for subscribed leagues
//$sportData->updateStandings(); //yM only for subscribed leagues
//$sportData->updateBookmakers(); //y1
//$sportData->updateOdds(); //yM only for subscribed leagues



//--------------ONE TIME UPDATE-----------------//
//$sportData = new SportData();
//$sportData->updateCountries();
//$sportData->updateBookmakers();


//--------------INITIAL UPDATE FOR NEW LEAGUE (50 api calls on avg)-----------------//
//$sportData = new SportData();
//$sportData->updateLeagues();
//$sportData->updateSeasons();
//$sportData->updateTeams();
//$sportData->updateMatches();
//$sportData->updateRounds();
////$sportData->updateStandings(); // inconsistent data
//$sportData->updateOdds();



//--------------REGULAR UPDATE FOR EXISTING LEAGUE AND CURRENT SEASON (25 api calls on avg)-----------------//
$sportData = new SportData();
$sportData->updateMatches();
$sportData->updateOdds();



//--------------UPDATE ODDS----------------//
//$sportData = new SportData();
//$sportData->updateOdds();