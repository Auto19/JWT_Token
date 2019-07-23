<?php
//Sort of an index page for the api
/*
TODO:
    - Change and migrate the secret key into a secure place. (Enviromental variable?)
    - Change the mysql users to secure place. (Env too?)
    - Make sure time works when servers are independant of each other.
    - Test and fill in mysql.
*/

//URL SAFE B64 encode and decode
//https://gist.github.com/nathggns/6652997
function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

//HEADERS
//https://developer.okta.com/blog/2019/03/08/simple-rest-api-php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



//Grab our token
$token_full = $_GET['token'];
$token_full = filter_var ($token_full, FILTER_SANITIZE_EMAIL); //D0n't trust Users 

$headers = apache_request_headers(); //left in here if tokens over headers is wanted > best use is in Authorization: Bearer


//Make sure our token is formated correctly and part it out to be used later
$token_separate = explode(".", $token_full);

if(sizeof($token_separate) != 3) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Bad Token');
}

$token_payload_to_utf8 = utf8_encode(base64url_decode($token_separate[1]));
$token_payload_to_utf8 = (string) $token_payload_to_utf8;
$json_payload = json_decode($token_payload_to_utf8, true);

if(time() > (int)$json_payload['exp'] || !array_key_exists('exp', $json_payload)) { //this will be a problem when time is > than 32 bit
    header("HTTP/1.1 401 Unauthorized");
    exit('Invalid Token');
}


//COMPARE TOKENS AND MAKE SURE WE ARE NOT BEING SCAMMED
$token_unsigned = $token_separate[0] . '.' . $token_separate[1];

$secret = 'yTAnNB06TxQI0aEIc3y8l19k1i5zeKJYaxyDkILfpqqMk0ojQyfbAO9wlPQW4HU2'; //64 letter secret

$token_real_signature = hash_hmac('sha256', $token_unsigned, $secret, true);

if(base64url_encode($token_real_signature) !== $token_separate[2]) {
    header("HTTP/1.1 401 Unauthorized");
    exit('Unauthorized');
}

//Now we can do api here

//Connect to the mysql database
$link = mysqli_connect("localhost", "mysql_user", "mysql_password", "mysql_database");

if (mysqli_connect_errno()) {
    http_response_code(500);
    exit('Database Failure');
}

mysqli_set_charset($link, "utf8")


$requestMethod = $_SERVER["REQUEST_METHOD"]; //Useable, for GET, POST, PUT, DELETE


switch ($requestMethod) {
    case 'GET':
        
        //POSSIBLE GET PARAMETERS [id] //Sample ID 
        $get_parameters = array();
        $get_parameters["id"] = $_GET["id"];  // NUll if does not exist
        

        //Check to make sure that data was actually given            
        $i = 0;
        foreach ($get_parameters as $v) {
            if($v == NULL) {
                $i += 1; 
            }
        }
        if($i == count($get_parameters)) {
            echo '{}';
            break; //no need to continue
        }
    
        //id select.        
	if($get_parameters["id"] !== NULL) {
            $id = $get_parameters["id"];
            $id = mysqli_real_escape_string($link, $id); //Again don't trust user input.
            
            mysqli_query($link, 'SELECT * FROM Users WHERE id=' . $id . ';'); 

        }
        break;
    case 'POST':
        break;
    case 'PUT':
        break;
    case 'DELETE':
        break;
    default:
        break;
}

mysqli_close($link);
?>



