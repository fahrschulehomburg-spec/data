<?php
// Audit Kurs B 2.11.2026 – Korrekturen einspielen. Aufruf: $PLAN_URL gesetzt, dann include.
if (!defined('ABSPATH')) exit;
$u=wp_upload_dir(); require_once $u['basedir'].'/tr-import/fixhelper.php'; global $wpdb;
$bk=$u['basedir'].'/tr-import/backup-audit-kursB-20261102.jsonl';
$log=function($w,$d) use($bk){ file_put_contents($bk,json_encode(['t'=>date('c'),'w'=>$w,'d'=>$d],JSON_UNESCAPED_UNICODE)."\n",FILE_APPEND); };
$res=wp_remote_get($PLAN_URL,['timeout'=>60]); if(is_wp_error($res)) return 'fetch: '.$res->get_error_message();
$plan=json_decode(wp_remote_retrieve_body($res),true); if(!$plan) return 'plan json leer';
$out=['kachel_ok'=>0,'kachel_fail'=>[],'q'=>[],'files'=>[]];
$dry=!empty($DRY);
// A1a: gespiegeltes offizielles A1b (tr-m14-002) -> schild-A1a.png
$src=$u['basedir'].'/2026/10/tr-m14-002.png'; $dst=$u['basedir'].'/fh-kurse/b/img/schild-A1a.png';
if(!file_exists($dst) && !$dry){ $im=imagecreatefrompng($src); imagealphablending($im,false); imagesavealpha($im,true); imageflip($im,IMG_FLIP_HORIZONTAL); imagepng($im,$dst); imagedestroy($im); $out['files'][]='schild-A1a.png '.filesize($dst); }
// Kacheln
foreach($plan['kachel_edits'] as $e){
  $k=trb_get_kachel($e['lid'],$e['i']);
  if($k===null){ $out['kachel_fail'][]=[$e['lid'],$e['i'],'fehlt']; continue; }
  $n=substr_count($k,$e['old']);
  if($n!==1){ $out['kachel_fail'][]=[$e['lid'],$e['i'],'count='.$n,$e['note']]; continue; }
  if($dry){ $out['kachel_ok']++; continue; }
  $log('kachel',['lid'=>$e['lid'],'i'=>$e['i'],'note'=>$e['note'],'alt'=>$k]);
  $r=trb_put_kachel($e['lid'],$e['i'],str_replace($e['old'],$e['new'],$k));
  if($r==='ok') $out['kachel_ok']++; else $out['kachel_fail'][]=[$e['lid'],$e['i'],$r];
}
if($dry) return $out;
// F350 Erklärung (4 Sprachen + Sim)
$eq=trb_q_eq(350); $ids=['de'=>$eq['de'],'fr'=>$eq['fr'],'ar'=>$eq['ar'],'th'=>$eq['th']];
foreach($ids as $l=>$qid){ foreach([$qid,$eq['sim'][$l]] as $id){ if(!$id) continue; $log('frage',trb_q($id)); $out['q'][]=['350',$l,$id,trb_q_update($id,['explanation'=>$plan['q350'][$l]])]; } }
// F1376: Foto tr-m14-197 aus Beschreibung entfernen
$eq=trb_q_eq(1376);
foreach(['de','fr','ar','th'] as $l){ foreach([$eq[$l],$eq['sim'][$l]] as $id){ if(!$id) continue; $q=trb_q($id); $d=$q['question_description']; $nd=preg_replace('/<img[^>]*tr-m14-197\.jpg[^>]*>/','',$d); if($nd!==$d){ $log('frage',$q); $out['q'][]=['1376',$l,$id,trb_q_update($id,['description'=>trim($nd)])]; } else $out['q'][]=['1376',$l,$id,'kein foto']; } }
// Du-Form (de + Sim de)
foreach($plan['du_fix'] as $qid=>$f){ $qid=(int)$qid; $eq=trb_q_eq($qid); $sim=$eq['sim']['de']??null; $ans=[]; foreach($f['answers'] as $k=>$t) $ans[]=['t'=>$t,'ok'=>$f['ok'][$k]];
  foreach([$qid,$sim] as $id){ if(!$id) continue; $log('frage',trb_q($id)); $out['q'][]=['du',$id,trb_q_update($id,['title'=>$f['title'],'answers'=>$ans])]; } }
// Quiz-Option Modul 1: max 37 -> 36
foreach([42127,42705,42856,43007] as $qz){ $o=get_post_meta($qz,'tutor_quiz_option',true); $log('quizopt',['quiz'=>$qz,'alt'=>$o]); if(is_array($o)){ $o['max_questions_for_answer']=36; update_post_meta($qz,'tutor_quiz_option',$o); $out['quizopt'][]=$qz; } }
if(function_exists('wpo_cache_flush')) wpo_cache_flush();
return $out;
