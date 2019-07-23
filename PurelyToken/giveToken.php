<?php
//NEED TO VERIFY USER BEFORE WE GIVE THEM A TOKEN FYI

//URL SAFE B64 encode and decode
//https://gist.github.com/nathggns/6652997
function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
} 

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}


//HEADER
$token_header = json_encode(["alg" => "HS256", "typ" => "JWT"]);


//PAYLOAD
$exp_date = time() + 600; //current time + 600 seconds
$t_id = (string)time() . ' ' . $_SERVER['REMOTE_ADDR']; //for a token to be correct limit requests to once a second

$token_payload = json_encode(["iss" => "emeralddata.net", "exp" => (string)$exp_date, "jti" => (string)$t_id]);

//HEADER and PAYLOAD

$token_unsigned = base64url_encode($token_header); 
$token_unsigned .= ".";
$token_unsigned .= base64url_encode($token_payload);

//SIGNATURE
$secret = 'yTAnNB06TxQI0aEIc3y8l19k1i5zeKJYaxyDkILfpqqMk0ojQyfbAO9wlPQW4HU2'; //64 letter secret


$token_signature = hash_hmac('sha256', $token_unsigned, $secret, true);

$token = $token_unsigned . '.' . base64url_encode($token_signature);

?>

<html>
<body>
<p>HEADER: <?php echo $token_header ?></p>
<p>PAYLOAD: <?php echo $token_payload ?></p>
<p>SIGNATURE: <?php echo $token_signature ?></p>
<p></p>
<p>TOKEN: <?php echo $token ?>
</body>
</html>
