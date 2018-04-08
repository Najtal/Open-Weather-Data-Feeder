

<?php


/*--------------------------------
 *          WEATHER APP          
 *
 *  Author: Jean-Vital Durieu
 *  Created on:     08/04/2018
 *  Last update:    08/04/2018
 *
 *--------------------------------
 *
 *  This app reads the openweather api and save the data in Atheris's database
 *  If the data from the api is updated compared with the one in db, then DB is updated otherwise not.
 *  All updated results are shown
 *
 *------------------------------*/


include 'Request.php';
use \JJG\Request as Request;


/*       GLOBALS
 -----------------------*/

// DB CNX DATA
$GLOBALS['servername'] = "";
$GLOBALS['username'] = "";
$GLOBALS['password'] = "";
$GLOBALS['dbname'] = "";
$GLOBALS['apiCode'] = "";


// CITIES
//  [ COUTRY CODE , CITY NAME , LATITUDE , LONGITUDE , CITY CODE ]
$cities = Array(
    // BELGIQUE
    Array("BE","Louvain-la-Neuve",2792073,50.6682, 4.6129),
    Array("BE","Brussels",2800865,50.8466, 4.3517),
    Array("BE","Namur",2790472,50.4665, 4.8662),
    Array("BE","Liège",2792414,50.6337, 5.5675),
    Array("BE","Oostende",2789786,51.2303, 2.9203),
    Array("BE","Charleroi",2800481,50.412, 4.4436),
    Array("BE","Kortrijk",2794055,50.8276, 3.266),
    Array("BE","Gent",2797656,51.0538, 3.725),
    // LUXEMBOURG
    Array("LU","Luxembourg",2960316,49.6113, 6.1298),
    // ALLEMAGNE
    Array("DE","Frankfurt",2823968,49.6806, 10.5267),
    Array("DE","Cologne",6691073,50.9384, 6.96),
    Array("DE","Stuttgart",2825297,48.7784, 9.18),
    Array("DE","Munich",2867714,48.1371, 11.5754),
    Array("DE","Hanover",6559065,52.3745, 9.7386),
    Array("DE","Berlin",6545310,52.517, 13.3889),
    Array("DE","Hamburg",2911298,53.5503, 10.0007),
    // PAYS BAS
    Array("NL","Amsterdam",2759794,52.3728, 4.8936),
    Array("NL","Rotterdam",2747890,51.9229, 4.4632),
    Array("NL","Assen",2759633,52.9954, 6.5606),
    // FRANCE
    Array("FR","Amiens",3037854,49.8942, 2.2957),
    Array("FR","Reims",6454251,49.2578, 4.0319),
    Array("FR","Metz",6454365,49.1197, 6.1764),
    Array("FR","Strasbourg",2973781,48.5846, 7.7507),
    Array("FR","Besancon",3033123,47.2488, 6.0182),
    Array("FR","Nancy",2990999,48.6937, 6.1834),
    Array("FR","Dijon",3006622,47.3216, 5.0415),
    Array("FR","Lyon",8015556,45.7578, 4.832),
    Array("FR","Montpellier",2992166,43.6112, 3.8767),
    Array("FR","Marseille",2995469,43.2962, 5.37),
    Array("FR","Toulouse",2972315,43.6045, 1.4442),
    Array("FR","Bordeaux",3031582,44.8412, -0.5801),
    Array("FR","Nantes",6434483,47.2186, -1.5542),
    Array("FR","Le mans",3003603,48.0079, 0.1997),
    Array("FR","Rouan",2982682,47.1858, -1.8599),
    Array("FR","Paris",2973781,48.8566, 2.3515),
    // SUISSE
    Array("CH","Genève",6691638,46.2018, 6.1466),
    Array("CH","Bern",2661552,46.9483, 7.4515),
    Array("CH","Zurich",6295546,47.3724, 8.5423),
    // GREAT BRITAIN
    Array("GB","London",2643743,51.5073, -0.1277),
    Array("GB","Manchester",2643123,53.4791, -2.2442),
    Array("GB","Edinburgh",3333229,55.9496, -3.1915),
    Array("GB","Belfast",2655984,54.597, -5.9301),    
    // Ireland
    Array("IE","Dublin",2962486,53.3498, -6.2603),
    Array("IE","Galway",2964180,53.2744, -9.0491),
    Array("IE","Cork",2965140,51.8979, -8.4706)
); 

