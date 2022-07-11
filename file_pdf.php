<?php
function searchFile($folder, $srch, &$results) {
  $folder = rtrim($folder, "/") . '/';
  if ($hd = opendir($folder)) {
    while (false !== ($file = readdir($hd))) { 
      if($file != '.' && $file != '..') {
        if(is_dir($folder . $file)) {
          searchFile($folder. $file, $srch, $results);
        } elseif(preg_match("#\.$srch$#", $file)) {
          $results[] = $folder . $file;
        }
      }
    }
    closedir($hd); 
  }
}
$r=array();
searchFile('htdocs', 'pdf', $r);
foreach($r as $f) {
  echo $f, '<br />';
}