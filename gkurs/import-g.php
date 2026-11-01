<?php
// TheorieRoute Fuehrerschein G: legt aus gkurs/g-{de,fr}.json (GitHub, SHA-URL) den Tutor-Kurs an (privat, WPML de<->fr).
// Idempotent ueber _tr_src (Praefix g-) / _tr_lang; nutzt tr_post()/tr_find() aus tr-import/import.php.
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';

// Kursdaten werden NICHT in uploads abgelegt: $GLOBALS['GK_SRC'][lang] = SHA-URL im GitHub-Repo (wird nur in den Speicher geladen)
function gk_data($lang){ static $C=[]; if(!isset($C[$lang])){ $r=wp_remote_get($GLOBALS['GK_SRC'][$lang],['timeout'=>60]); $C[$lang]=json_decode(wp_remote_retrieve_body($r),true); } return $C[$lang]; }

// Kursbild: vorhandene Datei fh-kurse/b/img/sit-875d309b.jpg als Anhang registrieren (einmalig, ohne Kopie)
function gk_thumb(){
  global $wpdb; $rel='fh-kurse/b/img/sit-875d309b.jpg';
  $id=(int)$wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_gkurs_thumb' LIMIT 1");
  if($id && get_post($id)) return $id;
  $u=wp_upload_dir(); $file=$u['basedir'].'/'.$rel; if(!is_readable($file)) return 0;
  require_once ABSPATH.'wp-admin/includes/image.php';
  $id=wp_insert_attachment(['post_mime_type'=>'image/jpeg','post_title'=>'TheorieRoute Fuehrerschein G Kursbild','post_status'=>'inherit','guid'=>$u['baseurl'].'/'.$rel],$file);
  update_post_meta($id,'_wp_attached_file',$rel);
  add_filter('intermediate_image_sizes_advanced','__return_empty_array');
  wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$file));
  remove_filter('intermediate_image_sizes_advanced','__return_empty_array');
  update_post_meta($id,'_gkurs_thumb','sit-875d309b.jpg');
  return $id;
}

function gk_course($lang){
  $D=gk_data($lang); $c=$D['course'];
  $id=tr_post('courses','g-course',$lang,['post_title'=>$c['title'],'post_status'=>'private','post_content'=>$c['content'],'post_excerpt'=>$c['excerpt']]);
  update_post_meta($id,'_tutor_course_price_type','free');
  update_post_meta($id,'_tutor_course_level','all_levels');
  update_post_meta($id,'_tutor_course_benefits',$c['benefits']);
  $t=gk_thumb(); if($t) set_post_thumbnail($id,$t);
  return $id;
}

function gk_quiz($src,$lang,$title,$topic,$order,$qs,$desc,$opt=[]){
  global $wpdb; $p=$wpdb->prefix; $nq=count($qs);
  $qid=tr_post('tutor_quiz',$src,$lang,['post_title'=>$title,'post_parent'=>$topic,'menu_order'=>$order,'post_content'=>$desc]);
  update_post_meta($qid,'tutor_quiz_option',array_merge(['time_limit'=>['time_value'=>0,'time_type'=>'minutes'],'hide_quiz_time_display'=>0,'feedback_mode'=>'reveal','enable_answer_reveal'=>'1','attempts_allowed'=>0,'limit_attempts_allowed'=>'1','pass_is_required'=>0,'passing_grade'=>81,'max_questions_for_answer'=>$nq,'quiz_auto_start'=>0,'question_layout_view'=>'single_question','questions_order'=>'rand','hide_question_number_overview'=>0,'short_answer_characters_limit'=>200,'open_ended_answer_characters_limit'=>500],$opt));
  $old=$wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$p}tutor_quiz_questions WHERE quiz_id=%d",$qid));
  if($old){ $in=implode(',',array_map('intval',$old)); $wpdb->query("DELETE FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id IN ($in)"); $wpdb->query("DELETE FROM {$p}tutor_quiz_questions WHERE question_id IN ($in)"); }
  $o=0;
  foreach($qs as $q){ $o++;
    $wpdb->insert($p.'tutor_quiz_questions',['quiz_id'=>$qid,'question_title'=>$q['q'],'question_description'=>$q['desc']??'','answer_explanation'=>$q['erkl']??'','question_type'=>'multiple_choice','question_mark'=>1,'question_settings'=>maybe_serialize(['question_type'=>'multiple_choice','question_mark'=>1,'answer_required'=>1,'randomize_question'=>'1','show_question_mark'=>0,'has_multiple_correct_answer'=>0,'is_image_answer'=>0]),'question_order'=>$o]);
    $questid=(int)$wpdb->insert_id; $ao=0;
    foreach($q['opts'] as $a){ $ao++; $wpdb->insert($p.'tutor_quiz_question_answers',['belongs_question_id'=>$questid,'belongs_question_type'=>'multiple_choice','answer_title'=>$a[0],'is_correct'=>$a[1]?1:0,'image_id'=>0,'answer_two_gap_match'=>'','answer_view_format'=>'text','answer_order'=>$ao]); }
  }
  return [$qid,$o];
}

function gk_import_module($lang,$n){
  $D=gk_data($lang); $m=null; foreach($D['modules'] as $x){ if((int)$x['n']===(int)$n) $m=$x; }
  if(!$m) return ['fehler'=>"modul $n fehlt"];
  $course=gk_course($lang);
  $topic=tr_post('topics',$m['src'],$lang,['post_title'=>$m['title'],'post_parent'=>$course,'menu_order'=>(int)$n,'post_content'=>$m['summary']]);
  $out=['modul'=>$n,'topic'=>$topic,'lektionen'=>[],'quiz'=>[]]; $order=0;
  foreach($m['items'] as $it){
    if($it['type']==='lesson'){
      $lid=tr_post('lesson',$it['src'],$lang,['post_title'=>wp_strip_all_tags($it['title']),'post_parent'=>$topic,'menu_order'=>$order++,'post_content'=>$it['html']]);
      update_post_meta($lid,'_tutor_course_id_for_lesson',$course); $out['lektionen'][]=$lid;
    } else {
      [$qid,$nq]=gk_quiz($it['src'],$lang,$it['title'],$topic,$order++,$it['qs'],$it['desc'],$it['opt']?:[]);
      $out['quiz'][]=[$qid,$nq];
    }
  }
  return $out;
}

function gk_ids(){
  $r=[];
  foreach(['de','fr'] as $l){
    $c=tr_find('g-course',$l); $e=['kurs'=>$c,'module'=>[]];
    foreach(gk_data($l)['modules'] as $m){
      $t=tr_find($m['src'],$l); $x=['n'=>$m['n'],'thema'=>$t,'lektionen'=>[],'quizze'=>[]];
      foreach($m['items'] as $it){ $id=tr_find($it['src'],$l); if($it['type']==='lesson') $x['lektionen'][]=$id; else $x['quizze'][]=$id; }
      $e['module'][]=$x;
    }
    $r[$l]=$e;
  }
  return $r;
}
