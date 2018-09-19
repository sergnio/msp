<?php
require_once 'mypicks_startsession.php';

/*
:mode=php:

   file: scoresautomation.php
   date: jul-2016
 author: hfs
   desc: Interface to ProFootballAPI.com account and data, and cron (time/date based
      updating)
      https://profootballapi.com/
*/

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_db.php';
require_once 'mypicks_phpgeneral.php';

ini_set("allow_url_fopen", 1);
validateUser('admin');


do_header('MySuperPicks.com - Scores Automation Control'); 


formatSessionMessage("Not implemented.  This is a demonstration file.  There are no mysupuerpicks operations.", 'info', $msg);
setSessionMessage($msg);
echoSessionMessage();
$url = 'https://profootballapi.com/schedule';

$api_key = 'tbG1wDnYOIWa4XdKzc5qk6eM7FSPmLpH';

$query_string = 'api_key=' . $api_key;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = '';
$result = curl_exec($ch);

curl_close($ch);

echo "
   <br />
   <h4>This is a raw dump, bottom of page, from <a href='https://profootballapi.com/schedule'>https://profootballapi.com/schedule</a>  A $10/m service.</h4>
   <h5>Code looks like this:</h5>
      <pre>
      <code>

   url = 'https://profootballapi.com/schedule';
   
   api_key = 'tbG1wDnYOIWa4XdKzc5qk6eM7FSPmLpH';
   
   query_string = 'api_key=' . api_key;
   
   ch = curl_init();
   
   curl_setopt(ch, CURLOPT_URL, url);
   curl_setopt(ch, CURLOPT_POSTFIELDS, query_string);
   curl_setopt(ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt(ch, CURLOPT_SSL_VERIFYPEER, false);
   
   result = curl_exec(ch);
   curl_close(ch);
   
   </pre></code>";
   
echo "
   <br />
   <h4>This is a raw dump, bottom of page, from <a href='http://www.nfl.com/liveupdate/scores/scores.json'>http://www.nfl.com/liveupdate/scores/scores.json</a>.  It's free but will it work next season?</h4>
   <h5>Code looks like this:</h5>
<pre>
<code>

if ((json = file_get_contents('http://www.nfl.com/liveupdate/scores/scores.json'))===false){
   exit;
} else {
   array = json_decode(json);
   foreach(array as key=>value) {
      date = substr(key, 0, -2);
      home = value->home->abbr;
      away = value->away->abbr;
      homescore = value->home->score->T;
      awayscore = value->away->score->T;
      conn = db_connect();
      result = conn->query('UPDATE schedules SET homescore='homescore',awayscore='awayscore' WHERE DATE_FORMAT(gametime, '%Y%m%d')='date' AND home='home' AND away='away'');
   }
}

</code>
</pre>";

echo "
<br /><br /> To view the current nfl data, link <a href='http://www.nfl.com/liveupdate/scores/scores.json'>nfl data</a>
<br /><br /> Here's the dump from the paid service: (trial for 10 days then this won't work ... should be good thru july.
<br /><br />
$result";

do_footer('clean');
?>