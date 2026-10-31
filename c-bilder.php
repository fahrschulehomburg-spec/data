<?php
// Setzt Kachelbilder in den Tutor-Kurs C: $map = { "n/s/c": {"de": html, "fr": html} } -> direkt nach dem c-ten <h3> der Lektion c-m{n}-l{s+1}
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';
function trc_bilder($map, $dry = false){
  global $wpdb; $res = ['ok'=>0,'skip'=>[],'fehler'=>[]]; $bk = []; $cache = [];
  foreach ($map as $k => $byLang){
    [$n,$s,$ci] = array_map('intval', explode('/', $k));
    foreach ($byLang as $l => $html){
      $lid = tr_find("c-m$n-l".($s+1), $l); if(!$lid){ $res['fehler'][]="$k $l lektion"; continue; }
      if(!isset($cache[$lid])){ $cache[$lid] = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d",$lid)); $bk[$lid]=$cache[$lid]; }
      $c = $cache[$lid];
      preg_match_all('/<h3[^>]*>.*?<\/h3>/s', $c, $mm, PREG_OFFSET_CAPTURE);
      if(!isset($mm[0][$ci])){ $res['fehler'][]="$k $l h3"; continue; }
      $pos = $mm[0][$ci][1] + strlen($mm[0][$ci][0]);
      if (preg_match('/^\s*<div class="tr-icon/', substr($c,$pos,40))){ $res['skip'][]="$k $l"; continue; }
      $c = substr($c,0,$pos)."\n".$html.substr($c,$pos);
      $cache[$lid] = $c; $res['ok']++;
    }
  }
  if(!$dry){
    $u = wp_upload_dir(); file_put_contents($u['basedir'].'/tr-import/backup-c-bilder-'.date('YmdHis').'.json', json_encode($bk, JSON_UNESCAPED_UNICODE));
    foreach($cache as $lid=>$c){ if($c !== $bk[$lid]){ $wpdb->update($wpdb->posts,['post_content'=>$c],['ID'=>$lid]); clean_post_cache($lid); } }
  }
  return $res;
}
