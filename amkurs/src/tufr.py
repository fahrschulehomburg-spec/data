# vous -> tu Umwandlung fuer franzoesische Kurstexte (Kurs B -> A/AM)
import re

SPECIAL = {
 'avez':'as','êtes':'es','pouvez':'peux','devez':'dois','voulez':'veux','savez':'sais','faites':'fais','dites':'dis','allez':'vas',
 'prenez':'prends','apprenez':'apprends','comprenez':'comprends','reprenez':'reprends','mettez':'mets','permettez':'permets','voyez':'vois','prévoyez':'prévois',
 'tenez':'tiens','maintenez':'maintiens','obtenez':'obtiens','retenez':'retiens','revenez':'reviens','convenez':'conviens','venez':'viens','devenez':'deviens',
 'connaissez':'connais','reconnaissez':'reconnais','sortez':'sors','partez':'pars','suivez':'suis','poursuivez':'poursuis','lisez':'lis',
 'inscrivez':'inscris','décrivez':'décris','écrivez':'écris','fuyez':'fuis','courez':'cours','parcourez':'parcours','percevez':'perçois','recevez':'reçois',
 'atteignez':'atteins','rejoignez':'rejoins','éteignez':'éteins','réduisez':'réduis','conduisez':'conduis','attendez':'attends','descendez':'descends',
 'perdez':'perds','rendez':'rends','répondez':'réponds','dépendez':'dépends','confondez':'confonds','combattez':'combats','battez':'bats',
 'couvrez':'couvres','ouvrez':'ouvres','offrez':'offres','essayez':'essaies','payez':'paies','débrayez':'débraies','appelez':'appelles','rappelez':'rappelles',
 'levez':'lèves','soulevez':'soulèves','amenez':'amènes','menez':'mènes','emmenez':'emmènes','achetez':'achètes','cédez':'cèdes','réglez':'règles','accélérez':'accélères',
 'complétez':'complètes','insérez':'insères','libérez':'libères','procédez':'procèdes','repérez':'repères','transférez':'transfères','modérez':'modères',
 'préférez':'préfères','considérez':'considères','répétez':'répètes','protégez':'protèges','possédez':'possèdes','pénétrez':'pénètres','gérez':'gères',
 'arrondissez':'arrondis','avertissez':'avertis','choisissez':'choisis','franchissez':'franchis','ralentissez':'ralentis','remplissez':'remplis',
 'réagissez':'réagis','éblouissez':'éblouis','finissez':'finis','agissez':'agis','saisissez':'saisis','réussissez':'réussis','établissez':'établis',
 'disposez':'disposes','soyez':'sois','ayez':'aies','puissiez':'puisses',
 'deviez':'devais','pouviez':'pouvais','aviez':'avais','rouliez':'roulais','étiez':'étais','vouliez':'voulais',
 'auriez':'aurais','seriez':'serais','devriez':'devrais','pourriez':'pourrais','voudriez':'voudrais','arrêteriez':'arrêterais','passeriez':'passerais','respecteriez':'respecterais',
 'respectiez':'respectes','risquiez':'risques','déplaciez':'déplaces','voyiez':'voies','lisiez':'lises',
}
IMP_SPECIAL = {'allez':'va','ayez':'aie','soyez':'sois','sachez':'sache'}
NONVERB = {'assez','chez','nez','rez'}
CLITIC = {'ne','n\'','le','la','les','l\'','en','y','lui','leur','me','m\'','se','s\'','te','t\''}
PREP = {'pour','de','à','devant','derrière','après','chez','vers','sans','avec','sur','par','contre','entre','envers','selon','autour','près','sauf','que','qu\''}
FEM = {'arrivée','attention','adresse','bande','carte','chaussée','demande','destination','direction','droite','gauche','guise','heure','moitié','plaque',
       'première','priorité','progression','route','sortie','vitesse','voiture','école','moto','voie','place','assurance','main','tête','position','trajectoire','sécurité','santé','vue','distance','roue','famille','région','commune','entrée'}

def is_verb_vous(w):
    lw = w.lower()
    if lw in NONVERB: return False
    return lw in SPECIAL or lw.endswith('ez') or lw.endswith('iez')

