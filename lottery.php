<?

// Configure your mysql database connection details:

$mysqlserverhost = "";
$database_name = "";
$username_mysql = "";
$password_mysql = "";

function connect_to_mysqli($mysqlserverhost, $username_mysql, $password_mysql, $database_name){
	$connect = mysqli_connect($mysqlserverhost, $username_mysql, $password_mysql, $database_name);
	if (!$connect) {
		  die("Connection failed mysql: " . mysqli_connect_error());
	}
	return $connect;	
}

function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	$headers = array(
		"Host: www.lottomaticaitalia.it", 
		"Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3", 
		"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",	
		"DNT: 1",
		"Connection: keep-alive",
		"Upgrade-Insecure-Requests: 1"
	);  
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_REFERER, 'https://www.lottomaticaitalia.it/it/prodotti/10-e-lotto/estrazioni-ogni-5');
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt($ch, CURLOPT_AUTOREFERER, true );
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_COOKIESESSION, true );
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


function scrap_numbers($url){
	$html = get_data($url);
	$html = preg_replace('~[\r\n]+~', '', $html);
	$html = str_replace("							","",$html);
	$html = str_replace("			","",$html);
	preg_match_all('/<th><div class="numeroEstrazione">(.*?)<\/tr>/', $html, $output_array);
	$mentions = array_unique($output_array[0]);
	$row = $mentions[count($mentions)-1];
	$html = str_replace("</div>"," ",$row);
	$row = strip_tags($html);
	return explode(" ",$row);
}

function nearest5mins($time) {
  $time = (round(strtotime($time) / 300)) * 300;
  return date('Y-m-d H:i:s', $time);
}

function is_new_registry($extraction, $connection){
	$checkdate = date('Y-m-d',time()+(60*60*2));
	$sql = "SELECT nr FROM datasss WHERE nr = '$extraction' AND date_of_event LIKE '$checkdate%'";
	$result = mysqli_query($connection,$sql); 
	if(mysqli_num_rows($result) > 0 ){
		return false;
	} else{
		return true;
	}	
}

function adding_to_database($number, $connection){
	$customdate = nearest5mins(date('Y-m-d H:i:s',time()+(60*60*2)));
	$sql = "INSERT INTO datasss (nr, date_of_event, l1, l2, l3, l4, l5, l6, l7, l8, l9, l10, l11, l12, l13, l14, l15, l16, l17, l18, l19, l20, l21, l22) VALUES
	('$number[0]', '$customdate', '$number[3]', '$number[4]', '$number[5]', '$number[6]', '$number[7]', '$number[8]', '$number[9]', '$number[10]', '$number[11]', '$number[12]', '$number[13]', '$number[14]', '$number[15]', '$number[16]', '$number[17]', '$number[18]', '$number[19]', '$number[20]', '$number[21]', '$number[22]', '$number[23]', '$number[24]')";
	
	if (mysqli_query($connection, $sql)) {
		  echo "<h2><font color=blue>New record added to database.</font></h2>";
	} else {
		  echo "Error 1: " . $sql . "<br>" . mysqli_error($connection);
	}
	$sql = "UPDATE datasss SET inner_id = id WHERE id = LAST_INSERT_ID()";
	if (mysqli_query($connection, $sql)) {
		  echo "<h3><font color=blue>All done.</font></h3>";
	} else {
		  echo "Error 2: " . $sql . "<br>" . mysqli_error($connection);
	}
	mysqli_close($connection);
}

// ---------------------------- Main program --------------------------------- //

$number = scrap_numbers("https://www.lottomaticaitalia.it/del/estrazioni-e-vincite/popup-pdf/estrazioni-giorno.html?data=".date('Ymd'));
$connection = connect_to_mysqli($mysqlserverhost, $username_mysql, $password_mysql, $database_name);
if(is_new_registry($number[0], $connection)){
	adding_to_database($number, $connection);	
} else{
	echo "INFO: <font color=red><b>Not added</b></font> new record (Extraction $number[0]) because it was already added recently. ";
}


?>

