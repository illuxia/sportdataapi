<?php

require_once ('Database.php');

class SportData {

    private $serviceUrl = 'https://app.sportdataapi.com/api/v1/soccer';
    private $apiKey = 'b3a6d1f0-1616-11eb-905c-79b6a3bf93ef';
    public $database;


    public function __construct()
    {
        $this->database = new Database();
    }

    private function getData(string $endpoint)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $endpoint);

        $response = curl_exec($ch);
        curl_close($ch);

//        print_r(json_decode($response));
        return json_decode($response);
    }

    private function getLeagues()
    {
        $url = $this->serviceUrl . '/leagues?apikey=' . $this->apiKey . '&subscribed=true';
        return $this->getData($url);
    }

    private function getCountries()
    {
        $url = $this->serviceUrl . '/countries?apikey=' . $this->apiKey;
        return $this->getData($url);
    }

    private function getSeasonsByLeagueId($league_id)
    {
        $url = $this->serviceUrl . '/seasons?apikey=' . $this->apiKey . '&league_id=' . $league_id;
        return $this->getData($url);
    }

    private function getTeamsByCountryId($country_id)
    {
        $url = $this->serviceUrl . '/teams?apikey=' . $this->apiKey . '&country_id=' . $country_id;
        return $this->getData($url);
    }

    private function getMatchesBySeasonId($season_id)
    {
        $url = $this->serviceUrl . '/matches?apikey=' . $this->apiKey . '&season_id=' . $season_id;
        return $this->getData($url);
    }

    private function getRoundsBySeasonId($season_id)
    {
        $url = $this->serviceUrl . '/rounds?apikey=' . $this->apiKey . '&season_id=' . $season_id;
        return $this->getData($url);
    }

    private function getStandingsBySeasonId($season_id)
    {
        $url = $this->serviceUrl . '/standings?apikey=' . $this->apiKey . '&season_id=' . $season_id;
        return $this->getData($url);
    }

    private function getBookmakers()
    {
        $url = $this->serviceUrl . '/bookmakers?apikey=' . $this->apiKey;
        return $this->getData($url);
    }

    private function getOddsByMatchId($match_id)
    {
        $url = $this->serviceUrl . '/odds/'. $match_id .'?apikey=' . $this->apiKey . '&type=prematch';
        return $this->getData($url);
    }

    public function updateLeagues()
    {
        $query = $this->database->pdo->prepare("UPDATE league SET is_subscribed = 0");
        $query->execute();

        $leagues = $this->getLeagues();
        sleep(13);

        foreach ($leagues->data as $obj)
        {
            $query = $this->database->pdo->prepare("INSERT INTO league(league_id, country_id, name, is_subscribed, creation_date)
                                                          VALUES(:league_id, :country_id, :name, 1, now())
                                                          ON DUPLICATE KEY UPDATE
                                                          country_id = :country_id,
                                                          name = :name,
                                                          is_subscribed = 1,
                                                          update_date = now()");
            $query->bindValue(':league_id', $obj->league_id);
            $query->bindValue(':country_id', $obj->country_id);
            $query->bindValue(':name', $obj->name);

            $query->execute();
        }
        echo('Leagues updated <br>');
    }

    public function updateCountries()
    {
        $countries = $this->getCountries();
        sleep(13);

        foreach ($countries->data as $obj)
        {
            $query = $this->database->pdo->prepare("INSERT INTO country(country_id, name, creation_date)
                                                          VALUES(:country_id,:name, now())
                                                          ON DUPLICATE KEY UPDATE
                                                          name = :name,
                                                          update_date = now()");
            $query->bindValue(':country_id', $obj->country_id);
            $query->bindValue(':name', $obj->name);

            $query->execute();
        }
        echo('Countries updated <br>');
    }

    public function updateSeasons()
    {
        $query = $this->database->pdo->prepare("SELECT league_id FROM league WHERE is_subscribed=1");
        $query->execute();
        $leagues = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($leagues as $league)
        {
            $seasons = $this->getSeasonsByLeagueId($league->league_id);
            sleep(13);

            foreach ($seasons->data as $obj)
            {
                $query = $this->database->pdo->prepare("INSERT INTO season(season_id, name, is_current, country_id, league_id, start_date, end_date, creation_date)
                                                              VALUES(:season_id, :name, :is_current, :country_id, :league_id, :start_date, :end_date, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              name = :name,
                                                              is_current = :is_current,
                                                              country_id = :country_id,
                                                              league_id = :league_id,
                                                              start_date = :start_date,
                                                              end_date = :end_date,
                                                              update_date = now()");
                $query->bindValue(':season_id', $obj->season_id);
                $query->bindValue(':name', $obj->name);
                $query->bindValue(':is_current', $obj->is_current);
                $query->bindValue(':country_id', $obj->country_id);
                $query->bindValue(':league_id', $obj->league_id);
                $query->bindValue(':start_date', $obj->start_date);
                $query->bindValue(':end_date', $obj->end_date);

                $query->execute();

            }
        }
        echo('Seasons updated <br>');
    }

    public function updateTeams()
    {
        $query = $this->database->pdo->prepare("SELECT country_id FROM league WHERE is_subscribed=1");
        $query->execute();
        $countries = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($countries as $country)
        {
            $teams = $this->getTeamsByCountryId($country->country_id);
            sleep(13);

            foreach ($teams->data as $obj)
            {
                $query = $this->database->pdo->prepare("INSERT INTO team(team_id, name, logo, country_id, creation_date)
                                                              VALUES(:team_id, :name, :logo, :country_id, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              name = :name,
                                                              logo = :logo,
                                                              country_id = :country_id,
                                                              update_date = now()");
                $query->bindValue(':team_id', $obj->team_id);
                $query->bindValue(':name', $obj->name);
                $query->bindValue(':logo', $obj->logo);
                $query->bindValue(':country_id', $obj->country->country_id);

                $query->execute();

            }
        }
        echo('Teams updated <br>');
    }

    public function updateMatches()
    {
        $query = $this->database->pdo->prepare("SELECT season_id 
                                                        FROM season s, league l
                                                        WHERE s.league_id = l.league_id
                                                        AND s.is_current=1
                                                        AND l.is_subscribed = 1");
        $query->execute();
        $seasons = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($seasons as $season)
        {
            $matches = $this->getMatchesBySeasonId($season->season_id);
//            sleep(13);

            foreach ($matches->data as $match)
            {
                $query = $this->database->pdo->prepare("INSERT INTO fixture(match_id, status_code, match_start, league_id, season_id, round_id,
                                home_team_id, away_team_id, home_score, away_score, ht_score, ft_score, et_score, ps_score, creation_date)
                                                              VALUES(:match_id, :status_code, :match_start, :league_id, :season_id, :round_id,
                                :home_team_id, :away_team_id, :home_score, :away_score, :ht_score, :ft_score, :et_score, :ps_score, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              status_code = :status_code,
                                                              match_start = :match_start,
                                                              league_id = :league_id,
                                                              season_id = :season_id,
                                                              round_id = :round_id,
                                                              home_team_id = :home_team_id,
                                                              away_team_id = :away_team_id,
                                                              home_score = :home_score,
                                                              away_score = :away_score,
                                                              ht_score = :ht_score,
                                                              ft_score = :ft_score,
                                                              et_score = :et_score,
                                                              ps_score = :ps_score,
                                                              update_date = now()");


                $query->bindValue(':match_id', $match->match_id);
                $query->bindValue(':status_code', $match->status_code);
                $query->bindValue(':match_start', $match->match_start);
                $query->bindValue(':league_id', $match->league_id);
                $query->bindValue(':season_id', $match->season_id);
                $query->bindValue(':round_id', $match->round->round_id);
                $query->bindValue(':home_team_id', $match->home_team->team_id);
                $query->bindValue(':away_team_id', $match->away_team->team_id);
                $query->bindValue(':home_score', $match->stats->home_score);
                $query->bindValue(':away_score', $match->stats->away_score);
                $query->bindValue(':ht_score', $match->stats->ht_score);
                $query->bindValue(':ft_score', $match->stats->ft_score);
                $query->bindValue(':et_score', $match->stats->et_score);
                $query->bindValue(':ps_score', $match->stats->ps_score);

                $query->execute();
            }
        }
        echo('Matches updated <br>');
    }

    public function updateRounds()
    {
        $query = $this->database->pdo->prepare("SELECT season_id 
                                                    FROM season s, league l
                                                    WHERE s.league_id = l.league_id
                                                    AND s.is_current=1
                                                    AND l.is_subscribed = 1");
        $query->execute();
        $seasons = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($seasons as $season)
        {
            $rounds = $this->getRoundsBySeasonId($season->season_id);
            sleep(13);

            foreach ($rounds->data as $round)
            {
                $query = $this->database->pdo->prepare("INSERT INTO round(round_id, name, is_current, season_id, league_id, creation_date)
                                                              VALUES(:round_id, :name, :is_current, :season_id, :league_id, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              name = :name,
                                                              is_current = :is_current,
                                                              season_id = :season_id,
                                                              league_id = :league_id,
                                                              update_date = now()");

                $query->bindValue(':round_id', $round->round_id);
                $query->bindValue(':name', $round->name);
                $query->bindValue(':is_current', $round->is_current);
                $query->bindValue(':season_id', $round->season_id);
                $query->bindValue(':league_id', $round->league_id);

                $query->execute();
            }
        }
        echo('Rounds updated <br>');
    }

    public function updateStandings()
    {
        $query = $this->database->pdo->prepare("SELECT season_id 
                                                    FROM season s, league l
                                                    WHERE s.league_id = l.league_id
                                                    AND s.is_current=1
                                                    AND l.is_subscribed = 1");
        $query->execute();
        $seasons = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($seasons as $season)
        {
            $standings = $this->getStandingsBySeasonId($season->season_id);
            sleep(13);

            foreach ($standings->data->standings as $standing)
            {
                $query = $this->database->pdo->prepare("INSERT INTO standing(team_id, points, status, result, games_played, won, draw, lost, goals_scored, goals_against, 
                                                                                home_games_played, home_won, home_draw, home_lost, home_goals_scored, home_goals_against,
                                                                                away_games_played, away_won, away_draw, away_lost, away_goals_scored, away_goals_against, season_id, creation_date)
                                                              VALUES(:team_id, :points, :status, :result, :games_played, :won, :draw, :lost, :goals_scored, :goals_against, 
                                                                                :home_games_played, :home_won, :home_draw, :home_lost, :home_goals_scored, :home_goals_against,
                                                                                :away_games_played, :away_won, :away_draw, :away_lost, :away_goals_scored, :away_goals_against, :season_id, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              points = :points,
                                                              status = :status,
                                                              result = :result,
                                                              games_played = :games_played,
                                                              won = :won,
                                                              draw = :draw,
                                                              lost = :lost,
                                                              goals_scored = :goals_scored,
                                                              goals_against = :goals_against,
                                                              home_games_played = :home_games_played,
                                                              home_won = :home_won,
                                                              home_draw = :home_draw,
                                                              home_lost = :home_lost,
                                                              home_goals_scored = :home_goals_scored,
                                                              home_goals_against = :home_goals_against,
                                                              away_games_played = :away_games_played,
                                                              away_won = :away_won,
                                                              away_draw = :away_draw,
                                                              away_lost = :away_lost,
                                                              away_goals_scored = :away_goals_scored,
                                                              away_goals_against = :away_goals_against,
                                                              season_id = :season_id,
                                                              update_date = now()");

                $query->bindValue(':team_id', $standing->team_id);
                $query->bindValue(':points', $standing->points);
                $query->bindValue(':status', $standing->status);
                $query->bindValue(':result', $standing->result);
                $query->bindValue(':games_played', $standing->overall->games_played);
                $query->bindValue(':won', $standing->overall->won);
                $query->bindValue(':draw', $standing->overall->draw);
                $query->bindValue(':lost', $standing->overall->lost);
                $query->bindValue(':goals_scored', $standing->overall->goals_scored);
                $query->bindValue(':goals_against', $standing->overall->goals_against);
                $query->bindValue(':home_games_played', $standing->home->games_played);
                $query->bindValue(':home_won', $standing->home->won);
                $query->bindValue(':home_draw', $standing->home->draw);
                $query->bindValue(':home_lost', $standing->home->lost);
                $query->bindValue(':home_goals_scored', $standing->home->goals_scored);
                $query->bindValue(':home_goals_against', $standing->home->goals_against);
                $query->bindValue(':away_games_played', $standing->away->games_played);
                $query->bindValue(':away_won', $standing->away->won);
                $query->bindValue(':away_draw', $standing->away->draw);
                $query->bindValue(':away_lost', $standing->away->lost);
                $query->bindValue(':away_goals_scored', $standing->away->goals_scored);
                $query->bindValue(':away_goals_against', $standing->away->goals_against);
                $query->bindValue(':season_id', $standings->data->season_id);

                $query->execute();
            }
        }
        echo('Standings updated <br>');
    }

    public function updateBookmakers()
    {
        $bookmakers = $this->getBookmakers();
        sleep(13);

        foreach ($bookmakers->data as $bookmaker)
        {
            $query = $this->database->pdo->prepare("INSERT INTO bookmaker(bookmaker_id, name, logo, creation_date)
                                                              VALUES(:bookmaker_id, :name, :logo, now())
                                                              ON DUPLICATE KEY UPDATE
                                                              name = :name,
                                                              logo = :logo,
                                                              update_date = now()");

            $query->bindValue(':bookmaker_id', $bookmaker->bookmaker_id);
            $query->bindValue(':name', $bookmaker->name);
            $query->bindValue(':logo', $bookmaker->img);

            $query->execute();
        }
        echo('Bookmakers updated <br>');
    }

    public function updateOdds()
    {
        // TODO update missing odds
        /*
         * Spain - LaLiga - 2021-11-25
         * Spain - Laliga2 - 2021-11-25
         * France - Ligue 1 - 2021-11-25
         * France - Ligue 2 - 2021-11-25
         * Italy - Serie A - 2021-11-25
         * Italy - Serie B - 2021-11-25
         * Germany - Bundesliga - 2021-11-25
         * Germany - 2 Bundesliga - 2021-11-25
         * Portugal - Primeira Liga - 2021-11-25
         * Portugal - Segunda Liga - 2021-11-25
         * Austria - Tipico Bundesliga - 2021-11-25
         * Austria - 2. Liga - 2021-11-25
         * Belgium - Jupiler League - 2021-11-25
         * Belgium - Proximus League - 2021-11-25
         * Netherlands - Eredivisie - 2021-11-25
         * Netherlands - Eerste Divisie - 2021-11-25
         * Norway - Eliteserien - 2021-11-25
         * Norway - OBOS-ligaen - 2021-11-25
         * Denmark - Superliga - 2021-11-25
         * Denmark - 1st Division - 2021-11-25
         * Sweden - Allsvenskan - 2021-11-25
         * Sweden - Superettan - 2021-11-25
         * Switzerland - Super League - 2021-11-25
         * Switzerland - Challenge League - 2021-11-25
         * Finland - Veikkausliiga - 2021-11-25
         * Turkey - Super Lig - 2021-11-25
         * Bulgaria - Parva Liga - 2021-11-25
         * Greece - Super League - 2021-11-25
         * Croatia - 1. HNL - 2021-11-25
         * Hungary - OTP Bank Liga - 2021-11-25
         * Czech Republic - 1. Liga - 2021-11-25
         * Poland - Ekstraklasa - 2021-11-25
         * Romania - Liga 1 - 2021-11-25
         * Russia - Premier League - 2021-11-25
         * Slovakia - Fortuna liga - 2021-11-25
         * Slovenia - Prva Liga - 2021-11-25
         * Serbia - Super Liga - 2021-11-25
         * Ukraine - Premier League - 2021-11-25
         * Cyprus - First Division - 2021-11-25
         * Israel - Ligat ha'Al - 2021-11-25
         *
        */

        $query = $this->database->pdo->prepare("SELECT DISTINCT(fx.match_id)
                                                    FROM fixture fx, league l, round r
                                                    WHERE fx.league_id = l.league_id
                                                    AND fx.round_id = r.round_id
                                                    AND r.name NOT IN (1,2,3,4,5)
                                                    AND l.is_subscribed = 1
                                                    AND DATE(fx.match_start) <= '2021-12-14'
                                                    AND fx.match_id not in (SELECT DISTINCT(o.fixture_id) FROM odd o)
                                                    ORDER BY fx.match_start;");


//        $query = $this->database->pdo->prepare("SELECT fx.match_id
//                                                    FROM fixture fx, league l
//                                                    WHERE fx.league_id = l.league_id
//                                                    AND fx.status_code = 0
//                                                    AND l.is_subscribed = 1
//                                                    AND fx.match_start BETWEEN NOW() AND (NOW() + INTERVAL 3 DAY)
//                                                    ORDER BY fx.match_start;");
        $query->execute();
        $matches = $query->fetchAll(\PDO::FETCH_OBJ);

        foreach ($matches as $match)
        {
            $odds = $this->getOddsByMatchId($match->match_id);
//            sleep(13);

            foreach ($odds->data as $key => $odd)
            {
                foreach ($odd->bookmakers as $bookmaker)
                {
//                    print_r($bookmaker); return;
                    $query = $this->database->pdo->prepare("INSERT INTO odd(fixture_id, bookmaker_id, home, draw, away, handicap, goals_over, goals_under, odd_type, creation_date)
                                                          VALUES(:fixture_id, :bookmaker_id, :home, :draw, :away, :handicap, :goals_over, :goals_under, :odd_type, now())
                                                          ON DUPLICATE KEY UPDATE
                                                          bookmaker_id = :bookmaker_id,
                                                          home = :home,
                                                          draw = :draw,
                                                          away = :away,
                                                          handicap = :handicap,
                                                          goals_over = :goals_over,
                                                          goals_under = :goals_under,
                                                          odd_type = :odd_type,
                                                          update_date = now()");

                    $query->bindValue(':fixture_id', $match->match_id);
                    $query->bindValue(':bookmaker_id', $bookmaker->bookmaker_id);
                    $query->bindValue(':home', $bookmaker->odds_data->home);
                    $query->bindValue(':draw', $bookmaker->odds_data->draw);
                    $query->bindValue(':away', $bookmaker->odds_data->away);
                    $query->bindValue(':handicap', $bookmaker->odds_data->handicap);
                    $query->bindValue(':goals_over', $bookmaker->odds_data->over);
                    $query->bindValue(':goals_under', $bookmaker->odds_data->under);
                    $query->bindValue(':odd_type', $key);

                    $query->execute();
                }
            }
        }
        echo('Odds updated <br>');
    }



}