def tu_present(w):
    lw = w.lower()
    if lw in SPECIAL: r = SPECIAL[lw]
    elif lw.endswith('ez'): r = lw[:-2] + 'es'   # -er Verben
    else: r = lw
    return r

def is_er_like(tu):  # Imperativ ohne s
    return tu.endswith('es') and not tu.endswith(('ais','ois','is'))

def tu_imp(w, before_en_y=False):
    lw = w.lower()
    if lw in IMP_SPECIAL: r = IMP_SPECIAL[lw]
    else:
        r = tu_present(w)
        if lw.endswith('ez') and lw not in SPECIAL: r = lw[:-2] + 'e'
        elif r.endswith('es') and lw in SPECIAL and SPECIAL[lw].endswith('es') and lw not in ('disposez',):
            r = r[:-1]
        elif lw == 'disposez': r = 'dispose'
        if before_en_y and r.endswith('e'): r += 's'
    return r

def cap(src, r):
    return r[0].upper() + r[1:] if src[:1].isupper() else r

VOW = re.compile(r'^[aeiouyhàâéèêëîïôûœ]', re.I)

TOK = re.compile(r"(<[^>]*>)|([A-Za-zÀ-ÖØ-öø-ÿœŒ]+'?|[A-Za-zÀ-ÖØ-öø-ÿœŒ]+)|([^A-Za-zÀ-ÖØ-öø-ÿœŒ<]+)")

PRE = [("seulement si vous ou le conducteur en face avez des flèches de présélection sur la chaussée", "seulement si des flèches de présélection sur la chaussée le prévoient pour l'un des deux conducteurs"),
       ("la vôtre", "la tienne"), ("le vôtre", "le tien"), ("les vôtres", "les tiens"),
       ("ni n'entravez", "ni n'entraves"), ("ou ne changez de direction", "ou ne changes de direction"), ("vous pouvez y vous arrêter", "tu peux t'y arrêter"), ("prioritaire et ne vous arrêtez que", "prioritaire et ne t'arrêtes que"), ("Roulez à vitesse d'homme et vous vous arrêtez au besoin", "Roule à vitesse d'homme et arrête-toi au besoin")]
POST = [(r"\b(dois|peux|veux|as|es|vas|sais|Dois|Peux|Veux|As)-toi\b", r"\1-tu")]

def convert(s, log=None):
    if not s: return s
    for a, b in PRE: s = s.replace(a, b)
    r = _convert(s, log)
    for a, b in POST: r = re.sub(a, b, r)
    return r

