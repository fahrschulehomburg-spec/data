<?php
// TheorieRoute Lkw C: legt aus uploads/tr-import/c-{de,fr}.json den Tutor-Kurs C an. Idempotent ueber _tr_src (Praefix c-) / _tr_lang.
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';

function trc_ui($lang){
  $U = [
    'de' => ['course'=>'TheorieRoute: Online-Theoriekurs Lkw C/CE','overview'=>'Überblick & Lernziele','merke'=>'Merksätze','goals'=>'Das lernst du in diesem Modul','module'=>'Modul','quiz'=>'Quiz','quizdesc'=>'%d Fragen mit Erklärung nach jeder Antwort.','sim'=>'Prüfungssimulation','simdesc'=>'50 zufällige Fragen aus allen Kapiteln. Bestanden ab 41 richtigen Antworten (82 %).'],
    'fr' => ['course'=>'TheorieRoute : cours de théorie en ligne – camion C/CE','overview'=>'Aperçu et objectifs','merke'=>'À retenir','goals'=>'Ce que vous apprenez dans ce module','module'=>'Module','quiz'=>'Quiz','quizdesc'=>'%d questions avec explication après chaque réponse.','sim'=>'Simulation d’examen','simdesc'=>'50 questions au hasard dans tous les chapitres. Réussi à partir de 41 bonnes réponses (82 %).'],
  ];
  return $U[$lang];
}
function trc_abs($html){ return str_replace(['src="/wp-content','href="/wp-content'], ['src="https://www.fahrschule-homburg.be/wp-content','href="https://www.fahrschule-homburg.be/wp-content'], (string)$html); }
function trc_data($lang){ static $C=[]; if(!isset($C[$lang])){ $u=wp_upload_dir(); $C[$lang]=json_decode(file_get_contents($u['basedir']."/tr-import/c-$lang.json"),true); } return $C[$lang]; }
function trc_anims($lang){ static $C=[]; if(!isset($C[$lang])){ $u=wp_upload_dir(); $f=$u['basedir']."/tr-import/c-anims-$lang.json"; $C[$lang]=is_readable($f)?json_decode(file_get_contents($f),true):[]; } return $C[$lang]; }
function trc_course($lang){
  $ui=trc_ui($lang);
  $id=tr_post('courses','c-course',$lang,['post_title'=>$ui['course'],'post_status'=>'private','post_content'=>'']);
  update_post_meta($id,'_tutor_course_product_id',41667); update_post_meta($id,'_tutor_course_price_type','free');
  return $id;
}
function trc_lesson($src,$lang,$title,$topic,$course,$order,$html){
  $lid=tr_post('lesson',$src,$lang,['post_title'=>wp_strip_all_tags($title),'post_parent'=>$topic,'menu_order'=>$order,'post_content'=>$html]);
  update_post_meta($lid,'_tutor_course_id_for_lesson',$course); return $lid;
}
function trc_section_html($s,$anims){
  $h='';
  if(!empty($s['sub'])) $h.='<p><em>'.wp_kses_post($s['sub'])."</em></p>\n";
  if(!empty($anims[$s['heading']])) $h.='<div class="tr-anim">'.$anims[$s['heading']]['svg'].'</div><p class="tr-cap">'.wp_kses_post($anims[$s['heading']]['cap'])."</p>\n";
  if(!empty($s['photo']['src'])) $h.='<figure class="tr-foto"><img src="'.esc_url(trc_abs_url($s['photo']['src'])).'" alt="'.esc_attr($s['photo']['alt']??'').'" loading="lazy" style="max-width:100%;height:auto;border-radius:10px;" />'.(!empty($s['photo']['cap'])?'<figcaption>'.wp_kses_post($s['photo']['cap']).'</figcaption>':'')."</figure>\n";
  foreach($s['cards'] as $k){
    if(!empty($k['h'])) $h.='<h3>'.wp_kses_post($k['h'])."</h3>\n";
    if(!empty($k['icon'])) $h.='<div class="tr-icon">'.trc_abs($k['icon'])."</div>\n";
    if(!empty($k['p'])) $h.='<p>'.wp_kses_post($k['p'])."</p>\n";
  }
  return $h;
}
function trc_abs_url($u){ return strpos($u,'/wp-content')===0 ? 'https://www.fahrschule-homburg.be'.$u : $u; }
function trc_quiz($src,$lang,$title,$topic,$order,$qs,$opt=[]){
  global $wpdb; $p=$wpdb->prefix; $ui=trc_ui($lang); $nq=count($qs);
  $qid=tr_post('tutor_quiz',$src,$lang,['post_title'=>$title,'post_parent'=>$topic,'menu_order'=>$order,'post_content'=>$opt['desc']??sprintf($ui['quizdesc'],$nq)]);
  update_post_meta($qid,'tutor_quiz_option',array_merge(['time_limit'=>['time_value'=>0,'time_type'=>'minutes'],'hide_quiz_time_display'=>0,'feedback_mode'=>'reveal','enable_answer_reveal'=>'1','attempts_allowed'=>0,'limit_attempts_allowed'=>'1','pass_is_required'=>0,'passing_grade'=>81,'max_questions_for_answer'=>$nq,'quiz_auto_start'=>0,'question_layout_view'=>'single_question','questions_order'=>'rand','hide_question_number_overview'=>0,'short_answer_characters_limit'=>200,'open_ended_answer_characters_limit'=>500],$opt['option']??[]));
  $old=$wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$p}tutor_quiz_questions WHERE quiz_id=%d",$qid));
  if($old){ $in=implode(',',array_map('intval',$old)); $wpdb->query("DELETE FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id IN ($in)"); $wpdb->query("DELETE FROM {$p}tutor_quiz_questions WHERE question_id IN ($in)"); }
  $o=0;
  foreach($qs as $q){ $o++;
    $wpdb->insert($p.'tutor_quiz_questions',['quiz_id'=>$qid,'question_title'=>$q['q'],'question_description'=>$q['desc']??'','answer_explanation'=>$q['erkl']??'','question_type'=>'multiple_choice','question_mark'=>1,'question_settings'=>maybe_serialize(['question_type'=>'multiple_choice','question_mark'=>1,'answer_required'=>1,'randomize_question'=>'1','show_question_mark'=>0,'has_multiple_correct_answer'=>0,'is_image_answer'=>0]),'question_order'=>$o]);
    $questid=(int)$wpdb->insert_id; $ao=0;
    foreach($q['opts'] as $opt1){ $ao++; $wpdb->insert($p.'tutor_quiz_question_answers',['belongs_question_id'=>$questid,'belongs_question_type'=>'multiple_choice','answer_title'=>$opt1[0],'is_correct'=>$opt1[1]?1:0,'image_id'=>0,'answer_two_gap_match'=>'','answer_view_format'=>'text','answer_order'=>$ao]); }
  }
  return [$qid,$o];
}
// n = 2..17 Inhaltsmodule (Anzeige n-1)
function trc_import_module($lang,$n){
  $D=trc_data($lang); $m=$D['MODULES'][(string)$n]; $ui=trc_ui($lang); $an=trc_anims($lang);
  $course=trc_course($lang); $out=['modul'=>$n,'lektionen'=>0,'fragen'=>0];
  $topic=tr_post('topics',"c-m$n",$lang,['post_title'=>$ui['module'].' '.($n-1).': '.$m['title'],'post_parent'=>$course,'menu_order'=>$n,'post_content'=>'<p>'.wp_kses_post($m['intro']).'</p>']);
  $order=0;
  $h=''; if(!empty($m['icon'])) $h.='<div class="tr-foto">'.trc_abs($m['icon']).'</div>';
  $h.='<p><em>'.wp_kses_post($m['intro'])."</em></p>\n<h3>".esc_html($ui['goals'])."</h3>\n<ul>"; foreach($m['goals'] as $g) $h.='<li>'.wp_kses_post($g).'</li>'; $h.='</ul>';
  trc_lesson("c-m$n-l0",$lang,$ui['overview'],$topic,$course,$order++,$h); $out['lektionen']++;
  foreach($m['sections'] as $i=>$s){ trc_lesson("c-m$n-l".($i+1),$lang,$s['heading'],$topic,$course,$order++,trc_section_html($s,$an)); $out['lektionen']++; }
  if(!empty($m['merke'])){ $h='<ul>'; foreach($m['merke'] as $x) $h.='<li>'.wp_kses_post($x).'</li>'; $h.='</ul>'; trc_lesson("c-m$n-lm",$lang,$ui['merke'],$topic,$course,$order++,$h); $out['lektionen']++; }
  if(!empty($m['quiz'])){ [$qid,$nq]=trc_quiz("c-m$n-quiz",$lang,$ui['quiz'].' '.$ui['module'].' '.($n-1).': '.$m['title'],$topic,$order++,trc_quiz_items($lang,$n)); $out['fragen']=$nq; }
  return $out;
}
// Fragen eines Moduls inkl. Erklaerungen aus c-erkl-{lang}.json (Schluessel "n-i")
function trc_quiz_items($lang,$n){
  static $E=[]; if(!isset($E[$lang])){ $u=wp_upload_dir(); $f=$u['basedir']."/tr-import/c-erkl-$lang.json"; $E[$lang]=is_readable($f)?json_decode(file_get_contents($f),true):[]; }
  $D=trc_data($lang); $r=[];
  foreach($D['MODULES'][(string)$n]['quiz'] as $i=>$q){ $q['erkl']=$E[$lang]["$n-$i"]??''; $r[]=$q; }
  return $r;
}
// Modul 18: Pruefungssimulation (alle Fragen, 50 zufaellig)
function trc_import_sim($lang){
  $D=trc_data($lang); $m=$D['MODULES']['18']; $ui=trc_ui($lang); $course=trc_course($lang);
  $topic=tr_post('topics','c-m18',$lang,['post_title'=>$ui['module'].' 17: '.$m['title'],'post_parent'=>$course,'menu_order'=>18,'post_content'=>'<p>'.wp_kses_post($m['intro']).'</p>']);
  $h='<p><em>'.wp_kses_post($m['intro'])."</em></p>\n"; foreach($m['sections'] as $s) $h.=trc_section_html($s,[]);
  trc_lesson('c-m18-l0',$lang,$ui['overview'],$topic,$course,0,$h);
  $all=[]; for($n=2;$n<=17;$n++) foreach(trc_quiz_items($lang,$n) as $q) $all[]=$q;
  [$qid,$nq]=trc_quiz('c-m18-quiz',$lang,$ui['sim'],$topic,1,$all,['desc'=>$ui['simdesc'],'option'=>['max_questions_for_answer'=>50,'passing_grade'=>82,'feedback_mode'=>'default','enable_answer_reveal'=>'0']]);
  return ['sim_fragen'=>$nq];
}
// Modul 19 (Anzeige 18): Verkehrszeichen, kopiert aus dem B-Kurs (Thema menu_order 14)
function trc_import_signs($lang){
  global $wpdb; $p=$wpdb->prefix; $bcourse=['de'=>42089,'fr'=>42691][$lang]; $course=trc_course($lang);
  $bt=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_type='topics' AND post_parent=%d AND menu_order=14 AND post_status<>'trash'",$bcourse));
  $title=preg_replace('/^(Modul|Module)\s+\d+\s*:/u','$1 18:',$bt->post_title);
  $topic=tr_post('topics','c-m19',$lang,['post_title'=>$title,'post_parent'=>$course,'menu_order'=>19,'post_content'=>$bt->post_content]);
  $kids=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE post_parent=%d AND post_status<>'trash' ORDER BY menu_order,ID",$bt->ID));
  $n=0;$fq=0;
  foreach($kids as $k){
    if($k->post_type==='lesson'){ trc_lesson('c-m19-'.$k->menu_order.'-'.$n,$lang,$k->post_title,$topic,$course,$k->menu_order,$k->post_content); $n++; }
    elseif($k->post_type==='tutor_quiz'){
      $qs=[]; foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}tutor_quiz_questions WHERE quiz_id=%d ORDER BY question_order",$k->ID)) as $q){ $opts=[]; foreach($wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id=%d ORDER BY answer_order",$q->question_id)) as $a) $opts[]=[$a->answer_title,(int)$a->is_correct===1]; $qs[]=['q'=>$q->question_title,'desc'=>$q->question_description,'erkl'=>$q->answer_explanation,'opts'=>$opts]; }
      $title2=preg_replace('/(Modul|Module)\s+\d+\s*:/u','$1 18:',$k->post_title);
      [$qid,$fq]=trc_quiz('c-m19-quiz',$lang,$title2,$topic,$k->menu_order,$qs);
    }
  }
  return ['zeichen_lektionen'=>$n,'zeichen_fragen'=>$fq];
}
