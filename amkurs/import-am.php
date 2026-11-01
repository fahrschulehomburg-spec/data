<?php
// TheorieRoute Motorrad & Moped (A, A1, A2, AM): legt den Tutor-Kurs de/fr an.
// Daten werden zur Laufzeit per URL (GitHub-SHA) geladen und NICHT in uploads gespeichert.
// Idempotent ueber _tr_src (Praefix am-) / _tr_lang; WPML-Verknuepfung ueber tr_post()/tr_wpml() aus tr-import/import.php.
if (!defined('ABSPATH')) exit;
$__u = wp_upload_dir(); require_once $__u['basedir'] . '/tr-import/import.php';

function am_data($lang, $url = null){
  static $C = [];
  if ($url !== null && !isset($C[$lang])) {
    $r = wp_remote_get($url, ['timeout' => 60]);
    if (is_wp_error($r) || wp_remote_retrieve_response_code($r) != 200) return null;
    $C[$lang] = json_decode(wp_remote_retrieve_body($r), true);
  }
  return $C[$lang] ?? null;
}

function am_thumb(){
  global $wpdb;
  $id = (int)$wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_am_thumb' LIMIT 1");
  if ($id && get_post($id)) return $id;
  $u = wp_upload_dir(); $rel = 'fh-kurse/b/img/sit-13cd3f86.jpg'; $file = $u['basedir'] . '/' . $rel; if (!is_readable($file)) return 0;
  require_once ABSPATH . 'wp-admin/includes/image.php';
  $id = wp_insert_attachment(['post_mime_type' => 'image/jpeg', 'post_title' => 'TheorieRoute Motorrad & Moped Kursbild', 'post_status' => 'inherit', 'guid' => $u['baseurl'] . '/' . $rel], $file);
  update_post_meta($id, '_wp_attached_file', $rel);
  add_filter('intermediate_image_sizes_advanced', '__return_empty_array');
  wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file));
  remove_filter('intermediate_image_sizes_advanced', '__return_empty_array');
  update_post_meta($id, '_am_thumb', 'sit-13cd3f86.jpg');
  return $id;
}

function am_course($lang){
  $c = am_data($lang)['course'];
  $id = tr_post('courses', 'am-course', $lang, ['post_title' => $c['title'], 'post_status' => 'private', 'post_content' => $c['content'], 'post_excerpt' => $c['excerpt']]);
  update_post_meta($id, '_tutor_course_price_type', 'free');
  update_post_meta($id, '_tutor_course_level', 'all_levels');
  update_post_meta($id, '_tutor_course_benefits', $c['benefits']);
  $t = am_thumb(); if ($t) set_post_thumbnail($id, $t);
  return $id;
}

