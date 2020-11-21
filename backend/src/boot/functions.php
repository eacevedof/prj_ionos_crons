<?php
//functions.php 20200721
function appboot_loadenv()
{
    $arpaths = [
        "%PATH_PUBLIC%" => PATH_PUBLIC, "%PATH_ROOT%" => PATH_ROOT,
        "%PATH_SRC%" => PATH_SRC, "%PATH_CONFIG%" => PATH_CONFIG
    ];

    $arEnvs = ["local" => ".env.local", "dev" => ".env.dev","test" => ".env.test","prod" => ".env", ];

    foreach ($arEnvs as $envtype => $envfile) {
        $pathenv = PATH_ROOT . DS . $envfile;
        if (is_file($pathenv)) {
            $content = file_get_contents($pathenv);
            $lines = explode("\n", $content);

            foreach ($lines as $strline) {
                if (strstr($strline, "=")) {
                    $keyval = explode("=", $strline);
                    $key = trim($keyval[0]);
                    if ($key) {
                        $value = trim($keyval[1] ?? "");
                        $value = str_replace(array_keys($arpaths), array_values($arpaths), $value);
                        $_ENV[$key] = $value;
                    }
                }//if line has =
            }//foreach lines
            return;
        }//if is file
    }//foreach envs

    $_SERVER += $_ENV;
}

function get_in_bytes(string $from)
{
    $units = ["B", "KB", "MB", "GB", "TB", "PB"];
    $number = substr($from, 0, -2);
    $suffix = strtoupper(substr($from,-2));

    //B or no suffix
    if(is_numeric(substr($suffix, 0, 1)))
        return preg_replace("[^\d]", "", $from);

    $exponent = array_flip($units)[$suffix] ?? null;
    if($exponent === null) return null;

    return $number * (1024 ** $exponent);
}

function pr($mxvar,$title="")
{
    $now = date("Ymd H:i:s");
    echo "\n$now";
    if($title) echo "\n$title:\n";
    print_r($mxvar);
    echo "\n";
}