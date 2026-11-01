import json, re, html, sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from sel import DROP_L, DROP_Q
import patches as P
import patches2 as P2
for _k, _v in P2.K.items(): P.K.setdefault(_k, []).extend(_v)
for _k, _v in P2.Q.items():
    for _f, _l in _v.items(): P.Q.setdefault(_k, {}).setdefault(_f, []).extend(_l)
import new1, new2, new3
from tufr import convert as tu

D = json.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'export-b.json')))
IMG = {'GEAR': 'sit-13cd3f86.jpg', 'HELM': 'sit-a54d5b43.jpg', 'SPEEDPED': 'sit-b974ad16c.jpg', 'PASSENGER': 'sit-38f610a7.jpg',
       'TYRE': 'sit-f55fced9.jpg', 'CURVE': 'sit-e3c217ed.jpg', 'RAIN': 'sit-ad77f6e7.jpg', 'SCOOTER': 'sit-97146fd9.jpg'}
BASE = 'https://www.fahrschule-homburg.be/wp-content/uploads/fh-kurse/b/img/'
IMGSTYLE = 'display:block;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;border-radius:8px;margin-bottom:10px;'
E = lambda s: html.escape(s, quote=True)
LANGS = ('de', 'fr')
STRIPFIG = {42500, 42529, 42600}   # Intro-Fotos mit Pkw-Motiv entfernen

def merk(big, small):
    return ('<div class="tr-merk" style="background:#16202B;color:#fff;border-left:6px solid #F5B700;border-radius:10px;padding:14px 18px;margin:6px 0 10px;max-width:560px;">'
            '<div style="font-size:1.35em;font-weight:700;line-height:1.25;">' + E(big) + '</div><div style="opacity:.85;margin-top:4px;font-size:.95em;">' + E(small) + '</div></div>')

def img_url(spec):
    if spec[0] == 'S': return spec[1]
    f = IMG.get(spec[1], spec[1])
    return f if f.startswith('http') else BASE + f

def tile(spec, li):
    if spec[0] == 'M':
        big, small = (spec[1], spec[2]) if li == 0 else (spec[3], spec[4])
        return '<div class="tr-icon tr-kachel">' + merk(big, small) + '</div>'
    alt = spec[2] if li == 0 else spec[3]
    if spec[0] == 'S':
        return '<div class="tr-icon tr-kachel"><img src="' + img_url(spec) + '" alt="' + E(alt) + '" loading="lazy" style="display:block;width:160px;max-width:100%;height:auto;margin-bottom:10px;"></div>'
    return '<div class="tr-icon tr-kachel"><img src="' + img_url(spec) + '" alt="' + E(alt) + '" loading="lazy" style="' + IMGSTYLE + '"></div>'

def qdesc(spec, li):
    if not spec: return ''
    alt = spec[2] if li == 0 else spec[3]
    if spec[0] == 'S':
        return '<div class="tr-quizbild" style="margin:6px 0 12px;"><img src="' + img_url(spec) + '" alt="' + E(alt) + '" loading="lazy" style="display:block;width:150px;height:auto;"></div>'
    return '<div class="tr-quizbild" style="margin:6px 0 12px;max-width:560px;"><img src="' + img_url(spec) + '" alt="' + E(alt) + '" loading="lazy" style="' + IMGSTYLE + '"></div>'

def lesson_html(L, li):
    h = '<p><em>' + E(L['sub'][li]) + '</em></p>\n'
    for c in L['cards']:
        h += '<h3>' + E(c[li]) + '</h3>\n' + tile(c[2], li) + '\n<p>' + E(c[3 + li]) + '</p>\n'
    return h

def overview_html(intro, ov, goals, li):
    lbl = ('Das lernst du in diesem Modul', 'Ce que tu apprends dans ce module')[li]
    alt = ov[2] if li == 0 else ov[3]
    if ov[0] == 'S':
        h = '<div class="tr-foto"><img src="' + img_url(ov) + '" alt="' + E(alt) + '" style="display:block;width:180px;height:auto;"></div>'
    else:
        h = '<div class="tr-foto"><img src="' + img_url(ov) + '" alt="' + E(alt) + '" style="display:block;width:100%;height:auto;"></div>'
    h += '<p><em>' + E(intro[li]) + '</em></p>\n<h3>' + lbl + '</h3>\n<ul>' + ''.join('<li>' + E(g) + '</li>' for g in goals[li]) + '</ul>'
    return h

