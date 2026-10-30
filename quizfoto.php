<?php
// Quizfragen -> Kachelfotos. Einbinden nach kachelbild.php
function qf_kacheln($cid,$mid){
  // liefert Liste [k, h3, img-tag|null, text]
  $out=[];$k=0;
  foreach(trk_lessons($cid,$mid) as $l){
    $c=$l->post_content;
    preg_match_all('/<h3[^>]*>(.*?)<\/h3>/s',$c,$mm,PREG_OFFSET_CAPTURE);
    $n=count($mm[0]);
    for($i=0;$i<$n;$i++){
      $st=$mm[0][$i][1]+strlen($mm[0][$i][0]);
      $en=$i+1<$n?$mm[0][$i+1][1]:strlen($c);
      $seg=substr($c,$st,$en-$st);
      $img=null;
      if(preg_match('/<div class="tr-icon tr-kachel">\s*(<img[^>]+\.jpg"[^>]*>)/',$seg,$im)) $img=$im[1];
      $txt=trim(strip_tags(preg_replace('/<div class="tr-icon.*?<\/div>/s','',$seg)));
      $out[]=['k'=>$k,'lid'=>$l->ID,'h3'=>trim(strip_tags($mm[1][$i][0])),'img'=>$img,'txt'=>$txt];
      $k++;
    }
  }
  return $out;
}
function qf_questions($cid){
  global $wpdb;$r=[];
  $tops=get_posts(['post_type'=>'topics','post_parent'=>$cid,'numberposts'=>-1,'orderby'=>'menu_order','order'=>'ASC','post_status'=>'any']);
  foreach($tops as $t){
    $qz=get_posts(['post_type'=>'tutor_quiz','post_parent'=>$t->ID,'numberposts'=>-1,'post_status'=>'any']);
    foreach($qz as $q){
      $qs=$wpdb->get_results("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE quiz_id={$q->ID} ORDER BY question_order ASC, question_id ASC");
      foreach($qs as $i=>$x){
        $ans=$wpdb->get_results("SELECT answer_title,is_correct FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id={$x->question_id}");
        $r[]=['mid'=>(int)$t->menu_order,'i'=>$i,'id'=>(int)$x->question_id,'title'=>$x->question_title,'desc'=>$x->question_description,'expl'=>$x->answer_explanation,'ans'=>$ans];
      }
    }
  }
  return $r;
}
function qf_tok($s){
  $s=mb_strtolower(html_entity_decode(strip_tags($s)));
  $s=preg_replace('/[^\p{L}\p{N}]+/u',' ',$s);
  $stop=array_flip(explode(' ','eine einer einem einen eines nicht darf muss müssen dürfen wenn dann oder auch noch nach beim bei dem den der die das des und mit von für vor auf aus ist sind wird werden welche welcher welches was wer wie wo man sie ihr ihre sich zum zur über unter durch kann können soll sollen haben hat gilt immer nur ganz mehr weniger diese dieser dieses jede jeder jedes alle allen etwa etwas schon bitte richtig falsch stimmt aussage aussagen folgende situation hier dort darauf also kein keine keinen ohne gegen bis seit zwischen'));
  $t=[];foreach(explode(' ',$s) as $w){ if(mb_strlen($w)<4||isset($stop[$w])) continue; $t[mb_substr($w,0,6)]=1; }
  return $t;
}
function qf_score($qt,$kt){ $s=0; foreach($qt as $w=>$wt){ if(isset($kt[$w])) $s+=$wt*$kt[$w]; } return $s; }
