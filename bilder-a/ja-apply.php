<?php
require_once __DIR__.'/fixhelper.php';
function ja_qlang($quiz){ $p=trb_pos($quiz); if($p) return $p[0]; $s=array_search((int)$quiz,TRB_SIM); return $s===false?null:$s; }
function ja_swap($html,$old,$new,$alt,$o){
  $base='https://www.fahrschule-homburg.be/wp-content/uploads/fh-kurse/b/img/';
  $q=preg_quote($old,'/'); $cnt=0;
  $photo='display:block;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;border-radius:8px;margin-bottom:10px;';
  $mk=function($tag) use($base,$new,$alt,$o,$photo){
    $tag=preg_replace('/src="[^"]*"/','src="'.$base.$new.'"',$tag,1);
    $tag=preg_replace('/alt="[^"]*"/','alt="'.esc_attr($alt).'"',$tag,1);
    if(!empty($o['h'])) $tag=preg_replace('/style="[^"]*"/','style="height:'.(int)$o['h'].'px;width:auto;"',$tag,1);
    if(!empty($o['photo'])) $tag=preg_replace('/style="[^"]*"/','style="'.$photo.'"',$tag,1);
    return $tag; };
  if(!empty($o['photo'])){
    $html=preg_replace_callback('/<div style="display:flex[^"]*">\s*(<img\b[^>]*src="[^"]*\/'.$q.'"[^>]*>)\s*<\/div>/',function($m) use($mk){return $mk($m[1]);},$html,-1,$c1); $cnt+=$c1;
  }
  $html=preg_replace_callback('/<img\b[^>]*src="[^"]*\/'.$q.'"[^>]*>/',function($m) use($mk){return $mk($m[0]);},$html,-1,$c2); $cnt+=$c2;
  return [$html,$cnt];
}
function ja_merk($big,$small,$rtl){ $side=$rtl?'border-right':'border-left';
  return '<div class="tr-merk" style="background:#16202B;color:#fff;'.$side.':6px solid #F5B700;border-radius:10px;padding:14px 18px;margin:6px 0 10px;max-width:560px;"><div style="font-size:1.35em;font-weight:700;line-height:1.25;">'.esc_html($big).'</div><div style="opacity:.85;margin-top:4px;font-size:.95em;">'.esc_html($small).'</div></div>'; }
function ja_run($ops,$dry=true){ global $wpdb; $R=[];
  foreach($ops as $op){ $r=$op['nr'].' '.$op['t'].' '; $o=['h'=>$op['h']??0,'photo'=>$op['photo']??0];
    if($op['t']=='k'||$op['t']=='m'){ $eq=trb_lesson_eq($op['lid']);
      foreach($eq as $l=>$id){ $k=trb_get_kachel($id,$op['i']); preg_match('/<h3[^>]*>(.*?)<\/h3>/s',(string)$k,$h);
        if($op['t']=='k'){ [$n,$c]=ja_swap($k,$op['old'],$op['new'],$op['alt'][$l],$o); }
        else { $c=0; $q=preg_quote($op['old'],'/'); $n=preg_replace('/<div class="tr-icon tr-kachel"><img\b[^>]*\/'.$q.'"[^>]*><\/div>/','<div class="tr-icon tr-kachel">'.ja_merk($op['merk'][$l][0],$op['merk'][$l][1],$l=='ar').'</div>',$k,1,$c); }
        $r.="$l:$id/".$op['i']."=$c [".mb_substr($h[1]??'',0,30)."] ";
        if($c && !$dry) trb_put_kachel($id,$op['i'],$n); } }
    elseif($op['t']=='q'){ $eq=trb_q_eq($op['qid']); $ids=[]; foreach(['de','fr','ar','th'] as $l) if($eq[$l]) $ids[]=[$l,$eq[$l]]; foreach($eq['sim'] as $l=>$s) if($s) $ids[]=[$l,$s];
      foreach($ids as [$l,$qid]){ $d=$wpdb->get_var($wpdb->prepare("SELECT question_description FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id=%d",$qid)); [$n,$c]=ja_swap($d,$op['old'],$op['new'],$op['alt'][$l],$o); $r.="$l:$qid=$c ";
        if($c && !$dry) trb_q_update($qid,['description'=>$n]); } }
    elseif($op['t']=='g'){ $like='%'.$wpdb->esc_like($op['old']).'%';
      foreach($wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status='publish'",$like)) as $pid){ $p=trb_pos($pid); if(!$p){ $r.="skipP$pid "; continue; } $l=$p[0];
        $cont=$wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d",$pid)); [$n,$c]=ja_swap($cont,$op['old'],$op['new'],$op['alt'][$l],$o); $r.="P$l:$pid=$c ";
        if($c && !$dry){ trb_log('ja-global',['id'=>$pid,'alt'=>$cont]); $wpdb->update($wpdb->posts,['post_content'=>$n],['ID'=>$pid]); clean_post_cache($pid); } }
      foreach($wpdb->get_results($wpdb->prepare("SELECT question_id,quiz_id,question_description FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_description LIKE %s",$like),ARRAY_A) as $q){ $l=ja_qlang($q['quiz_id']); if(!$l){ $r.='skipQ'.$q['question_id'].' '; continue; }
        [$n,$c]=ja_swap($q['question_description'],$op['old'],$op['new'],$op['alt'][$l],$o); $r.="Q$l:".$q['question_id']."=$c ";
        if($c && !$dry) trb_q_update($q['question_id'],['description'=>$n]); } }
    $R[]=$r; }
  return $R; }