def merke_html(m, li): return '<ul>' + ''.join('<li>' + E(x) + '</li>' for x in m[li]) + '</ul>'

def newq(q, li, tag=''):
    opts = q[2] if li == 0 else q[3]
    return dict(q=q[li], desc=qdesc(q[7], li), erkl=q[5 + li], opts=[[o, i == q[4]] for i, o in enumerate(opts)], tag=(q[8] if len(q) > 8 else tag), src='new')

# ---------- Kurs-B-Inhalte ----------
def topic(ti, lang): return D[lang][ti]

def b_lesson(x, y):
    """x,y: de/fr lesson dicts -> (title_de, title_fr, html_de, html_fr) oder None"""
    dl = DROP_L.get(x['id'], set())
    if dl == 'X': return None
    out = []
    for li, z in enumerate((x, y)):
        parts = re.split(r'(?=<h3)', z['html'])
        keep = []
        for i, p in enumerate(parts):
            if i in dl: continue
            for (lang, old, new) in P.K.get((x['id'], i), []):
                if lang != LANGS[li]: continue
                if old not in p:
                    p2 = html.unescape(p)
                    if old in p2: p = p2
                    else: raise SystemExit('Patch nicht gefunden %s/%d %s: %s' % (x['id'], i, lang, old[:60]))
                p = p.replace(old, new)
            if i == 0 and x['id'] in STRIPFIG:
                p = re.sub(r'<figure class="tr-foto">.*?</figure>', '', p, flags=re.S)
            keep.append(p)
        h = ''.join(keep)
        if li == 1: h = tu(h)
        out.append(h)
    t_fr = tu(html.unescape(y['title']))
    t_de = html.unescape(x['title'])
    if x['id'] in P2.TITLES: t_de, t_fr = P2.TITLES[x['id']]
    return (t_de, t_fr, out[0], out[1])

def b_questions(x, y, only=None, tagfn=None):
    res = []
    for q, qf in zip(x['qs'], y['qs']):
        qid = int(q['question_id'])
        if only is not None and qid not in only: continue
        if only is None and qid in DROP_Q: continue
        item = []
        for li, z in enumerate((q, qf)):
            d = dict(q=z['question_title'], desc=z['question_description'], erkl=z['answer_explanation'],
                     opts=[[a['answer_title'], a['is_correct'] == '1'] for a in z['answers']])
            pq = P.Q.get(qid, {})
            for field, lst in pq.items():
                for (lang, old, new) in lst:
                    if lang != LANGS[li]: continue
                    if field == 'a':
                        hit = False
                        for o in d['opts']:
                            if o[0] == old: o[0] = new; hit = True
                        if not hit: raise SystemExit('Antwort-Patch fehlt %d %s' % (qid, old))
                    else:
                        k = field
                        if old not in d[k]: raise SystemExit('Q-Patch fehlt %d %s %s' % (qid, k, old[:50]))
                        d[k] = d[k].replace(old, new)
            if li == 0 and qid in new3.DE_REWRITE:
                rw = new3.DE_REWRITE[qid]
                if 'q' in rw: d['q'] = rw['q']
                if 'erkl' in rw: d['erkl'] = rw['erkl']
                if 'a' in rw:
                    assert len(rw['a']) == len(d['opts'])
                    for o, t in zip(d['opts'], rw['a']): o[0] = t
            if li == 1 and qid in new3.FR_REWRITE:
                rw = new3.FR_REWRITE[qid]
                if 'q' in rw: d['q'] = rw['q']
                if 'erkl' in rw: d['erkl'] = rw['erkl']
                if 'a' in rw:
                    for o, t in zip(d['opts'], rw['a']): o[0] = t
            if li == 1:
                d['q'] = tu(d['q']); d['erkl'] = tu(d['erkl']); d['desc'] = tu(d['desc'])
                for o in d['opts']: o[0] = tu(o[0])
            d['tag'] = tagfn(qid) if tagfn else ''
            d['src'] = 'b%d' % qid
            item.append(d)
        res.append(item)
    return res

