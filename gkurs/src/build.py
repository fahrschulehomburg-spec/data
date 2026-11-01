import json, sys, os, re, html
sys.path.insert(0, os.path.dirname(__file__))
import de1, de2, de3, fr1, fr2, fr3

BASE = 'https://www.fahrschule-homburg.be/wp-content/uploads/fh-kurse/b/img/'
IMGSTYLE = 'display:block;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;border-radius:8px;margin-bottom:10px;'
SIGNSTYLE = 'display:block;max-width:220px;width:60%;height:auto;margin:0 auto 10px;'
def esc(s): return html.escape(s, quote=True)
def is_sign(n): return n.endswith('.png')
def merk(big, small):
    return ('<div class="tr-merk" style="background:#16202B;color:#fff;border-left:6px solid #F5B700;border-radius:10px;padding:14px 18px;margin:6px 0 10px;max-width:560px;">'
            '<div style="font-size:1.35em;font-weight:700;line-height:1.25;">' + esc(big) + '</div><div style="opacity:.85;margin-top:4px;font-size:.95em;">' + esc(small) + '</div></div>')
def img(spec):
    st = SIGNSTYLE if is_sign(spec[1]) else IMGSTYLE
    return '<img src="' + BASE + spec[1] + '" alt="' + esc(spec[2]) + '" loading="lazy" style="' + st + '">'
def tile(spec):
    if spec[0] == 'M': return '<div class="tr-icon tr-kachel">' + merk(spec[1], spec[2]) + '</div>'
    return '<div class="tr-icon tr-kachel">' + img(spec) + '</div>'
def lesson_html(L):
    h = '<p><em>' + esc(L['sub']) + '</em></p>\n'
    for (hh, spec, p) in L['cards']:
        h += '<h3>' + esc(hh) + '</h3>\n' + tile(spec) + '\n<p>' + esc(p) + '</p>\n'
    return h
def overview_html(m, goals_label):
    s = m['ov']
    h = '<div class="tr-foto"><img src="' + BASE + s[1] + '" alt="' + esc(s[2]) + '" style="display:block;width:100%;height:auto;"></div>'
    h += '<p><em>' + esc(m['intro']) + '</em></p>\n<h3>' + esc(goals_label) + '</h3>\n<ul>' + ''.join('<li>' + esc(g) + '</li>' for g in m['goals']) + '</ul>'
    return h
def merke_html(m): return '<ul>' + ''.join('<li>' + esc(x) + '</li>' for x in m['merke']) + '</ul>'

