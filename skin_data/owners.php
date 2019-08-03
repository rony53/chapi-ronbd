<?php

function convertSpecialChars($str, $permitidos, $remplazo) { // reemplaxo caracteres
  for ($i = 0; $i < strlen($permitidos); $i++) {
    $allowed[] = substr($permitidos, $i, 1);
  }
  $out='';
  for ($i = 0; $i < strlen($str); $i++) {
    $char = substr($str, $i, 1);
    if(in_array($char, $allowed)) $out .= $char;
    else $out .= $remplazo;
  }
  unset($allowed);
  return $out;
}

#############################################################################################

$owners=array();
if($lineas = @file("php://stdin")) {
  foreach($lineas as $linea) {
    $linea = str_replace("\n", "", $linea);
    list($user, $data) = explode('=', $linea, 2);
    parse_str($data);
    $owners[$user]=$creator;
  }

}

$o = getopt("d:");

$output='';

if($o['d']) {
  foreach($owners as $reseller){
    if($reseller!='root' && $reseller!='admin') {
      $imgName = $reseller.".gif";
      $RESELLER = convertSpecialChars(strtoupper($reseller), 'QWERTYUIOPASDFGHJKLZXCVBNM_1234567890', '_');
      $output .= "IMG_RESLOGO_".$RESELLER."=images/custom/".$imgName."\n";
    }
  }
  echo $output;
} else {
  echo serialize($owners);
}

?>