function am_quiz($src, $lang, $title, $topic, $order, $qs, $desc, $opt = []){
  global $wpdb; $p = $wpdb->prefix; $nq = count($qs);
  $qid = tr_post('tutor_quiz', $src, $lang, ['post_title' => $title, 'post_parent' => $topic, 'menu_order' => $order, 'post_content' => $desc]);
  update_post_meta($qid, 'tutor_quiz_option', array_merge(['time_limit' => ['time_value' => 0, 'time_type' => 'minutes'], 'hide_quiz_time_display' => 0, 'feedback_mode' => 'reveal', 'enable_answer_reveal' => '1', 'attempts_allowed' => 0, 'limit_attempts_allowed' => '1', 'pass_is_required' => 0, 'passing_grade' => 81, 'max_questions_for_answer' => $nq, 'quiz_auto_start' => 0, 'question_layout_view' => 'single_question', 'questions_order' => 'rand', 'hide_question_number_overview' => 0, 'short_answer_characters_limit' => 200, 'open_ended_answer_characters_limit' => 500], $opt));
  $old = $wpdb->get_col($wpdb->prepare("SELECT question_id FROM {$p}tutor_quiz_questions WHERE quiz_id=%d", $qid));
  if ($old) { $in = implode(',', array_map('intval', $old)); $wpdb->query("DELETE FROM {$p}tutor_quiz_question_answers WHERE belongs_question_id IN ($in)"); $wpdb->query("DELETE FROM {$p}tutor_quiz_questions WHERE question_id IN ($in)"); }
  $o = 0;
  foreach ($qs as $q) { $o++;
    $wpdb->insert($p . 'tutor_quiz_questions', ['quiz_id' => $qid, 'question_title' => $q['q'], 'question_description' => $q['desc'] ?? '', 'answer_explanation' => $q['erkl'] ?? '', 'question_type' => 'multiple_choice', 'question_mark' => 1, 'question_settings' => maybe_serialize(['question_type' => 'multiple_choice', 'question_mark' => 1, 'answer_required' => 1, 'randomize_question' => '1', 'show_question_mark' => 0, 'has_multiple_correct_answer' => 0, 'is_image_answer' => 0]), 'question_order' => $o]);
    $questid = (int)$wpdb->insert_id; $ao = 0;
    foreach ($q['opts'] as $a) { $ao++; $wpdb->insert($p . 'tutor_quiz_question_answers', ['belongs_question_id' => $questid, 'belongs_question_type' => 'multiple_choice', 'answer_title' => $a[0], 'is_correct' => $a[1] ? 1 : 0, 'image_id' => 0, 'answer_two_gap_match' => '', 'answer_view_format' => 'text', 'answer_order' => $ao]); }
  }
  return [$qid, $o];
}

// Ein Modul einer Sprache anlegen/aktualisieren. $what: 'lessons' | 'quiz' | 'sim' | 'all'
function am_import_module($lang, $n, $url, $what = 'all'){
  $D = am_data($lang, $url); if (!$D) return ['fehler' => 'json'];
  $m = null; foreach ($D['modules'] as $x) { if ((int)$x['n'] === (int)$n) $m = $x; }
  if (!$m) return ['fehler' => "modul $n fehlt"];
  $course = am_course($lang);
  $topic = tr_post('topics', $m['src'], $lang, ['post_title' => $m['title'], 'post_parent' => $course, 'menu_order' => (int)$n, 'post_content' => $m['summary']]);
  $out = ['modul' => $n, 'kurs' => $course, 'topic' => $topic, 'lektionen' => [], 'quiz' => []]; $order = 0;
  foreach ($m['items'] as $it) {
    $isSim = strpos($it['src'], 'am-sim') === 0;
    if ($it['type'] === 'lesson') {
      if ($what === 'all' || $what === 'lessons') {
        $lid = tr_post('lesson', $it['src'], $lang, ['post_title' => wp_strip_all_tags($it['title']), 'post_parent' => $topic, 'menu_order' => $order, 'post_content' => $it['html']]);
        update_post_meta($lid, '_tutor_course_id_for_lesson', $course); $out['lektionen'][] = $lid;
      }
    } else if (($isSim && ($what === 'all' || $what === 'sim')) || (!$isSim && ($what === 'all' || $what === 'quiz'))) {
      [$qid, $nq] = am_quiz($it['src'], $lang, $it['title'], $topic, $order, $it['qs'], $it['desc'], $it['opt'] ?: []);
      $out['quiz'][] = [$it['src'], $qid, $nq];
    }
    $order++;
  }
  return $out;
}

function am_ids($url_de, $url_fr){
  $r = [];
  foreach (['de' => $url_de, 'fr' => $url_fr] as $l => $url) {
    $D = am_data($l, $url); $e = ['kurs' => tr_find('am-course', $l), 'module' => []];
    foreach ($D['modules'] as $m) {
      $x = ['n' => $m['n'], 'thema' => tr_find($m['src'], $l), 'lektionen' => [], 'quizze' => []];
      foreach ($m['items'] as $it) { $id = tr_find($it['src'], $l); if ($it['type'] === 'lesson') $x['lektionen'][] = $id; else $x['quizze'][] = [$it['src'] => $id]; }
      $e['module'][] = $x;
    }
    $r[$l] = $e;
  }
  return $r;
}