UI = {
 'de': dict(module='Modul', overview='Überblick & Lernziele', merke='Merksätze', goals='Das lernst du in diesem Modul', quiz='Quiz', quizdesc='%d Fragen mit Erklärung nach jeder Antwort.'),
 'fr': dict(module='Module', overview='Aperçu et objectifs', merke='À retenir', goals='Ce que tu apprends dans ce module', quiz='Quiz', quizdesc='%d questions avec explication après chaque réponse.'),
}
COURSE = {
 'de': dict(title='TheorieRoute: Online-Theoriekurs Führerschein G (landwirtschaftliche Fahrzeuge)',
   lead='Online-Theoriekurs für den belgischen Führerschein G – Traktor, Anhänger und landwirtschaftliche Fahrzeuge: 12 Module von Führerschein und Fahrzeug über Ausrüstung, Kupplung, Ladung und Beleuchtung bis zu Vorfahrt, Geschwindigkeit, Unfall, Alkohol und Technik – dazu eine Prüfungssimulation mit 40 zufälligen Fragen wie in der echten Prüfung. Mit Fotos, Verkehrszeichen, Merkkarten und einer Erklärung zu jeder Quizfrage.',
   access='Zugang 60 Tage, auf Handy, Tablet und Computer.',
   benefits=['Wissen, wer den Führerschein G braucht und was mit 16 und 18 Jahren erlaubt ist', 'Traktor und Anhänger richtig ausrüsten, kuppeln, beladen und beleuchten', 'Vorfahrt, Geschwindigkeit und Abstand mit dem langen Traktorzug sicher beherrschen', 'Mit 120 Fragen und Erklärungen und einer Prüfungssimulation (40 Fragen, bestanden ab 33) üben']),
 'fr': dict(title='TheorieRoute : cours de théorie en ligne permis G (véhicules agricoles)',
   lead='Cours de théorie en ligne pour le permis G belge – tracteur, remorque et véhicules agricoles : 12 modules, du permis et du véhicule à l’équipement, l’attelage, le chargement et l’éclairage jusqu’à la priorité, la vitesse, l’accident, l’alcool et la technique – plus une simulation d’examen de 40 questions au hasard comme à l’examen réel. Avec photos, signaux routiers, fiches mémo et une explication pour chaque question.',
   access='Accès 60 jours, sur smartphone, tablette et ordinateur.',
   benefits=['Savoir qui a besoin du permis G et ce qui est permis à 16 et à 18 ans', 'Bien équiper, atteler, charger et éclairer tracteur et remorque', 'Maîtriser la priorité, la vitesse et les distances avec un long ensemble de tracteur', 'S’entraîner avec 120 questions expliquées et une simulation d’examen (40 questions, réussi à partir de 33)']),
}
SIM = {
 'de': dict(title='Prüfungssimulation', intro='Hier übst du unter echten Bedingungen: 40 zufällige Fragen aus allen zwölf Modulen – wie in der Theorieprüfung G. Bestanden hast du ab 33 richtigen Antworten. Die Auswertung siehst du am Ende; jeder Durchgang stellt die Fragen neu zusammen.',
   goals=['40 Fragen aus allen Modulen, zufällig gemischt', 'Bestanden ab 33 von 40 richtigen Antworten (rund 82 %)', 'Auswertung mit Erklärungen erst am Ende', 'Falsche Antworten im passenden Modul nachlesen und erneut üben'],
   qtitle='Prüfungssimulation Führerschein G', qdesc='40 zufällige Fragen aus allen Modulen. Bestanden ab 33 richtigen Antworten (rund 82 %).'),
 'fr': dict(title='Simulation d’examen', intro='Ici, tu t’entraînes dans les conditions réelles : 40 questions au hasard dans les douze modules – comme à l’examen théorique G. Tu réussis à partir de 33 bonnes réponses. Le résultat s’affiche à la fin ; chaque essai compose de nouvelles questions.',
   goals=['40 questions de tous les modules, mélangées au hasard', 'Réussi à partir de 33 bonnes réponses sur 40 (environ 82 %)', 'Correction avec explications seulement à la fin', 'Relire les réponses fausses dans le module correspondant et s’entraîner à nouveau'],
   qtitle='Simulation d’examen permis G', qdesc='40 questions au hasard dans tous les modules. Réussi à partir de 33 bonnes réponses (environ 82 %).'),
}

def mods(lang):
    return (de1.MODS + de2.MODS + de3.MODS) if lang == 'de' else (fr1.MODS + fr2.MODS + fr3.MODS)

def qitem(q):
    text, opts, ok, erkl = q[:4]
    desc = ('<p>' + img(q[4]) + '</p>') if len(q) > 4 and q[4] else ''
    return dict(q=text, desc=desc, erkl=erkl, opts=[[t, i == ok] for i, t in enumerate(opts)])

