<?php
// Hilfsfunktionen für Korrekturen in Kurs B (4 Sprachen). Einbinden mit:
// $u=wp_upload_dir(); require_once $u['basedir'].'/tr-import/fixhelper.php';
if (!defined('ABSPATH')) exit;
const TRB_COURSES = ['de'=>42089,'fr'=>42691,'ar'=>42842,'th'=>42993];
const TRB_SIM = ['de'=>43786,'fr'=>43787,'ar'=>43788,'th'=>43789];

function trb_struct(){
  static $S=null; if($S!==null) return $S; global $wpdb; $S=[];
  foreach(TRB_COURSES as $l=>$cid){
    $tops=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent=%d AND post_type='topics' ORDER BY menu_order,ID",$cid));
    foreach($tops as $ti=>$t){
      $items=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_parent=%d AND post_status='publish' AND ID NOT IN (".implode(',',TRB_SIM).") ORDER BY menu_order,ID",$t));
      foreach($items as $li=>$id) $S[$l][$ti][$li]=(int)$id;
    }
  }
  return $S;
}
function trb_pos($id){ foreach(trb_struct() as $l=>$tops) foreach($tops as $ti=>$items) foreach($items as $li=>$x) if($x==$id) return [$l,$ti,$li]; return null; }
// Lektion/Quiz-ID (deutsch) -> ['de'=>..,'fr'=>..,'ar'=>..,'th'=>..]
function trb_lesson_eq($id){ $p=trb_pos($id); if(!$p) return null; $S=trb_struct(); $o=[]; foreach(TRB_COURSES as $l=>$c) $o[$l]=$S[$l][$p[1]][$p[2]]??null; return $o; }

function trb_log($what,$data){ $u=wp_upload_dir(); file_put_contents($u['basedir'].'/tr-import/backup-fix-'.date('Ymd').'.jsonl', json_encode(['t'=>date('c'),'w'=>$what,'d'=>$data],JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); }

// Kacheln: Teil i = i-tes Stück beim Aufteilen vor jedem <h3> (i=0 = Einleitung)
function trb_parts($lid){ global $wpdb; $c=$wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d",$lid)); return preg_split('/(?=<h3)/',$c); }
function trb_get_kachel($lid,$i){ $p=trb_parts($lid); return $p[$i]??null; }
// Ganze Kachel (rohes HTML inkl. <h3>, Bild-Div, Text) ersetzen. Leerstring = Kachel löschen.
function trb_put_kachel($lid,$i,$html){ global $wpdb; $p=trb_parts($lid); if(!isset($p[$i])) return "kachel $i fehlt"; trb_log('kachel',['lid'=>$lid,'i'=>$i,'alt'=>$p[$i]]); $p[$i]=$html; $wpdb->update($wpdb->posts,['post_content'=>implode('',$p)],['ID'=>$lid]); clean_post_cache($lid); return 'ok'; }
// Nur den Fließtext (alles nach </h3> und nach Bild/Merk/Anim-Divs) einer Kachel ersetzen; $html z.B. '<p>…</p>'
function trb_put_text($lid,$i,$html){ $k=trb_get_kachel($lid,$i); if($k===null) return "kachel $i fehlt";
  // Ersetzt alle <p>…</p>, <ul>…</ul>, <ol>…</ol> der Kachel durch $html (an der Stelle des ersten); Überschrift, Bild, Merkkarte, Animation bleiben.
  $re='/<(p|ul|ol)(?:\s[^>]*)?>[\s\S]*?<\/\1>\s*/';
  if(!preg_match($re,$k,$m,PREG_OFFSET_CAPTURE)) return "kein Text-Absatz in Kachel $i – nutze trb_put_kachel";
  $pos=$m[0][1]; $rest=preg_replace($re,'',substr($k,$pos)); $n=substr($k,0,$pos).$html."\n".$rest;
  return trb_put_kachel($lid,$i,$n); }
function trb_put_heading($lid,$i,$txt){ $k=trb_get_kachel($lid,$i); $n=preg_replace('/<h3([^>]*)>.*?<\/h3>/s','<h3$1>'.esc_html($txt).'</h3>',$k,1); return trb_put_kachel($lid,$i,$n); }

// Quizfragen
function trb_q($qid){ global $wpdb; $q=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id=%d",$qid),ARRAY_A); if(!$q) return null; $q['answers']=$wpdb->get_results($wpdb->prepare("SELECT answer_id,answer_title,is_correct,answer_order FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id=%d ORDER BY answer_order",$qid),ARRAY_A); return $q; }
// deutsche Frage-ID -> ['de'=>id,'fr'=>id,'ar'=>id,'th'=>id,'sim'=>['de'=>id|null,...]]
function trb_q_eq($qid){ global $wpdb; $q=trb_q($qid); if(!$q) return null; $quiz=(int)$q['quiz_id']; $eq=trb_lesson_eq($quiz); if(!$eq) return null;
  $ord=$wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id=%d ORDER BY question_order,question_id",$quiz)); $n=array_search($qid,$ord);
  $o=[]; foreach($eq as $l=>$qz){ $ids=$wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id=%d ORDER BY question_order,question_id",$qz)); $o[$l]=isset($ids[$n])?(int)$ids[$n]:null; }
  $o['sim']=[]; foreach(TRB_SIM as $l=>$sq){ $t=$o[$l]?$wpdb->get_var($wpdb->prepare("SELECT question_title FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id=%d",$o[$l])):null; $o['sim'][$l]=$t?(int)$wpdb->get_var($wpdb->prepare("SELECT question_id FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id=%d AND question_title=%s LIMIT 1",$sq,$t)):null; }
  return $o; }
// $f: ['title'=>..,'explanation'=>..,'description'=>..,'answers'=>[['t'=>'..','ok'=>1],...]] – nur gesetzte Felder werden geändert.
// Antworten: Anzahl muss gleich bleiben (werden der Reihe nach überschrieben), sonst Fehler.
function trb_q_update($qid,$f){ global $wpdb; $q=trb_q($qid); if(!$q) return "frage $qid fehlt"; trb_log('frage',$q); $up=[];
  if(isset($f['title'])) $up['question_title']=$f['title']; if(isset($f['explanation'])) $up['answer_explanation']=$f['explanation']; if(isset($f['description'])) $up['question_description']=$f['description'];
  if($up) $wpdb->update($wpdb->prefix.'tutor_quiz_questions',$up,['question_id'=>$qid]);
  if(isset($f['answers'])){ if(count($f['answers'])!=count($q['answers'])) return 'antwortzahl ungleich'; foreach($q['answers'] as $k=>$a){ $wpdb->update($wpdb->prefix.'tutor_quiz_question_answers',['answer_title'=>$f['answers'][$k]['t'],'is_correct'=>$f['answers'][$k]['ok']?1:0],['answer_id'=>$a['answer_id']]); } }
  return 'ok'; }