def module_from_b(ti, extra_lessons=(), extra_q=(), quiz_only=None, tagfn=None, insert_before_merke=True):
    a, b = D['de'][ti], D['fr'][ti]
    lessons, qs = [], []
    for x, y in zip(a['items'], b['items']):
        if x['type'] == 'lesson':
            r = b_lesson(x, y)
            if r: lessons.append(r)
        else:
            qs += b_questions(x, y, only=quiz_only, tagfn=tagfn)
    for L in extra_lessons:
        lessons.append((L['t'][0], L['t'][1], lesson_html(L, 0), lesson_html(L, 1)))
    for q in extra_q:
        qs.append([newq(q, 0), newq(q, 1)])
    title = re.sub(r'^Modul \d+:\s*', '', html.unescape(a['title']))
    title_fr = re.sub(r'^Module \d+\s*:\s*', '', html.unescape(b['title']))
    return dict(title=(title, title_fr), lessons=lessons, qs=qs)

def module_new(M, extra_b_lessons=(), extra_b_q=(), tags=None):
    lessons = [(L['t'][0], L['t'][1], lesson_html(L, 0), lesson_html(L, 1)) for L in M['lessons']]
    qs = []
    for i, q in enumerate(M.get('quiz', [])):
        t = (tags or {}).get(i, '')
        qs.append([newq(q, 0, t), newq(q, 1, t)])
    return dict(title=M['t'], lessons=lessons, qs=qs)

def find_item(iid):
    for ti, (a, b) in enumerate(zip(D['de'], D['fr'])):
        for x, y in zip(a['items'], b['items']):
            if x['id'] == iid: return x, y
    raise KeyError(iid)

def build():
    mods = []
    # 1
    m = module_new(new1.M1, tags={2: 'A', 3: 'A', 4: 'A', 6: 'A', 7: 'A', 8: 'A', 10: 'A', 14: 'A', 15: 'A'}); m['src'] = new1.M1; mods.append(m)
    # 2: neue Lektionen + Kurs-B-Lektionen Licht/Ladung
    m = module_new(new1.M2, tags={2: 'A', 3: 'A', 5: 'A', 10: 'A', 15: 'A'}); m['src'] = new1.M2
    ins = []
    for lid in (42491, 42509, 42493, 42500):
        x, y = find_item(lid); r = b_lesson(x, y)
        if r: ins.append(r)
    m['lessons'] = m['lessons'][:4] + ins[:3] + m['lessons'][4:] + ins[3:]
    x, y = find_item(42513); m['qs'] += b_questions(x, y)
    mods.append(m)
    A_ONLY = {70, 80, 168, 170, 188, 189, 375}
    tg = lambda q: 'A' if q in A_ONLY else ''
    specs = [(0, [new2.L_MOPED], new2.Q_MOPED), (1, [], []), (2, [new2.L_FLUSS], new2.Q_FLUSS), (3, [new2.L_TEMPO], new2.Q_TEMPO),
             (4, [], []), (5, [new2.L_PARK], new2.Q_PARK), (6, [], [])]
    for ti, el, eq in specs:
        tf = (lambda q: '' if q in (241, 249) else 'A') if ti == 6 else tg
        m = module_from_b(ti, el, eq, tagfn=tf); m['bmod'] = new3.BMODS[ti]; mods.append(m)
    m = module_new(new3.M10, tags={4: 'A', 9: 'A', 10: 'A', 11: 'A', 13: 'A'}); m['src'] = new3.M10; mods.append(m)
    for ti in (10, 8, 9, 12):
        m = module_from_b(ti, [], new3.EXTRA_Q.get(ti, []), tagfn=tg); m['bmod'] = new3.BMODS[ti]; mods.append(m)
    m = module_new(new3.M15); m['src'] = new3.M15
    x, y = find_item(42689); m['qs'] = b_questions(x, y, only=set(new3.KEEP_M12) - {376}, tagfn=tg)
    mods.append(m)
    return mods

