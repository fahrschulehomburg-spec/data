<?php
// TheorieRoute CCAP Bus (Klasse D) (Grundqualifikation Code 95): legt aus uploads/tr-import/ccapd-{de,fr}.json den Tutor-Kurs an.
// Idempotent ueber _tr_src (Praefix ccapd-) / _tr_lang; WPML-Verknuepfung de<->fr ueber tr_wpml() aus tr-import/import.php.
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';

function ccapd_data($lang){ static $C=[]; if(!isset($C[$lang])){ $u=wp_upload_dir(); $C[$lang]=json_decode(file_get_contents($u['basedir']."/tr-import/ccapd/ccapd-$lang.json"),true); } return $C[$lang]; }

// Kursbild: vorhandene Datei fh-kurse/b/img/sit-8e34e202.jpg als Anhang registrieren (einmalig, ohne Kopie)
function ccapd_thumb(){
  global $wpdb;
  $id=(int)$wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_ccapd_thumb' LIMIT 1");
  if($id && get_post($id)) return $id;
  $u=wp_upload_dir(); $file=$u['basedir'].'/fh-kurse/b/img/sit-8e34e202.jpg'; if(!is_readable($file)) return 0;
  require_once ABSPATH.'wp-admin/includes/image.php';
  $id=wp_insert_attachment(['post_mime_type'=>'image/jpeg','post_title'=>'TheorieRoute CCAP Bus Kursbild','post_status'=>'inherit','guid'=>$u['baseurl'].'/fh-kurse/b/img/sit-8e34e202.jpg'],$file);
  update_post_meta($id,'_wp_attached_file','fh-kurse/b/img/sit-8e34e202.jpg');
  add_filter('intermediate_image_sizes_advanced','__return_empty_array');
  wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$file));
  remove_filter('intermediate_image_sizes_advanced','__return_empty_array');
  update_post_meta($id,'_ccapd_thumb','sit-8e34e202.jpg');
  return $id;
}

function ccapd_course($lang){
  $D=ccapd_data($lang); $c=$D['course'];
  $id=tr_post('courses','ccapd-course',$lang,['post_title'=>$c['title'],'post_status'=>'private','post_content'=>$c['content'],'post_excerpt'=>$c['excerpt']]);
  update_post_meta($id,'_tutor_course_price_type','free');
  update_post_meta($id,'_tutor_course_level','all_levels');
  update_post_meta($id,'_tutor_course_benefits',$c['benefits']);
  $t=ccapd_thumb(); if($t) set_post_thumbnail($id,$t);
  return $id;
}

function ccapd_quiz($src,$lang,$title,$topic,$order,$qs,$desc,$opt=[],$fixed=false){
  global $wpdb; $p=$wpdb->prefix; $nq=count($qs);
  $qid=tr_post('tutor_quiz',$src,$lang,['post_title'=>$title,'post_parent'=>$topic,'menu_order'=>$order,'post_content'=>$desc]);
  update_post_meta($qid,'tutor_quiz_option',array_merge(['time_limit'=>['time_value'=>0,'time_type'=>'minutes'],'hide_quiz_time_display'=>0,'feedback_mode'=>'reveal','enable_answer_reveal'=>'1','attempts_allowed'=>0,'limit_attempts_allowed'=>'1','pass_is_required'=>0,'passing_grade'=>81,'max_questions_for_answer'=>$nq,'quiz_auto_start'=>0,'question_layout_view'=>'single_question','questions_order'=>'rand','hide_question_number_overview'=>0,'short_answer_characters_limit'=>200,'open_ended_answer_characters_limit'=>500],$opt));
  $old=$wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$p}tutor_quiz_questions WHERE quiz_id=%d",$qid));
  if($old){ $in=implode(',',array_map('intval',$old)); $wpdb->query("DELETE FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id IN ($in)"); $wpdb->query("DELETE FROM {$p}tutor_quiz_questions WHERE question_id IN ($in)"); }
  $o=0;
  foreach($qs as $q){ $o++;
    $wpdb->insert($p.'tutor_quiz_questions',['quiz_id'=>$qid,'question_title'=>$q['q'],'question_description'=>$q['desc']??'','answer_explanation'=>$q['erkl']??'','question_type'=>'multiple_choice','question_mark'=>1,'question_settings'=>maybe_serialize(['question_type'=>'multiple_choice','question_mark'=>1,'answer_required'=>1,'randomize_question'=>$fixed?'0':'1','show_question_mark'=>0,'has_multiple_correct_answer'=>0,'is_image_answer'=>0]),'question_order'=>$o]);
    $questid=(int)$wpdb->insert_id; $ao=0;
    foreach($q['opts'] as $a){ $ao++; $wpdb->insert($p.'tutor_quiz_question_answers',['belongs_question_id'=>$questid,'belongs_question_type'=>'multiple_choice','answer_title'=>$a[0],'is_correct'=>$a[1]?1:0,'image_id'=>0,'answer_two_gap_match'=>'','answer_view_format'=>'text','answer_order'=>$ao]); }
  }
  return [$qid,$o];
}

// Ein Modul (1..8) einer Sprache anlegen/aktualisieren
function ccapd_import_module($lang,$n){
  $D=ccapd_data($lang); $m=null; foreach($D['modules'] as $x){ if((int)$x['n']===(int)$n) $m=$x; }
  if(!$m) return ['fehler'=>"modul $n fehlt"];
  $course=ccapd_course($lang);
  $topic=tr_post('topics',$m['src'],$lang,['post_title'=>$m['title'],'post_parent'=>$course,'menu_order'=>(int)$n,'post_content'=>$m['summary']]);
  $out=['modul'=>$n,'topic'=>$topic,'lektionen'=>[],'quiz'=>[]]; $order=0;
  foreach($m['items'] as $it){
    if($it['type']==='lesson'){
      $lid=tr_post('lesson',$it['src'],$lang,['post_title'=>wp_strip_all_tags($it['title']),'post_parent'=>$topic,'menu_order'=>$order++,'post_content'=>$it['html']]);
      update_post_meta($lid,'_tutor_course_id_for_lesson',$course); $out['lektionen'][]=$lid;
    } else {
      [$qid,$nq]=ccapd_quiz($it['src'],$lang,$it['title'],$topic,$order++,$it['qs'],$it['desc'],$it['opt']?:[],!empty($it['fixed']));
      $out['quiz'][]=[$qid,$nq];
    }
  }
  return $out;
}

// Liste aller angelegten IDs
function ccapd_ids(){
  $r=[];
  foreach(['de','fr'] as $l){
    $c=tr_find('ccapd-course',$l); $e=['kurs'=>$c,'module'=>[]];
    foreach(ccapd_data($l)['modules'] as $m){
      $t=tr_find($m['src'],$l); $x=['n'=>$m['n'],'thema'=>$t,'lektionen'=>[],'quizze'=>[]];
      foreach($m['items'] as $it){ $id=tr_find($it['src'],$l); if($it['type']==='lesson') $x['lektionen'][]=$id; else $x['quizze'][]=$id; }
      $e['module'][]=$x;
    }
    $r[$l]=$e;
  }
  return $r;
}