// STATS
$nbUpdates = 0;
$nbNewCities = 0;


/*-------------------------------
 *           MAIN APP           *
 *------------------------------*/

function main($cities) {
    global $nbUpdates, $nbNewCities;

    // Establish SQL connexion
    $conn = buildconn();

    // Get latest encoded results
    $result = getLatestData($conn);

    echo count($result)." previous cities in db<br><br>";

    // FOR ALL CITIES
    foreach($cities as $item) {
        // Find latest data and compare
        // If newer data --> UPDATE data with finel time
        //               --> CREATE new data with newest data
        // If not newer  --> do nothing
        // If no latest  --> CREATE new data with newest data
        findAndUpdateData($item, $result, $conn);
    }

    // Close connexion
    $conn->close();

    // Display stats
    echo "<br>";
    echo "Number updates : ".$nbUpdates;
    echo "<br>";
    echo "Number new cities : ".$nbNewCities;
}


/**
 *  Create a MY SQL CONNEXION
 *  Return an open connexion instance
 */
function buildConn() {
    // Create connexion
    $conn = new mysqli($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['dbname']);
    // CHeck connexion
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}


/**
 *  Get latest data from open the database per city location
 *  Return Array of data from Openn weather API
 */
function getLatestData($conn) {
    // GET LATEST DATA FROM DATABASE
    $sqlGetLatestData = "SELECT * FROM weather w1 WHERE creation_time = (SELECT MAX(creation_time) FROM weather w2 WHERE w1.id = w2.id) GROUP BY id";
    $res = $conn->query($sqlGetLatestData);
    return resultToArray($res);
}


/**
 *  Find latest data and compare
 *  If newer data --> UPDATE data with finel time
 *                --> CREATE new data with newest data
 *  If not newer  --> do nothing
 *  If no latest  --> CREATE new data with newest data
 *  Retudn -
 */
function findAndUpdateData($item,$result, $conn) {
    global $nbUpdates, $nbNewCities;
    $find = 0;

    // FIND LATEST DATA
    // FOR ALL LATEST DATA
    foreach($result as $row) { 
        // If latest data is from city
        if ($row["id"] == $item[2]) {
            // Latest data found
            $find = 1;

            // get latest weather data
            $jsWeatherCity = getLatestWeather($item);

            // TEST RESPONSE
            if ($jsWeatherCity == null) {
                break;  
            }

            echo "check <b>".$item[1]."</b> [".$row["id"]."]<br>";

            // IF DATA IS NEWER THAN LAST ONE
            if ($jsWeatherCity['dt'] != $row["time"]) {
                echo "last temp is ".$row['temperature']."°C, wind is ".$row['wind_speed']."m/s and cloud coverage is ".$row['clouds']."% <br>";
                echo "new temp is ".$jsWeatherCity['main']['temp']."°C, wind is ".$jsWeatherCity['wind']['speed']."m/s and cloud coverage is    ".$jsWeatherCity['clouds']['all']."% <br>";

                // CREATE new DATA
                registerNewMeasurement($item, $conn, $jsWeatherCity);
                $nbUpdates = $nbUpdates+1;
                echo "<br>";
            } else {
                // UPDATE last data
                $sql = "UPDATE weather SET update_time = now() WHERE id = '".$row["id"]."' AND creation_time = '" . $row["creation_time"] ."'";
                if ($conn->query($sql) === TRUE) {
                    echo "> data updated<br><br>";
                } else {
                    echo "[!] Error updating record: " . $conn->error."<br>";
                }
            }
            break;
        }
    }

    // IF MESUREMENT NOT EXIST
    if ($find == 0) {
        // CREATE NEW MEASUREMENT
        $jsWeatherCity = getLatestWeather($item);
        echo "Not yet registred<br>";
        echo "new temp is ".$jsWeatherCity['main']['temp']."°C, wind is ".$jsWeatherCity['wind']['speed']."m/s and cloud coverage is ".$jsWeatherCity['clouds']['all']."% <br>";
        if (registerNewMeasurement($item, $conn, $jsWeatherCity) == true) {
            $nbNewCities = $nbNewCities +1;
        }
        echo "<br>";
    }
}