UI = {'de': dict(module='Modul', overview='Überblick & Lernziele', merke='Merksätze', quiz='Quiz', quizdesc='%d Fragen mit Erklärung nach jeder Antwort.'),
      'fr': dict(module='Module', overview='Aperçu et objectifs', merke='À retenir', quiz='Quiz', quizdesc='%d questions avec explication après chaque réponse.')}
COURSE = {
 'de': dict(title='TheorieRoute: Online-Theoriekurs Motorrad & Moped (A, A1, A2, AM)',
   lead='Dein Online-Theoriekurs für die belgischen Führerscheine A1, A2, A und AM: 15 Module von Fahrzeugklassen, Schutzkleidung und Fahrtechnik auf zwei Rädern über Vorfahrt, Tempo, Parken und Autobahn bis zu Alkohol, Erster Hilfe und allen Verkehrszeichen. Kurze Lernschritte mit Fotos, amtlichen Schildern und Merkkarten, nach jedem Modul ein Quiz mit Erklärung zu jeder Antwort – und zum Schluss zwei Prüfungssimulationen: A mit 50 und AM mit 40 Fragen.',
   access='Zugang 60 Tage, auf Handy, Tablet und Computer.',
   benefits=['Die Führerscheine AM, A1, A2 und A und den Weg dorthin kennen', 'Helm- und Schutzkleidungspflicht, Beifahrer- und Kinderregeln sicher beherrschen', 'Wissen, wo Mofas und Motorräder fahren dürfen, und sicher durch Kurven, Regen und Stau kommen', 'Mit Prüfungssimulationen für A (50 Fragen) und AM (40 Fragen) üben']),
 'fr': dict(title='TheorieRoute : cours théorique en ligne moto et cyclomoteur (A, A1, A2, AM)',
   lead="Ton cours théorique en ligne pour les permis belges A1, A2, A et AM : 15 modules, des catégories de véhicules, de l'équipement de protection et de la technique de conduite sur deux roues à la priorité, la vitesse, le stationnement et l'autoroute, jusqu'à l'alcool, aux premiers secours et à tous les signaux routiers. Des étapes courtes avec photos, signaux officiels et fiches mémo, un quiz après chaque module avec une explication pour chaque réponse – et pour finir deux simulations d'examen : A avec 50 et AM avec 40 questions.",
   access='Accès 60 jours, sur smartphone, tablette et ordinateur.',
   benefits=['Connaître les permis AM, A1, A2 et A et le chemin pour les obtenir', "Maîtriser l'obligation du casque et de l'équipement, les règles pour passagers et enfants", 'Savoir où cyclomoteurs et motos peuvent rouler, et passer sûrement virages, pluie et bouchons', "S'entraîner avec les simulations d'examen A (50 questions) et AM (40 questions)"]),
}
SIM = {
 'de': [('Prüfungssimulation A (50 Fragen)', '50 Zufallsfragen aus allen Modulen wie in der Theorieprüfung A1/A2/A. Bestanden ab 41 richtigen Antworten (82 %). Die Sonderregel der Prüfung in der Wallonie und in Flandern (zwei Fehler bei Fragen zu schweren Verstößen oder zur Geschwindigkeitsüberschreitung = nicht bestanden) bildet die Simulation nicht ab. Ohne Zeitlimit. Die Lösungen siehst du am Ende.'),
        ('Prüfungssimulation AM (40 Fragen)', '40 Zufallsfragen aus dem Stoff für Kleinkrafträder wie in der Theorieprüfung AM. Bestanden ab 33 richtigen Antworten (82,5 %). Ohne Zeitlimit. Die Lösungen siehst du am Ende.')],
 'fr': [('Simulation d\'examen A (50 questions)', "50 questions au hasard de tous les modules, comme à l'examen théorique A1/A2/A. Réussi dès 41 bonnes réponses (82 %). La règle particulière de l'examen en Wallonie et en Flandre (deux erreurs aux questions sur les infractions graves ou les excès de vitesse = échec) n'est pas reproduite dans la simulation. Sans limite de temps. Tu vois les réponses à la fin."),
        ('Simulation d\'examen AM (40 questions)', "40 questions au hasard dans la matière des cyclomoteurs, comme à l'examen théorique AM. Réussi dès 33 bonnes réponses (82,5 %). Sans limite de temps. Tu vois les réponses à la fin.")],
}

