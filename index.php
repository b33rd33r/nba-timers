<?php

  require __DIR__ . '/vendor/autoload.php';

  if (isset($_GET['team']))
    $team = trim(strip_tags(trim($_GET['team'])));

  $teams = array(
    'bucks' => 'Milwaukee Bucks',
    'mavericks' => 'Dallas Mavericks',
    'rockets' => 'Houston Rockets',
    'grizzlies' => 'Memphis Grizzlies',
    'pelicans' => 'New Orleans Pelicans',
    'spurs' => 'San Antonio Spurs',
    'nuggets' => 'Denver Nuggets',
    'timberwolves' => 'Minnesota Timberwolves',
    'thunder' => 'Oklahoma City Thunder',
    'blazers' => 'Portland Blazers',
    'jazz' => 'Utah Jazz',
    'warriors' => 'Golden State Warriors',
    'clippers' => 'Los Angeles Clippers',
    'lakers' => 'Los Angeles Lakers',
    'suns' => 'Phoenix Suns',
    'kings' => 'Sacramento Kings',
    'celtics' => 'Boston Celtics',
    'nets' => 'New Jersey Nets',
    'knicks' => 'New York Knicks',
    'sixers' => 'Philadelphia 76ers',
    'raptors' => 'Toronto Raptors',
    'bulls' => 'Chicago Bulls',
    'cavaliers' => 'Cleveland Cavaliers',
    'pistons' => 'Detroit Pistons',
    'pacers' => 'Indiana Pacers',
    'hawks' => 'Atlanta Hawks',
    'hornets' => 'Charlotte Hornets',
    'heat' => 'Miami Heat',
    'magic' => 'Orlando Magic',
    'wizards' => 'Washington Wizards'
  );

  echo build_start();
  if (empty($team)) {
    build_all_teams($teams);   
  } else {
    build_single_team($teams, $team);
  }
  echo build_end();


  function build_all_teams($teams) {
    foreach ($teams as $team => $team_value) {
      build_single_team($teams, $team);
    }
  }

  function build_single_team($teams, $team) {
    $client = new Predis\Client();

    $cached = cache_is_valid($team, $client);

    if ($cached) {
      build_page($cached, $teams[$team], $team, TRUE);
    } else {
      handle_cache($team, $client);
      build_page($timestamp, $teams[$team], $team, FALSE);
    }
  }

  function build_page($timestamp, $team_name, $team_key, $cache_hit) {
    echo build_prefix($team_key);
    echo build_html($timestamp, $team_name, $team_key);
    echo build_suffix($cache_hit, $team_key);
  }

  function handle_cache($team, $client) {
    $json = get_nba_json($team);
    $new_date = get_new_team_schedule($json);
    $timestamp = $new_date->getTimestamp();
    update_cache($team, $timestamp, $client);
  }

  function get_nba_json($team) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
    $url = "http://data.nba.net/data/10s/prod/v1/2018/teams/$team/schedule.json";
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
  }

  function cache_is_valid($team, $client) {
    $team_time = $team . "_time";

    if (empty($client->get($team)))
      return FALSE;

    if (empty($client->get($team_time)))
      return FALSE;

    $now = new DateTime();
    $now_timestamp = $now->getTimestamp();

    if ($now_timestamp - intval($client->get($team_time)) > 3600)
      return FALSE;

    return $client->get($team);
  }

  function get_new_team_schedule($json) {
    $team_schedule = json_decode($json, TRUE);
    $last_index = $team_schedule['league']['lastStandardGamePlayedIndex'];
    $next_index = $last_index + 1;
    $next_game = $team_schedule['league']['standard'][$next_index];
    $next_start = $next_game["startTimeUTC"];
    $date1 = new DateTime($next_start);

    return $date1;
  }

  function update_cache($team, $date, $client) {
    $team_time = $team . "_time";
    $client->set($team, $date);
    $now = new DateTime();
    $now_timestamp = $now->getTimestamp();
    $client->set($team_time, $now_timestamp);
  }

  function build_html($timestamp, $team_name, $team_key) {
    $now = new DateTime();
    $now_timestamp = $now->getTimestamp();
    $diff_in_seconds = intval($timestamp) - $now_timestamp;

    $output = "<h2 class='p-4'>$team_name</h2>";
    $output .= "<div class='clock-$team_key'></div>";
    $output .= "<script type='text/javascript'>";
    $output .= "var clock = $('.clock-$team_key').FlipClock($diff_in_seconds, {";
    $output .= "clockFace: 'DailyCounter',";
    $output .= "countdown: true,";
    $output .= "showSeconds: true";
    $output .= "});";
    $output .= "</script>";

    return $output;
  }

  function build_start() {
    $output = "<!doctype html>";
    $output .= "<html lang='en'>";
    $output .= "<head>";
    $output .= '<!-- Global site tag (gtag.js) - Google Analytics -->';
    $output .= '<script async src="https://www.googletagmanager.com/gtag/js?id=UA-128082233-1"></script>';
    $output .= '<script>';
    $output .= 'window.dataLayer = window.dataLayer || [];';
    $output .= 'function gtag(){dataLayer.push(arguments);}';
    $output .= "gtag('js', new Date());";
    $output .= "gtag('config', 'UA-128082233-1');";
    $output .= "</script>";

    $output .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
    $output .= "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css'/>";
    $output .= "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/flipclock/0.7.8/flipclock.min.css'/>";
    $output .= "<link rel='stylesheet' href='style.css'/>";
    $output .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>";
    $output .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/flipclock/0.7.8/flipclock.min.js'></script>";
    $output .= "<title>THE BEST COUNTDOWNS IN THE NBA!</title>";
    $output .= "</head>";
    $output .= "<body style='background-color: #efefef;'>";

    return $output;

  }

  function build_prefix($team_key) {
    $output = "<div id='$team_key' class='container justify-content-center mt-4 p-3'>";
    $output .= "<div class='row'>";
    $output .= "<div class='col-sm-12'>";

    return $output;
  }

  function build_suffix($hit_cache, $team_key) {
    $output = '<div class="alert alert-primary" role="alert">';
    $output .= 'Go ahead and <a href="https://nba.mashup.gr/?team=' . $team_key . '" class="alert-link">bookmark the direct link!</a></div>';
    $output .= "<mark>";
    $output .= $hit_cache ? "Cache hit!" : "Request sent (no cache hit)!";
    $output .= "</mark>";
    $output .= "</div>"; // Col SM 12
    $output .= "</div>"; // Row
    $output .= "</div>"; // Container

    return $output;
  }

  function build_end() {
    $output = "</body>";
    $output .= "</html>";

    return $output;
  }
