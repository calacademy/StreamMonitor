**Note:** This repo also contains the Farallones cam control backend code (ported from legacy)

Current status…

https://utility.calacademy.org/webcams/monitor/?view=1

Run a full check manually…

https://utility.calacademy.org/webcams/monitor/youtube/

## Deploy
```sh
scp -r /PATH/webcams/ ec2-user@utility.calacademy.org:/var/www/html
```

## Cron
```sh
# Send an alert if live streams are stale
0 * * * * /usr/bin/php /var/www/html/webcams/monitor/alert.php > /dev/null 2>&1

# Check YouTube live stream and send pulse to database
*/15 * * * * /usr/bin/php /var/www/html/webcams/monitor/youtube/index.php > /dev/null 2>&1
```