def to_json(mods, lang):
    li = 0 if lang == 'de' else 1; ui = UI[lang]; C = COURSE[lang]
    out = dict(course=dict(title=C['title'], content='<p>' + E(C['lead']) + '</p><p>' + E(C['access']) + '</p>', excerpt=C['lead'] + ' ' + C['access'], benefits='\n'.join(C['benefits'])), modules=[])
    allq = []
    for n, m in enumerate(mods, 1):
        src = m.get('src') or m.get('bmod')
        title = m['title'][li]
        items = [dict(type='lesson', src=f'am-m{n}-l0', title=ui['overview'], html=overview_html(src['intro'], src['ov'], src['goals'], li))]
        for k, L in enumerate(m['lessons'], 1):
            items.append(dict(type='lesson', src=f'am-m{n}-l{k}', title=L[li], html=L[2 + li]))
        items.append(dict(type='lesson', src=f'am-m{n}-lm', title=ui['merke'], html=merke_html(src['merke'], li)))
        qs = [q[li] for q in m['qs']]
        allq += [dict(q, modul=n) for q in qs]
        items.append(dict(type='quiz', src=f'am-m{n}-quiz', title=f"{ui['quiz']} {ui['module']} {n}: {title}", desc=ui['quizdesc'] % len(qs), qs=qs, opt={}))
        if n == len(mods):
            base = dict(time_limit={'time_value': 0, 'time_type': 'minutes'}, feedback_mode='default', enable_answer_reveal='0', passing_grade=82, questions_order='rand')
            simA = [dict(q) for q in allq]
            simAM = [dict(q) for q in allq if q.get('tag') != 'A']
            (ta, da), (tb, db) = SIM[lang]
            items.append(dict(type='quiz', src='am-sim-a', title=ta, desc=da, qs=simA, opt=dict(base, max_questions_for_answer=50)))
            items.append(dict(type='quiz', src='am-sim-am', title=tb, desc=db, qs=simAM, opt=dict(base, max_questions_for_answer=40)))
        out['modules'].append(dict(n=n, src=f'am-m{n}', title=f"{ui['module']} {n}: {title}", summary='<p>' + E(src['intro'][li]) + '</p>', items=items))
    return out

if __name__ == '__main__':
    mods = build()
    de, fr = to_json(mods, 'de'), to_json(mods, 'fr')
    prob = []
    for a, b in zip(de['modules'], fr['modules']):
        for x, y in zip(a['items'], b['items']):
            if x['type'] != y['type'] or x['src'] != y['src']: prob.append(('src', x['src']))
            if x['type'] == 'lesson' and x['html'].count('<h3') != y['html'].count('<h3'): prob.append(('h3', x['src']))
            if x['type'] == 'quiz':
                if len(x['qs']) != len(y['qs']): prob.append(('nq', x['src']))
                for q1, q2 in zip(x['qs'], y['qs']):
                    if [o[1] for o in q1['opts']] != [o[1] for o in q2['opts']]: prob.append(('ok', q1['q'][:40]))
                    if sum(o[1] for o in q1['opts']) != 1: prob.append(('nok', q1['q'][:40]))
                    if not q1['erkl'] or not q2['erkl']: prob.append(('erkl', q1['q'][:40]))
    print('Probleme:', prob)
    outd = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'out'); os.makedirs(outd, exist_ok=True)
    json.dump(de, open(os.path.join(outd, 'am-de.json'), 'w'), ensure_ascii=False)
    json.dump(fr, open(os.path.join(outd, 'am-fr.json'), 'w'), ensure_ascii=False)
    for lang, d in (('de', de), ('fr', fr)):
        les = sum(1 for m in d['modules'] for x in m['items'] if x['type'] == 'lesson')
        qz = [(x['src'], len(x['qs'])) for m in d['modules'] for x in m['items'] if x['type'] == 'quiz']
        h = ''.join(x.get('html', '') for m in d['modules'] for x in m['items'])
        print(lang, 'Lektionen', les, 'Kacheln', h.count('<h3'), 'Quiz', qz)
