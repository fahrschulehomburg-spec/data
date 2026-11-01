<?php
if (!defined('ABSPATH')) exit;
function aud_log($what,$data){ $u=wp_upload_dir(); file_put_contents($u['basedir'].'/tr-import/backup-audit-am-2026-11-02.jsonl', json_encode(['t'=>date('c'),'w'=>$what,'d'=>$data],JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND); }
function aud_merk($t,$s){ return '<div class="tr-icon tr-kachel"><div class="tr-merk" style="background:#16202B;color:#fff;border-left:6px solid #F5B700;border-radius:10px;padding:14px 18px;margin:6px 0 10px;max-width:560px;"><div style="font-size:1.35em;font-weight:700;line-height:1.25;">'.esc_html($t).'</div><div style="opacity:.85;margin-top:4px;font-size:.95em;">'.esc_html($s).'</div></div></div>'; }
function aud_img($src,$alt){ return '<div class="tr-icon tr-kachel"><img src="https://www.fahrschule-homburg.be/wp-content/uploads/'.$src.'" alt="'.esc_attr($alt).'" loading="lazy" style="display:block;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;border-radius:8px;margin-bottom:10px;"></div>'; }
function aud_parts($lid){ global $wpdb; $c=$wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d",$lid)); return preg_split('/(?=<h3)/',$c); }
function aud_put($lid,$i,$html,$why){ global $wpdb; $p=aud_parts($lid); if(!isset($p[$i])) return "kachel $i fehlt in $lid"; aud_log('kachel',['lid'=>$lid,'i'=>$i,'why'=>$why,'alt'=>$p[$i]]); $p[$i]=$html; $wpdb->update($wpdb->posts,['post_content'=>implode('',$p)],['ID'=>$lid]); clean_post_cache($lid); return 'ok'; }
function aud_apply($ops){ global $wpdb; $res=[];
  foreach($ops as $n=>$o){ $t=$o['op']; $k=$o['lid'].'#'.($o['i']??'').' '.$t;
    if($t=='str'){ $p=aud_parts($o['lid']); $part=$p[$o['i']]??null; if($part===null){$res[$k]='kachel fehlt';continue;} $c=substr_count($part,$o['from']); if($c!=1){$res[$k]="from count=$c";continue;} $res[$k]=aud_put($o['lid'],$o['i'],str_replace($o['from'],$o['to'],$part),'str'); }
    elseif($t=='merk'){ $p=aud_parts($o['lid']); $part=$p[$o['i']]??null; if($part===null){$res[$k]='kachel fehlt';continue;} $m=aud_merk($o['t'],$o['s']);
      if(preg_match('/<div class="tr-icon[^"]*">\s*(<img[^>]*>\s*)+<\/div>/',$part)){ $new=preg_replace('/<div class="tr-icon[^"]*">\s*(<img[^>]*>\s*)+<\/div>/',$m,$part,1); }
      elseif(preg_match('/<div class="tr-icon tr-kachel"><div class="tr-merk".*?<\/div><\/div><\/div>/s',$part)){ $res[$k]='hat schon merk'; continue; }
      elseif(preg_match('/<\/h3>\s*/',$part)){ $new=preg_replace('/(<\/h3>\s*)/',"$1".$m."\n",$part,1); }
      else { $res[$k]='kein h3'; continue; }
      $res[$k]=aud_put($o['lid'],$o['i'],$new,'merk'); }
    elseif($t=='img'){ $p=aud_parts($o['lid']); $part=$p[$o['i']]??null; if($part===null){$res[$k]='kachel fehlt';continue;}
      if(preg_match('/<div class="tr-icon[^"]*">\s*<img[^>]*>\s*<\/div>/',$part)){ $new=preg_replace('/<div class="tr-icon[^"]*">\s*<img[^>]*>\s*<\/div>/',aud_img($o['src'],$o['alt']),$part,1); }
      elseif(preg_match('/<figure class="tr-foto"><img src="[^"]+" alt="[^"]*"/',$part)){ $new=preg_replace('/(<figure class="tr-foto"><img src=")[^"]+(" alt=")[^"]*(")/','${1}https://www.fahrschule-homburg.be/wp-content/uploads/'.$o['src'].'${2}'.esc_attr($o['alt']).'${3}',$part,1); }
      else { $res[$k]='kein img'; continue; }
      $res[$k]=aud_put($o['lid'],$o['i'],$new,'img'); }
    elseif($t=='swapimg'){ $p=aud_parts($o['lid']); $part=$p[$o['i']]??null; if($part===null){$res[$k]='kachel fehlt';continue;} $c=substr_count($part,$o['from']); if($c!=1){$res[$k]="src count=$c";continue;} $new=str_replace($o['from'],$o['to'],$part); if(isset($o['alt_from'])) $new=str_replace($o['alt_from'],$o['alt_to'],$new); $res[$k]=aud_put($o['lid'],$o['i'],$new,'swapimg'); }
    elseif($t=='title'){ $old=get_post($o['lid'])->post_title; aud_log('title',['lid'=>$o['lid'],'alt'=>$old]); $wpdb->update($wpdb->posts,['post_title'=>$o['title']],['ID'=>$o['lid']]); clean_post_cache($o['lid']); $res['title '.$o['lid']]='ok'; }
    elseif($t=='q'){ foreach($o['ids'] as $qid){ $q=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id=%d",$qid),ARRAY_A); if(!$q){$res["q$qid"]='fehlt';continue;} $ans=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id=%d ORDER BY answer_order",$qid),ARRAY_A); aud_log('frage',['q'=>$q,'a'=>$ans]); $up=[];
        if(isset($o['title'])) $up['question_title']=$o['title']; if(isset($o['explanation'])) $up['answer_explanation']=$o['explanation'];
        if(isset($o['desc_src'])){ $d=$q['question_description']; if($o['desc_src']===''){ $d=''; } else { $d=preg_replace('/src="[^"]+"/','src="https://www.fahrschule-homburg.be/wp-content/uploads/'.$o['desc_src'].'"',$d,1); $d=preg_replace('/alt="[^"]*"/','alt="'.esc_attr($o['desc_alt']).'"',$d,1);} $up['question_description']=$d; }
        if(isset($o['expl_from'])){ $e=$q['answer_explanation']; if(substr_count($e,$o['expl_from'])!=1){$res["q$qid"]='expl_from nicht 1x';continue;} $up['answer_explanation']=str_replace($o['expl_from'],$o['expl_to'],$e); }
        if($up) $wpdb->update($wpdb->prefix.'tutor_quiz_questions',$up,['question_id'=>$qid]);
        if(isset($o['answers'])){ if(count($o['answers'])!=count($ans)){$res["q$qid"]='antwortzahl';continue;} foreach($ans as $j=>$a){ $wpdb->update($wpdb->prefix.'tutor_quiz_question_answers',['answer_title'=>$o['answers'][$j]['t'],'is_correct'=>$o['answers'][$j]['ok']?1:0],['answer_id'=>$a['answer_id']]); } }
        $res["q$qid"]='ok'; } }
  }
  return $res; }
