<?php

// load google data api
// references: 
// http://stackoverflow.com/questions/14086833/google-calendar-api-for-php-simple-read-only-request-to-get-calendar-events
// https://developers.google.com/google-apps/calendar/v3/reference/events/list
// https://console.developers.google.com/project/559624307439/apiui/credential?authuser=0#

date_default_timezone_set('Asia/Taipei');
set_include_path('/var/www/robot/google-api/src');

if(empty($argv[1])){
  print("Please use 'php checkCalendarSendbyChatAPI.php \$appName \$devKey \$calID \$WeekOrDay \$token \$channel'\n");
  return ;
}else{
  list($file, $appName, $devKey, $calID, $WeekOrDay, $TOKEN, $CHANNEL) = $argv;
  $eventlist = get_event($appName, $devKey, $calID, $WeekOrDay);
  $text = getStringFromEventList($eventlist, $WeekOrDay);
  sendMessageToSlack($DOMAIN, $TOKEN, $CHANNEL, $text);
}

function get_event($appName, $devKey, $calID, $WeekOrDay){
  require_once 'Google/autoload.php';

  $client = new Google_Client();
  $client->setApplicationName($appName);
  // need server key
  $client->setDeveloperKey($devKey);

  switch ($WeekOrDay) {
    case 'week':
      $timeLimit = '+8 day';
      break;
    case 'day':
    default:
      $timeLimit = '+2 day';
      break;
  }
  

  $service = new Google_Service_Calendar($client);
  $events = $service->events->listEvents($calID,
    array(
      'timeMin' => date('Y-m-d', strtotime('+1 day')).'T00:00:00+08:00',
      'timeMax' => date('Y-m-d', strtotime($timeLimit)).'T00:00:00+08:00',
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
  

function getStringFromEventList($eventlist, $WeekOrDay){
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

  switch ($WeekOrDay) {
    case 'week':
      $day = '下週';
      break;
    case 'day':
    default:
      $day = '明天';
      break;
  }

  if($count==0){
    $text .= "${day}沒有活動，爽啦!!!!!\n";
  }else{
    $text .= "${day}有 $count 個活動喔!!\n";
    for ($i=$count-1; $i >= 0 ; $i--) { 
      $event = $eventlist[$i];
      $time = strtotime($event['start']['dateTime']);
      $date = date('m/d',$time);
      $weekday = weekday(date('D',$time));
      $text.= "- ${date}（${weekday}）".' '.$event['summary']."\n";
    }

    // foreach ($eventlist as $event) {
    //   $text.=date('A h:i',strtotime($event['start']['dateTime'])).' 開始，'.$event['summary']."\n";
    // }
  }
  

  $rand = rand(0,5);
  switch ($rand) {
    case 0:
      $text.="希望${day}是好日子。";  
      break;
    case 1:
      $text.="${day}也要加把勁啦!!!";  
      break;
    case 2:
      $text.="好好加油啊!!";  
      break;
    case 3:
      $text.="${day}最適合到處亂跑啦!!";  
      break;
    case 4:
      $text.="${day}最適合宅在家惹!!";  
      break;
    default:
      $text.="明天見啦~";  
      break;
  }

  return $text;
}

function weekday($day){
  switch ($day) {
    case 'Sun':
      return '日';
      break;
    case 'Mon':
      return '一';
    break;
    case 'Tue':
      return '二';
    break;
    case 'Wed':
      return '三';
    break;
    case 'Thu':
      return '四';
    break;
    case 'Fri':
      return '五';
    break;
    case 'Sat':
      return '六';
    break;
    default:
      # code...
      break;
  }
}
  

function sendMessageToSlack($DOMAIN, $TOKEN, $CHANNEL, $text){
  //reference : https://github.com/madeinnordeste/slackbot-example/blob/master/bot.php
  //More information on slack.com integrations
  
  // $DOMAIN = 'doyouaflavor';
  // $TOKEN = 'ns42CNGgU21zsp3yEeqWY3h6';
  // $CHANNEL = 'dyaf_tomorrow';
  // $url = 'https://'.$DOMAIN.'.slack.com/services/hooks/slackbot?token='.$TOKEN.'&channel=%23'.$CHANNEL; 
  // $url = 'https://'.$DOMAIN.'.slack.com/services/hooks/slackbot?token='.$TOKEN.'&channel='.$CHANNEL;
  $url = 'https://slack.com/api/chat.postMessage';
  $data = array(
    'token' => $TOKEN,
    'channel' => $CHANNEL,
    'text' => $text,
    'username' => 'Slack機器人（阿機）',
    'icon_url' => 'https://cdn3.iconfinder.com/data/icons/animal-faces/72/33-128.png',
    );
  //send to slack
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL, $url);
  // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
  // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  // curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
  // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
  $result = curl_exec($ch);
  curl_close($ch);
}


  // last : 
  // use cron job to execute this
  // http://content.edu.tw/primary/info_edu/cy_sa/LinuxY/cmd/crontab.htm
?>