/**
 *  Get the latest weather data from openweather
 *  Return a array of weather data
 */
function getLatestWeather($data) {
    // Prepare REQUEST
    $request = new Request('http://api.openweathermap.org/data/2.5/weather?lat='.$data[3].'&lon='.$data[4].'&units=metric&appid='.$GLOBALS['apiCode']);
    
    // Execute REQUEST
    $request->execute();
    $response = $request->getResponse();
    $httpCode = $request->getHttpCode();

    if ($httpCode != 200) {
        echo "[!] Weather code: " . $httpCode;
        return null;    
    }

    // Prepare RESPONSE
    $responseBody = $request->getHttpCode();
    $responseHeader = $request->getHeader();

    $rbJson = json_decode($response, true);

    return $rbJson;
}


/**
 *  Register in DB a new measurement
 */
function registerNewMeasurement($data, $conn, $rbJson) {
    // EXTRACT INFO
    // -- DATA INFO
    $time = $rbJson['dt'];                          // unit: LONG       Time of data calculation, unix, UTC 
    $locationId = $rbJson['id'];                    // unit: INT        City ID (client device unique identifier)
    // -- position
    $pLat = $data[3];                                // unit: DOUBLE    Coordonate-longitude
    $pLon = $data[4];                                // unit: DOUBLE    Coordonate-latitude
    // -- weather
    if (count($rbJson['weather']) > 0) {
        $wId = $rbJson['weather'][0]['id'];         // unit: INT        Weather condition id (ENUM)
        $wInfo = $rbJson['weather'][0]['main'];     // unit: STRING     Weather description
    } else {
        $wId = 0;
        $wInfo = "";
    }

    // -- main
    $mTemperature = $rbJson['main']['temp'];        // unit: DOUBLE     °C
    $mPressure = $rbJson['main']['pressure'];       // unit: INT        hPa
    $mHumidity = $rbJson['main']['humidity'];       // unit: INT        %
    $mTemp_min = $rbJson['main']['temp_min'];       // unit: DOUBLE
    $mTemp_max = $rbJson['main']['temp_max'];       // unit: DOUBLE
    $mVisibility = $rbJson['visibility'];           // unit: INT
    // -- wind
    $wSpeed = $rbJson['wind']['speed'];             // unit: DOUBLE     meter/sec
    $wDeg = $rbJson['wind']['deg'];                 // unit: INT        [0-360]
    // -- clouds
    $clouds = $rbJson['clouds']['all'];             // unit: INT        %


    /* --------------------------
     *     SAVE IN DATA BASE
     --------------------------*/

    // INSERT DATA TO DB

    $sql = "INSERT INTO weather (time, id, lat, lon, weather_id, weather_info, temperature, pressure, humidity, temp_min, temp_max, visibility, wind_speed, wind_deg, clouds) 
    VALUES ('".$time."', '".$locationId."', '".$pLat."', '".$pLon."', '".$wId."', '".$wInfo."', '".$mTemperature."', '".$mPressure."', '".$mHumidity."', '".$mTemp_min."', '".$mTemp_max."', '".$mVisibility."', '".$wSpeed."', '".$wDeg."', '".$clouds."')";

    //$sql = "INSERT INTO weather (time, lname, email, company, sector, country, address)
    //      VALUES ('".$fname."', '".$lname."', '".$email."', '".$company."', '".$sector."', '".$country."', '".$address."')";

    if ($conn->query($sql) === TRUE) {
        echo "> new data saved<br>";
        return true;
    } else {
        echo "   ---   [!] Error: " . $sql . " :: " . $conn->error;
    }
}



/*----------------------
 *       UTILS
 ----------------------*/


/**
 *  Return sql result to Array
 */
function resultToArray($result) {
    $rows = Array();
    if ($result == null) {
        return null;
    }
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}


main($cities);


?>