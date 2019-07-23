<?php
//the verification idea for the api


//URL SAFE B64 encode and decode
//https://gist.github.com/nathggns/6652997
function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}


//Grab our token
$token_full = $_GET['token'];

$token_full = filter_var ($token_full, FILTER_SANITIZE_EMAIL); //D0n't trust Users 

$headers = apache_request_headers(); //left in here if tokens over headers is wanted > best use is in Authorization: Bearer


//Make sure our token is formated correctly and part it out to be used later
$token_separate = explode(".", $token_full);

if(sizeof($token_separate) != 3) {
    echo "That's not a proper token!";
    exit();
}

$token_payload_to_utf8 = utf8_encode(base64url_decode($token_separate[1]));
$token_payload_to_utf8 = (string) $token_payload_to_utf8;
$json_payload = json_decode($token_payload_to_utf8, true);

if(time() > (int)$json_payload['exp'] || !array_key_exists('exp', $json_payload)) { //this will be a problem when time is > than 32 bit
    echo "Your Token has expired or is invalid";
    exit();
}


//COMPARE TOKENS AND MAKE SURE WE ARE NOT BEING SCAMMED
$token_unsigned = $token_separate[0] . '.' . $token_separate[1];

$secret = 'yTAnNB06TxQI0aEIc3y8l19k1i5zeKJYaxyDkILfpqqMk0ojQyfbAO9wlPQW4HU2'; //64 letter secret

$token_real_signature = hash_hmac('sha256', $token_unsigned, $secret, true);

if(base64url_encode($token_real_signature) == $token_separate[2]) {
    echo "Welcome to the system";
} else {
    echo "Faker, that ain't a real token";
    exit();
}

//Now we can do api here

?>


<html>
<body>
<p><?php echo base64url_decode($token_separate[0]); ?></p>
<p><?php echo base64url_decode($token_separate[1]); ?></p>
<p><?php echo base64url_decode($token_separate[2]); ?></p>
<p><?php echo (int)$json_payload['exp'] - time(); ?> seconds until expires </p>
</body>
</html>
