# Lottery numbers scraper
Script to scrap lottery numbers from Lottomatica Italia website ( https://www.lottomaticaitalia.it ) and inserts data into MySQL database.

------------------------------------
Configuration
------------------------------------
1.) Open file lottery.php using a text editor and modify the following lines to use your

MySQL hosting configuration:
$mysqlserverhost = "MYSQL_HOST";
$database_name = "MYSQL_DATABASE_NAME";
$username_mysql = "MYSQL_USERNAME";
$password_mysql = "MYSQL_PASSWORD";

2.) Upload file “lottery.php” to your hosting account.

3.) Create a conjob entry in your server so that scripts is executed every 5 minutes and stores new data in database. The exact command you must use in crontab is:

*/5 * * * * /usr/bin/wget -q https://www.yourwebsite.com/lottery.php > /dev/null 2>&1

Done.
