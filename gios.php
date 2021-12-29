<?php

if (php_sapi_name() != "cli")
{
	die(messageCli("Execute is avaible only on console"));
}

$cfg = array();
$cfg["storageDir"] = "data/";
$cfg["gios"] = array(
	"stations" => "https://api.gios.gov.pl/pjp-api/rest/station/findAll",
	"sensors" => "https://api.gios.gov.pl/pjp-api/rest/station/sensors/",
	"data" => "https://api.gios.gov.pl/pjp-api/rest/data/getData/"
);

if (count($argv) < 2)
{
	die(messageCli("
	
		Examples:
		
		# find station ID
		php gios.php find \"City name\"
		
		# get station sensors
		php gios.php sensors station_id
		
		# get data from selected sensor
		php gios.php data sensor_id
		
		# get all datas from selected station
		php gios.php getall station_id
		
	"));
}

set_time_limit(0);
error_reporting(E_ALL);

switch ($argv[1])
{
	case "find":
		// get and parse city name from argv
		$city = array();
		for($i = 2; $i < count($argv); $i++)
		{
			$city[] = trim($argv[$i]);
		}
		$city = implode(" ", $city);
		
		// get stations list json from GIOS server
		$stations = file_get_contents_curl($cfg["gios"]["stations"]);
		if ($stations)
		{
			$stations = json_decode($stations, true);
			
			$stations = array_filter($stations, function($stack) use ($city) {
				return ($stack["city"]["name"] == $city);
			});
			
			echo "\n" . messageCli(bashFormat(array("white", "lightmagentabg", "bold"), "List of stations in " . $city . ":"), 2);

			if ($stations)
			{
				foreach($stations as $station)
				{
					echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Station ID:   ") . " " . $station["id"], 1);
					echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Station name: ") . " " . $station["stationName"], 1);
					echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Address:      ") . " " . $station["addressStreet"] . " (". $station["city"]["commune"]["provinceName"] .")", 1);
					echo "\n";
				}
			}
			else
			{
				die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find the city..."), 2));
			}
		}
	break;
	
	case "sensors":
		if (!isset($argv[2]) || intval($argv[2]) == 0)
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find station ID argument..."), 2));
		}
		
		// get list of sensors
		$sensors = file_get_contents_curl($cfg["gios"]["sensors"] . $argv[2]);
		if ($sensors)
		{
			$sensors = json_decode($sensors, true);
			
			echo "\n" . messageCli(bashFormat(array("white", "lightmagentabg", "bold"), "List of sensors in " . $argv[2] . " station:"), 2);

			foreach($sensors as $sensor)
			{
				echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Sensor ID:   ") . " " . $sensor["id"], 1);
				echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Sensor name: ") . " " . $sensor["param"]["paramName"], 1);
				echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "Sensor code: ") . " " . $sensor["param"]["paramCode"], 1);
				echo "\n";
			}

		}
		else
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find any sensors..."), 2));
		}
	break;
	
	case "data":
		if (!isset($argv[2]) || intval($argv[2]) == 0)
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find sensor ID argument..."), 2));
		}
		
		// get data from sensor
		$data = file_get_contents_curl($cfg["gios"]["data"] . $argv[2]);
		if ($data)
		{
			$aData = json_decode($data, true);
			if (file_put_contents($cfg["storageDir"] . $argv[2] . "-" . strtolower($aData["key"] . ".json"), $data))
			{
				echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "OK:") . " " . $aData["key"] . " data from sensor ID " . $argv[2] . " has been stored correctly.", 2);
			}
		}
		else
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find any data from selected sensor..."), 2));
		}
	break;
	
	case "getall":
		if (!isset($argv[2]) || intval($argv[2]) == 0)
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find station ID argument..."), 2));
		}
		
		// get list of sensors
		$sensors = file_get_contents_curl($cfg["gios"]["sensors"] . $argv[2]);
		if ($sensors)
		{
			$sensors = json_decode($sensors, true);
			
			foreach($sensors as $sensor)
			{
				// get data from sensor
				$data = file_get_contents_curl($cfg["gios"]["data"] . $sensor["id"]);
				if ($data)
				{
					$aData = json_decode($data, true);
					if (file_put_contents($cfg["storageDir"] . $sensor["id"] . "-" . strtolower($aData["key"] . ".json"), $data))
					{
						echo messageCli(bashFormat(array("black", "lightyellowbg", "bold"), "OK:") . " " . $aData["key"] . " data from sensor ID " . $sensor["id"] . " has been stored correctly.", 1);
					}
				}
			}

		}
		else
		{
			die("\n" . messageCli(bashFormat(array("white", "redbg", "bold"), "Error: can't find any sensors..."), 2));
		}
	break;
}

function file_get_contents_curl($url = "", $params = false, $target = false)
{
	global $robot;

	$c = curl_init();

	curl_setopt($c, CURLOPT_AUTOREFERER, true);
	curl_setopt($c, CURLOPT_HEADER, 0);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_URL, $url);

	$useragent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:95.0) Gecko/20100101 Firefox/95.0";
	
	curl_setopt($c, CURLOPT_USERAGENT, $useragent);
	curl_setopt($c, CURLOPT_COOKIESESSION, false);
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 5); 
	curl_setopt($c, CURLOPT_TIMEOUT, 10);

	$data = curl_exec($c);
	curl_close($c);

	if (isset($file))
	{
		fclose($file);
	}
	return $data;
}

function messageCli(string $text = "", int $nl = 2)
{
	$nlstr = "";
	for ($i = 1; $i <= $nl; $i++)
	{
		$nlstr .= "\n";
	}
	return trim(preg_replace("/\t+/", "", $text)) . $nlstr;
}

function bashFormat(array $format=[], string $text = "")
{
	$codes = array(
		"bold" => 1,
		"italic" => 3, "underline" => 4, "strikethrough" => 9,
		"black" => 30, "red" => 31, "green" => 32, "yellow" => 33, "lightyellow" => 93,"blue" => 34, "magenta" => 35, "cyan" => 36, "white" => 37,
		"blackbg" => 40, "redbg" => 41, "greenbg" => 42, "yellowbg" => 43, "lightyellowbg" => 103, "bluebg" => 44, "magentabg" => 45, "lightmagentabg" => 105, "cyanbg" => 46, "lightgreybg" => 47
	);
	
	$formatMap = array_map(function ($v) use ($codes) { return $codes[$v]; }, $format);
	
	return "\e[" . implode(";", $formatMap) . "m" . $text . "\e[0m";
}

	
?>