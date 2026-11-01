<?php
// TheorieRoute Motorrad & Moped: Korrekturrunde 2 – aktualisiert bestehende Lektionen/Quizze in place.
// Aendert NUR Inhalt/Titel (Lektionen, Themen, Quizze) und Quizfragen; post_status, WPML, Reihenfolge bleiben unberuehrt.
// Vorher wird jeder geaenderte Stand in tr-import/backup-am2-<datum>.jsonl gesichert.
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';

function am2_get($url){
  $r = wp_remote_get($url, ['timeout' => 60]);
  if (is_wp_error($r) || wp_remote_retrieve_response_code($r) != 200) return null;
  return json_decode(wp_remote_retrieve_body($r), true);
}

function am2_server_qs($qid){
  global $wpdb; $p = $wpdb->prefix; $out = [];
  $qs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}tutor_quiz_questions WHERE quiz_id=%d ORDER BY question_order", $qid), ARRAY_A);
  foreach ($qs as $q) {
    $as = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id=%d ORDER BY answer_order", $q['question_id']), ARRAY_A);
    $q['answers'] = $as; $out[] = $q;
  }
  return $out;
}

function am2_norm_qs($rows){
  $o = [];
  foreach ($rows as $q) { $a = []; foreach ($q['answers'] as $x) $a[] = [$x['answer_title'], (int)$x['is_correct'] === 1];
    $o[] = [$q['question_title'], (string)$q['question_description'], (string)$q['answer_explanation'], $a]; }
  return $o;
}
function am2_json_qs($qs){
  $o = [];
  foreach ($qs as $q) { $a = []; foreach ($q['opts'] as $x) $a[] = [$x[0], (bool)$x[1]]; $o[] = [$q['q'], (string)($q['desc'] ?? ''), (string)($q['erkl'] ?? ''), $a]; }
  return $o;
}

// $mode: 'dry' (nur vergleichen) | 'apply'
function am2_run($new, $old, $mode = 'dry', $backup = null){
  global $wpdb; $p = $wpdb->prefix;
  $rep = ['geaendert' => [], 'unveraendert' => 0, 'server_weicht_ab' => [], 'fehlt' => []];
  foreach (['de', 'fr'] as $lang) {
    $N = $new[$lang]; $O = $old[$lang];
    foreach ($N['modules'] as $mi => $m) {
      $om = $O['modules'][$mi];
      $items = [['type' => 'topic', 'src' => $m['src'], 'title' => $m['title'], 'html' => $m['summary']]];
      $oitems = [['type' => 'topic', 'src' => $om['src'], 'title' => $om['title'], 'html' => $om['summary']]];
      foreach ($m['items'] as $it) $items[] = $it;
      foreach ($om['items'] as $it) $oitems[] = $it;
      foreach ($items as $k => $it) {
        $ot = $oitems[$k] ?? null;
        if (!$ot || $ot['src'] !== $it['src']) { $rep['fehlt'][] = "$lang/{$it['src']} Struktur"; continue; }
        $id = tr_find($it['src'], $lang); if (!$id) { $rep['fehlt'][] = "$lang/{$it['src']}"; continue; }
        $post = get_post($id);
        if ($it['type'] === 'quiz') {
          $sq = am2_norm_qs(am2_server_qs($id)); $nq = am2_json_qs($it['qs']); $oq = am2_json_qs($ot['qs']);
          $opt = get_post_meta($id, 'tutor_quiz_option', true); if (!is_array($opt)) $opt = [];
          $newopt = array_merge($opt, $it['opt'] ?: []);
          if (strpos($it['src'], 'am-sim') !== 0) $newopt['max_questions_for_answer'] = count($it['qs']);
          $same_new = ($sq === $nq) && $post->post_content === $it['desc'] && $post->post_title === $it['title'] && $opt == $newopt;
          if ($same_new) { $rep['unveraendert']++; continue; }
          $same_old = ($sq === $oq) && $post->post_content === $ot['desc'] && $post->post_title === $ot['title'];
          if (!$same_old) $rep['server_weicht_ab'][] = "$lang/{$it['src']} ($id)" . ($sq === $nq ? ' [Fragen schon neu]' : '');
          $rep['geaendert'][] = "$lang/{$it['src']} ($id) Fragen " . count($sq) . '->' . count($nq);
          if ($mode === 'apply') {
            file_put_contents($backup, wp_json_encode(['t' => current_time('mysql'), 'id' => $id, 'src' => $it['src'], 'lang' => $lang, 'post_title' => $post->post_title, 'post_content' => $post->post_content, 'tutor_quiz_option' => $opt, 'questions' => am2_server_qs($id)]) . "\n", FILE_APPEND);
            $wpdb->update($wpdb->posts, ['post_title' => $it['title'], 'post_content' => $it['desc']], ['ID' => $id]);
            update_post_meta($id, 'tutor_quiz_option', $newopt);
            if ($sq !== $nq) {
              $old_ids = $wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$p}tutor_quiz_questions WHERE quiz_id=%d", $id));
              if ($old_ids) { $in = implode(',', array_map('intval', $old_ids)); $wpdb->query("DELETE FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id IN ($in)"); $wpdb->query("DELETE FROM {$p}tutor_quiz_questions WHERE question_id IN ($in)"); }
              $o = 0;
              foreach ($it['qs'] as $q) { $o++;
                $wpdb->insert($p . 'tutor_quiz_questions', ['quiz_id' => $id, 'question_title' => $q['q'], 'question_description' => $q['desc'] ?? '', 'answer_explanation' => $q['erkl'] ?? '', 'question_type' => 'multiple_choice', 'question_mark' => 1, 'question_settings' => maybe_serialize(['question_type' => 'multiple_choice', 'question_mark' => 1, 'answer_required' => 1, 'randomize_question' => '1', 'show_question_mark' => 0, 'has_multiple_correct_answer' => 0, 'is_image_answer' => 0]), 'question_order' => $o]);
                $qq = (int)$wpdb->insert_id; $ao = 0;
                foreach ($q['opts'] as $a) { $ao++; $wpdb->insert($p . 'tutor_quiz_question_answers', ['belongs_question_id' => $qq, 'belongs_question_type' => 'multiple_choice', 'answer_title' => $a[0], 'is_correct' => $a[1] ? 1 : 0, 'image_id' => 0, 'answer_two_gap_match' => '', 'answer_view_format' => 'text', 'answer_order' => $ao]); }
              }
            }
            clean_post_cache($id);
          }
        } else {
          $title = $it['type'] === 'lesson' ? wp_strip_all_tags($it['title']) : $it['title'];
          $otitle = $it['type'] === 'lesson' ? wp_strip_all_tags($ot['title']) : $ot['title'];
          if ($post->post_content === $it['html'] && $post->post_title === $title) { $rep['unveraendert']++; continue; }
          if (!($post->post_content === $ot['html'] && $post->post_title === $otitle)) $rep['server_weicht_ab'][] = "$lang/{$it['src']} ($id)";
          $rep['geaendert'][] = "$lang/{$it['src']} ($id)";
          if ($mode === 'apply') {
            file_put_contents($backup, wp_json_encode(['t' => current_time('mysql'), 'id' => $id, 'src' => $it['src'], 'lang' => $lang, 'post_title' => $post->post_title, 'post_content' => $post->post_content]) . "\n", FILE_APPEND);
            $wpdb->update($wpdb->posts, ['post_title' => $title, 'post_content' => $it['html']], ['ID' => $id]);
            clean_post_cache($id);
          }
        }
      }
    }
  }
  return $rep;
}