def build(lang):
    ui = UI[lang]; C = COURSE[lang]
    out = dict(course=dict(title=C['title'], content='<p>' + esc(C['lead']) + '</p><p>' + esc(C['access']) + '</p>', excerpt=C['lead'] + ' ' + C['access'], benefits='\n'.join(C['benefits'])), modules=[])
    allq = []
    for n, m in enumerate(mods(lang), 1):
        items = [dict(type='lesson', src=f'g-m{n}-l0', title=ui['overview'], html=overview_html(m, ui['goals']))]
        for i, L in enumerate(m['lessons'], 1):
            items.append(dict(type='lesson', src=f'g-m{n}-l{i}', title=L['t'], html=lesson_html(L)))
        items.append(dict(type='lesson', src=f'g-m{n}-lm', title=ui['merke'], html=merke_html(m)))
        qs = [qitem(q) for q in m['quiz']]; allq += qs
        items.append(dict(type='quiz', src=f'g-m{n}-quiz', title=f"{ui['quiz']} {ui['module']} {n}: {m['title']}", desc=ui['quizdesc'] % len(qs), qs=qs, opt={}))
        out['modules'].append(dict(n=n, src=f'g-m{n}', title=f"{ui['module']} {n}: {m['title']}", summary='<p>' + esc(m['intro']) + '</p>', items=items))
    T = SIM[lang]; n = len(out['modules']) + 1
    tm = dict(intro=T['intro'], goals=T['goals'], ov=('B', 'sit-875d309b.jpg', 'Traktor mit Anhänger in der Dämmerung' if lang == 'de' else 'Tracteur avec remorque au crépuscule'))
    items = [dict(type='lesson', src=f'g-m{n}-l0', title=ui['overview'], html=overview_html(tm, ui['goals'])),
             dict(type='quiz', src=f'g-m{n}-quiz', title=T['qtitle'], desc=T['qdesc'], qs=allq,
                  opt=dict(max_questions_for_answer=40, passing_grade=82, feedback_mode='default', enable_answer_reveal='0', questions_order='rand'))]
    out['modules'].append(dict(n=n, src=f'g-m{n}', title=f"{ui['module']} {n}: {T['title']}", summary='<p>' + esc(T['intro']) + '</p>', items=items))
    return out

def check(de, fr):
    imgs = set(); problems = []
    for a, b in zip(de['modules'], fr['modules']):
        if len(a['items']) != len(b['items']): problems.append(('items', a['n']))
        for x, y in zip(a['items'], b['items']):
            if x['type'] != y['type'] or x['src'] != y['src']: problems.append(('src', x['src'], y['src']))
            if x['type'] == 'lesson':
                if x['html'].count('<h3>') != y['html'].count('<h3>'): problems.append(('h3', x['src']))
                if re.findall(r'img/([^"]+)"', x['html']) != re.findall(r'img/([^"]+)"', y['html']): problems.append(('img', x['src']))
                if x['html'].count('tr-merk') != y['html'].count('tr-merk'): problems.append(('merk', x['src']))
                if x['html'].count('<li>') != y['html'].count('<li>'): problems.append(('li', x['src']))
            else:
                if len(x['qs']) != len(y['qs']): problems.append(('nq', x['src'], len(x['qs']), len(y['qs'])))
                for q1, q2 in zip(x['qs'], y['qs']):
                    if [o[1] for o in q1['opts']] != [o[1] for o in q2['opts']]: problems.append(('ok', x['src'], q1['q'][:40], q2['q'][:40]))
                    if sum(o[1] for o in q1['opts']) != 1: problems.append(('nok', q1['q'][:40]))
                    if not q1['erkl'] or not q2['erkl']: problems.append(('erkl', x['src'], q1['q'][:40]))
                    if re.findall(r'img/([^"]+)"', q1['desc']) != re.findall(r'img/([^"]+)"', q2['desc']): problems.append(('qimg', q1['q'][:40]))
    for d in (de, fr):
        for m in d['modules']:
            for x in m['items']:
                s = x.get('html', '') + ''.join(q['desc'] for q in x.get('qs', []))
                imgs |= set(re.findall(r'img/([^"]+\.(?:jpg|png))', s))
    return problems, imgs

if __name__ == '__main__':
    de, fr = build('de'), build('fr')
    p, imgs = check(de, fr)
    print('problems', p)
    out = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), '..', '..', 'ghrepo', 'gkurs')
    os.makedirs(out, exist_ok=True)
    json.dump(de, open(os.path.join(out, 'g-de.json'), 'w'), ensure_ascii=False)
    json.dump(fr, open(os.path.join(out, 'g-fr.json'), 'w'), ensure_ascii=False)
    for lang, d in (('de', de), ('fr', fr)):
        les = sum(1 for m in d['modules'] for x in m['items'] if x['type'] == 'lesson')
        qz = [(x['src'], len(x['qs'])) for m in d['modules'] for x in m['items'] if x['type'] == 'quiz']
        h = ''.join(x.get('html', '') for m in d['modules'] for x in m['items'])
        print(lang, 'lessons', les, 'kacheln', h.count('tr-kachel'), 'merk', h.count('class="tr-merk"'), 'quiz', qz[-1], sum(n for s, n in qz[:-1]))
    print(len(imgs), sorted(imgs))
