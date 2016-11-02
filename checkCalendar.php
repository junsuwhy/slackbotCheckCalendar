<?php

// load google data api
// references: 
// http://stackoverflow.com/questions/14086833/google-calendar-api-for-php-simple-read-only-request-to-get-calendar-events
// https://developers.google.com/google-apps/calendar/v3/reference/events/list
// https://console.developers.google.com/project/559624307439/apiui/credential?authuser=0#

date_default_timezone_set('Asia/Taipei');
set_include_path('/var/www/robot/google-api/src');

if(empty($argv[1])){
  print("Please use 'php robot2.php \$appName \$devKey \$calID \$domain \$token \$channel'\n");
  return ;
}else{
  list($file, $appName, $devKey, $calID, $DOMAIN, $TOKEN, $CHANNEL) = $argv;

  $eventlist = get_event($appName, $devKey, $calID);
  $text = getStringFromEventList($eventlist);
  sendMessageToSlack($DOMAIN, $TOKEN, $CHANNEL, $text);
}

function get_event($appName, $devKey, $calID){
  require_once 'Google/autoload.php';

  $client = new Google_Client();
  $client->setApplicationName($appName);
  // need server key
  $client->setDeveloperKey($devKey);

  $service = new Google_Service_Calendar($client);
  $events = $service->events->listEvents($calID,
    array(
      'timeMin' => date('Y-m-d', strtotime('+1 day')).'T00:00:00+08:00',
      'timeMax' => date('Y-m-d', strtotime('+2 day')).'T00:00:00+08:00',
      // 'timeZone' => 'UTF+8',
      ));
  $eventlist = $events->getItems();

  usort($eventlist, "cmp");

  return $eventlist;
}

function cmp($a, $b){
  $timea = strtotime($a['start']['dateTime']);
  $timeb = strtotime($b['start']['dateTime']);
  return strcmp($timeb, $timea);
}
  

function getStringFromEventList($eventlist){
  $text = "";

  $rand = rand(0,5);
  switch ($rand) {
    case 0:
      $text.='哈囉!!';  
      break;
    case 1:
      $text.='Hi~';  
      break;
    case 2:
      $text.='哈哈~';  
      break;
    case 3:
      $text.='耶嘿!!';  
      break;
    case 4:
      $text.='科科科 ';  
      break;
    default:
      $text.='喵~';  
      break;
  }

  
  $count = count($eventlist);

  if($count==0){
    $text .= "明天沒有活動，爽啦!!!!!\n";
  }else{
    $text .= "明天有 $count 個活動喔!!\n";
    for ($i=$count-1; $i >= 0 ; $i--) { 
      $event = $eventlist[$i];
      $text.=date('A h:i',strtotime($event['start']['dateTime'])).' '.$event['summary']."\n";
    }

    // foreach ($eventlist as $event) {
    //   $text.=date('A h:i',strtotime($event['start']['dateTime'])).' 開始，'.$event['summary']."\n";
    // }
  }
  

  $rand = rand(0,5);
  switch ($rand) {
    case 0:
      $text.='希望明天是個好日子。';  
      break;
    case 1:
      $text.='明天也要加把勁啦!!!';  
      break;
    case 2:
      $text.='明天好好加油啊!!';  
      break;
    case 3:
      $text.='是個適合到處亂跑的一天';  
      break;
    case 4:
      $text.='是個適合宅在工作室的一天!!';  
      break;
    default:
      $text.='明天見啦~';  
      break;
  }

  return $text;
}
  

function sendMessageToSlack($DOMAIN, $TOKEN, $CHANNEL, $text){
  //reference : https://github.com/madeinnordeste/slackbot-example/blob/master/bot.php
  //More information on slack.com integrations
  
  // $DOMAIN = 'doyouaflavor';
  // $TOKEN = 'ns42CNGgU21zsp3yEeqWY3h6';
  // $CHANNEL = 'dyaf_tomorrow';
  // $url = 'https://'.$DOMAIN.'.slack.com/services/hooks/slackbot?token='.$TOKEN.'&channel=%23'.$CHANNEL; 
  $url = 'https://'.$DOMAIN.'.slack.com/services/hooks/slackbot?token='.$TOKEN.'&channel='.$CHANNEL;
  //send to slack
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $text);
  $result = curl_exec($ch);
  curl_close($ch);
}


  // last : 
  // use cron job to execute this
  // http://content.edu.tw/primary/info_edu/cy_sa/LinuxY/cmd/crontab.htm
?>

