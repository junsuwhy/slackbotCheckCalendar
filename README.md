 用法：

 1. 下載
 2. 把 google-api 放在此目錄下（ google-api/src 裡面要有程式碼）
 3. 註冊 google 的 app ，取得 app Name 和 develop Key
 4. 取得 slack app 的 token 
 5. 找到 calendar 的 id ，長 xxxxxxxxxxxxxxxxxxxxx@group.calendar.google.com
 6. 用 bash 執行 php checkCalendar.php $appName $devKey $calID $domain $token $channel
 （其中 $channel 不用前面的 #）
 7. 也可以寫在 cron ，例如 0 21 * * * php checkCalendar.php ..... 就是每晚 9 點執行一次