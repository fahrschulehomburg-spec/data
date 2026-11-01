<?php
if (!defined('ABSPATH')) exit;
function tr_startbild($srcs){
  $up=wp_upload_dir(); $d=$up['basedir'].'/tr-import/fonts/'; if(!is_dir($d)) mkdir($d);
  foreach(['Poppins-Bold.ttf','Poppins-Medium.ttf'] as $f){ if(!is_file($d.$f)) file_put_contents($d.$f,wp_remote_retrieve_body(wp_remote_get('https://raw.githubusercontent.com/fahrschulehomburg-spec/data/tmp-fhk/fonts/'.$f))); }
  $txt=['B'=>['Online-Theoriekurs Klasse B','Führerschein Belgien · TheorieRoute'],'C'=>['Online-Theoriekurs Lkw C/CE','Führerschein Belgien · TheorieRoute']];
  $out=[];
  foreach($srcs as $k=>$u){
    $raw=wp_remote_retrieve_body(wp_remote_get($u,['timeout'=>60]));
    $im=imagecreatefromstring($raw); if(!$im){ $out[$k]='kein bild'; continue; }
    $W=imagesx($im); $H=imagesy($im);
    $c=imagecreatetruecolor(1280,720);
    $sh=(int)round(720*$W/1280); $sy=max(0,(int)(($H-$sh)/2));
    imagecopyresampled($c,$im,0,0,0,$sy,1280,720,$W,min($sh,$H));
    imagealphablending($c,true);
    for($y=0;$y<400;$y++){ $a=(int)min(127,8+pow($y/400,1.6)*119); imageline($c,0,$y,1279,$y,imagecolorallocatealpha($c,10,18,28,$a)); }
    $white=imagecolorallocate($c,255,255,255); $yel=imagecolorallocate($c,245,183,0);
    imagefilledrectangle($c,40,52,900,290,imagecolorallocatealpha($c,10,18,28,38));
    imagefilledrectangle($c,64,72,73,262,$yel);
    imagettftext($c,64,0,98,150,$white,$d.'Poppins-Bold.ttf','Fahrschule Homburg');
    imagettftext($c,36,0,100,212,$white,$d.'Poppins-Medium.ttf',$txt[$k][0]);
    imagettftext($c,24,0,102,256,$yel,$d.'Poppins-Medium.ttf',$txt[$k][1]);
    $f=$up['basedir']."/fh-kurse/b/img/start-kurs-$k.jpg"; imagejpeg($c,$f,86);
    $out[$k]=$f;
  }
  return $out;
}
