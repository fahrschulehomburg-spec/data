# Zweite Korrekturrunde (Bericht 2, 1.11.2026) an uebernommenen Kurs-B-Inhalten.
# Format wie patches.py; wird in build_am.py zu P.K / P.Q hinzugefuegt (Listen werden verlaengert, nicht ersetzt).
K = {
 # patches.py hatte (42304,4) doppelt als Schluessel -> der erste Eintrag (de) ging verloren
 (42304,4): [('de',' Das ist eine bekannte Prüfungsfrage.',''),('fr'," C'est une question d'examen bien connue.",'')],
 # Siggi: keine Wartezeit vor dem Atemtest; "wie frueher" ist nicht belegt
 (42529,8): [('de','Eine Bedenkzeit wie früher gibt es nicht mehr.','Eine Bedenk- oder Wartezeit gibt es nicht.'),
             ('fr',"Il n'existe plus de temps de réflexion comme auparavant.","Il n'y a ni temps de réflexion ni délai d'attente.")],
}
Q = {
 366: {'erkl':[('de','und eine Bedenkzeit wie früher gibt es nicht mehr.','und eine Bedenk- oder Wartezeit gibt es nicht.'),
               ('fr',"et un délai de réflexion comme autrefois n'existe plus.","et il n'y a ni temps de réflexion ni délai d'attente.")]},
 370: {'erkl':[('de',' Wer zu hoch klickt, kassiert 5 Fehlerpunkte.',''),('fr'," Celui qui clique trop haut écope de 5 points d'erreur.",'')]},
 377: {'erkl':[('de','Landwirtschaftliche Traktoren sind auf 40 km/h ausgelegt und dürfen','Landwirtschaftliche Traktoren dürfen'),
              ('fr','Les tracteurs agricoles sont conçus pour 40 km/h et ne peuvent','Les tracteurs agricoles ne peuvent')]},
 385: {'q':[('de','im Auto mitzuführen','dabeizuhaben'),('fr',"de n'emporter dans la voiture que des copies","de n'avoir sur toi que des copies")]},
 389: {'q':[('de','Woran erkennst du auf einem Bild am sichersten eine Autobahn?','Woran erkennst du am sichersten, dass du auf einer Autobahn fährst?'),
           ('fr','À quoi reconnaissez-vous le plus sûrement une autoroute sur une image ?','À quoi reconnais-tu le plus sûrement que tu roules sur une autoroute ?')]},
}
# Lektionstitel aus Kurs B ersetzen (Lektions-ID de -> (de, fr))
TITLES = {
 42493: ('Blinkfrequenz', 'Fréquence des clignotants'),
}