def _convert(s, log=None):
    if not s: return s
    toks = [m.group(0) for m in TOK.finditer(s)]
    assert ''.join(toks) == s
    words = [(i, t) for i, t in enumerate(toks) if t and t[0] != '<' and re.match(r"[A-Za-zÀ-ÖØ-öø-ÿœŒ]", t)]
    out = list(toks)
    role = {}  # index -> 'subj' | 'refl'
    widx = [i for i, _ in words]
    pos = {i: k for k, i in enumerate(widx)}
    def sep_between(a, b):
        return ''.join(toks[a+1:b])
    # 1) Verben in vous-Form finden
    for k, i in enumerate(widx):
        w = toks[i]
        if not is_verb_vous(w): continue
        # rueckwaerts Klitika-Kette
        chain = []
        j = k - 1
        while j >= 0:
            t = toks[widx[j]].lower()
            sep = sep_between(widx[j], widx[j+1] if j+1 < len(widx) else i)
            if re.search(r"[.,;:!?()\[\]«»\"–—]", sep): break
            if t == 'vous' or t in CLITIC:
                chain.insert(0, (widx[j], t)); j -= 1
            else: break
        nxt = toks[i+1] if i+1 < len(toks) else ''
        inverted = nxt == '-' and i+2 < len(toks) and toks[i+2].lower() == 'vous'
        vous_in_chain = [c for c in chain if c[1] == 'vous']
        if inverted:
            rest = s[sum(len(t) for t in toks[:i]):]
            mq = re.search(r'[.?!]', rest)
            question = bool(mq and mq.group(0) == '?')
            if question:
                out[i] = cap(w, tu_present(w)); role[i+2] = 'inv_q'
            else:
                out[i] = cap(w, tu_imp(w)); role[i+2] = 'inv_imp'
            for c in vous_in_chain: role[c[0]] = 'refl'
            continue
        if vous_in_chain and chain[0][1] == 'vous':
            role[chain[0][0]] = 'subj'
            for c in vous_in_chain[1:]: role[c[0]] = 'refl'
            out[i] = cap(w, tu_present(w))
        else:
            # Imperativ (evtl. "ne vous ...")
            for c in vous_in_chain: role[c[0]] = 'refl'
            nn = toks[i+2].lower() if i+2 < len(toks) else ''
            m = nxt == '-' and nn in ('en', 'y')
            out[i] = cap(w, tu_imp(w, bool(m)))
            if m and w.lower() == 'allez': out[i] = cap(w, 'vas')
            if log is not None: log.append(('imp', w, out[i]))
    # 2) vous ersetzen
    for k, i in enumerate(widx):
        t = toks[i]
        if t.lower() != 'vous': continue
        r = role.get(i)
        nxt = toks[i+1] if i+1 < len(toks) else ''
        nw = toks[widx[k+1]] if k+1 < len(widx) else ''
        prev = toks[widx[k-1]].lower() if k > 0 else ''
        if r == 'subj': rep = 'tu'
        elif r == 'inv_q': rep = 'tu'
        elif r == 'inv_imp': rep = 'toi'
        elif nxt == '-' and nw.lower().startswith('même'): rep = 'toi'
        elif prev in PREP and r is None:
            rep = 'te' if (nxt == ' ' and re.search(r'(er|ir|re|oir)$', nw) and nw.islower()) else 'toi'
            if rep == 'te' and VOW.match(nw):
                out[i] = cap(t, "t'"); out[i+1] = ''; continue
        elif r == 'refl' or (prev not in PREP and nxt == ' ' and nw):
            rep = 'te'
            if VOW.match(nw) and nxt == ' ':
                out[i] = cap(t, "t'"); out[i+1] = ''; continue
        else: rep = 'toi'
        out[i] = cap(t, rep)
        if log is not None and r is None: log.append(('obj', prev, t, nw, rep))
    # 3) votre / vos
    for k, i in enumerate(widx):
        t = toks[i]; lt = t.lower()
        if lt not in ('votre', 'vos', 'vôtre'): continue
        nw = toks[widx[k+1]].lower() if k+1 < len(widx) else ''
        if lt == 'vos': out[i] = cap(t, 'tes'); continue
        if lt == 'vôtre': out[i] = cap(t, 'tien'); continue
        noun = nw
        if nw in ('propre', 'unique', 'première', 'premier', 'seul', 'seule', 'nouveau', 'nouvelle', 'ancien', 'ancienne') and k+2 < len(widx):
            noun = toks[widx[k+2]].lower()
            fem = noun in FEM or nw in ('première', 'seule', 'nouvelle', 'ancienne')
            out[i] = cap(t, 'ta' if fem else 'ton')
            if nw == 'unique' or nw == 'ancienne': out[i] = cap(t, 'ton' if VOW.match(nw) else ('ta' if fem else 'ton'))
            continue
        if VOW.match(nw): out[i] = cap(t, 'ton')
        elif nw in FEM: out[i] = cap(t, 'ta')
        else: out[i] = cap(t, 'ton')
    r = ''.join(out)
    # te + Vokal
    r = re.sub(r"\b([Tt])e (?=[aeiouyhàâéèêëîïôûœAEIOUYHÀÂÉÈÊ])", r"\1'", r)
    return r

if __name__ == '__main__':
    tests = ["Vous devez vous arrêter.", "Arrêtez-vous au feu.", "Ne vous arrêtez pas sur la bande.", "Pouvez-vous dépasser ?",
             "Gardez vos distances et réglez votre vitesse.", "Si vous ne le voyez pas, ralentissez.", "Il vous dépasse par la droite.",
             "Pour vous protéger, placez-vous derrière lui.", "Vous êtes responsable de votre véhicule.", "Laissez-le passer.", "Penses-y : allez-y."]
    for t in tests: print(t, '->', convert(t))
