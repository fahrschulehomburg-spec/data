# Kurs „TheorieRoute: Online-Theoriekurs Motorrad & Moped (A, A1, A2, AM)“ – Bericht (1.11.2026)

## Ergebnis
- Kurs de **44318** / fr **44499**: Status privat, WPML verknüpft (trid 181251), Kursbild Attachment 44319 (sit-13cd3f86.jpg). Kein WooCommerce-Produkt, keine mu-plugins geändert. Kurs B wurde nicht verändert.
- 15 Module, je Sprache 147 Lektionen, 935 Kacheln und 17 Quizze mit 1005 Fragen (davon 348 + 309 Kopien in den Simulationen). Jede Frage hat genau eine richtige Antwort, alle Bild-URLs existieren, die Struktur ist in de und fr gleich.
- IDs: `amkurs/ids.json`. Zuordnung Kurs B → neuer Kurs: `amkurs/zuordnung.md`.
  - Kacheln: 784 übernommen, 33 angepasst, 249 gestrichen.
  - Fragen: 247 übernommen, 29 angepasst, 71 gestrichen.
- Quellen im Arbeitsordner: build_am.py, sel.py, patches.py, new1-3.py, tufr.py. Auf GitHub liegt alles unter `amkurs/` (Stand 963a143). Import-Skript: `tr-import/import-am.php`.
  - Die JSON-Daten lädt das Skript per GitHub-SHA. Im uploads-Ordner liegt kein Kursinhalt.
- `wpo_cache_flush()` ist ausgeführt.

## Aufbau
1. **Motorrad, Moped & Führerschein** (neu): Fahrzeugklassen, AM/A1/A2/A, Weg zu AM und zu A, provisorischer Führerschein, Papiere, Versicherung und Kennzeichen S-A/S-B/S-P.
2. **Schutzkleidung, Ausrüstung & Technik** (neu, dazu Licht und Ladung aus Kurs B): Helm, Pflicht-Schutzkleidung, Beifahrer und Kinder, Licht und Rückstrahler, Technik-Check (Reifen 1,6 mm bzw. 1 mm, DOT, Kette), Ladung.
3. bis 9. aus Kurs B, angepasst:
   - 3: Straßenraum, dazu die neue Lektion „Wo Kleinkrafträder fahren dürfen“
   - 4: Vorfahrt
   - 5: Überholen und Ampeln, dazu „Motorrad im Verkehrsfluss“ (Position, Fahren zwischen den Reihen höchstens 50 km/h und höchstens +20 km/h, B19)
   - 6: Tempo, dazu „Tempo auf zwei Rädern“
   - 7: Fußgänger
   - 8: Parken, dazu „Motorrad und Kleinkraftrad abstellen“
   - 9: Autobahn
10. **Fahrtechnik** (neu): Blick und Kurven, Bremsen und ABS, toter Winkel, Wetter, Gruppenfahren mit Wegkapitänen (15 bis 50 dürfen, über 50 mindestens zwei), Müdigkeit.
11. bis 14. aus Kurs B: Defensiv fahren, Alkohol und Sanktionen, Erste Hilfe (neu: Helm abnehmen, nichts zu trinken geben), Verkehrszeichen (neu: Fragen zu C5, C7, C9, E9i).
15. **Prüfung & Prüfungssimulation**:
    - Info zu den Prüfungen: A1/A2/A mit 50 Fragen, bestanden ab 41; AM mit 40 Fragen, bestanden ab 33; Praxis Gelände und Straße.
    - Quiz mit 30 Fragen.
    - **Simulation A** (de 44497 / fr 44677): 50 Zufallsfragen aus allen Modulen, 82 %, 13 Minuten.
    - **Simulation AM** (de 44498 / fr 44678): 40 Zufallsfragen, ohne die nur für das Motorrad geltenden Fragen, 82 % (also 33 von 40), 10 Minuten.
- Überblick und Merksätze habe ich in allen Modulen neu geschrieben.
- Französisch in tu-Form: neue Texte direkt in tu; übernommene Texte mit Umwandlungsskript, danach geprüft.
  - Kontrolle: kein „vous“, „votre“ oder „vos“ mehr. Die -ez-Formen sind bereinigt, einige Sonderfälle von Hand korrigiert.
- Entfernt aus Kurs B:
  - Pkw-Technik, Gurt und Kindersitz, Anhänger B/BE, Warndreieck und Pflichtzubehör Auto
  - provisorischer Führerschein B, Prüfung B und Risikotest
  - Lkw- und Bus-Sonderregeln
  - Prüfungstipps („Prüfungsfrage/-falle“, „in der Prüfung …“) in de und fr
  - „Ab 2027 angekündigt“ (0,2 ‰ für Fahranfänger, nicht belegt)
- Buch buch_a_am (Ausgabe 2016, 390 Fragen): Den Prüfungsstoff habe ich mit eigenen Fragen abgedeckt. Beispiele:
  - Helm festmachen, Klasse-A-Helm, Kinder unter 3 und von 3 bis 8, 750 cm³
  - Radwegregeln Klasse A und B, Aufstellfläche, Busspur, D9/D10
  - Filtern mit 50/20 km/h, Gruppe 26/52 Motorräder, Kapitän an der Ampel
  - Reifendruck kalt bzw. beladen, Profil in der Mitte, Tank, Bremsen und Auskuppeln, toter Winkel, Kurven, Zebrastreifen bei Regen
  - Ladung Mofa, Parkscheibe, GPS auf dem Radweg, Kaffee und Alkohol, Medikamente
  - Schilder C5/C7/C9/E9i, Sachschaden räumen, Fahrerflucht
  - Allgemeine Fragen (Vorfahrt, Schilder, Tempo) sind schon durch Kurs B abgedeckt.
  - Veraltete Buchstände habe ich nicht übernommen: Wartezeit vor dem Atemtest, 3/6 h Fahrverbot, grüne Karte, Anhaltewege des Buchs. Es gilt der Kursstand (12 h, (v/10)²).

## Bilder
- Wiederverwendet: Kurs-B-Bilder mit Moped- und Motorrad-Motiven, amtliche Schilder (2026/10/tr-m14-*).
- Neu mit FLUX (alle geprüft: Rechtsverkehr, Helm, Schutzkleidung):
  - sit-13cd3f86 (Ausrüstung, Kursbild)
  - sit-a54d5b43 (Kinnriemen)
  - sit-38f610a7 (Beifahrer)
  - sit-f55fced9 (Reifendruck)
  - sit-e3c217ed (Kurve)
  - sit-97146fd9 (Roller)
  - sit-ad77f6e7 (Regen/Zebrastreifen, 2. Versuch)
  - sit-b974ad16c (Speed Pedelec, zugeschnitten, weil im Original das Auto auf der Gegenspur wirkte)
- Verworfen: Regen Version 1 (Fahrspur unklar), Roller Version 1 (zu weit links), beide Schulterblick-Bilder. Dafür gibt es eine Merkkarte.
- Pkw-Innenraum-Fotos in den Einleitungen von 3 übernommenen Lektionen habe ich entfernt.

## Zweifel / bitte prüfen
1. **Mindestprofiltiefe Motorrad 1,6 mm / Mofa 1 mm**: Belegt sind nur readytoroad.be und die Antwortoptionen des Buchs, kein amtlicher Text gefunden. Eine Kachel und zwei Fragen hängen daran.
2. **Aufstellfläche (Fahrradaufstellfeld) auch für zweirädrige Mofas der Klasse B**: Das schließe ich aus dem Buch („opstelvak voor fietsers en tweewielige bromfietsen“). Einen amtlichen Text habe ich nicht gefunden.
3. **Mindestalter A2 = 20**: So steht es bei Autosécurité, Touring und vlaanderen.be. Das ist ungewöhnlich (EU-Mindestalter 18), aber bestätigt.
4. **Fahrgast auf dem Mofa erst ab 18** und **Mofa Klasse A ab 16 ohne Führerschein**: Das habe ich aus Kurs B übernommen, nicht neu geprüft.
5. **Rückstrahler-Farben** und **ABS-Pflicht ab 2016 für neue Motorräder über 125 cm³** (EU-VO 168/2013): Allgemeinwissen, ohne belgische Quelle zitiert.
6. **Theorie A**: Ob dort wie bei B schwere Fehler 5 Punkte zählen, ist amtlich nicht geklärt. readytoroad.be sagt nein. Im Kurs steht deshalb nur „bestanden ab 41 von 50“.
7. **Zeitlimits der Simulationen** (13 bzw. 10 Minuten) sind übernommen bzw. geschätzt, keine amtlichen Werte.
8. Nicht aufgenommen, weil nicht sicher klärbar:
   - Motorrad-Anhänger (Maße und Tempo)
   - Ladungsmaße am Motorrad
   - hochgeklappter Klapphelm
   - Zusatzscheinwerfer und Nebelschlussleuchte am Motorrad
   - Parkgebühren für Motorräder
   - technische Kontrolle Motorrad
9. Der neue Wegcode tritt laut Presse am **1.6.2027** in Kraft. Der Kurs gibt den Stand vor diesem Datum wieder.
10. Beim Import gab es Warnungen von WPML („gzuncompress(): data error“, FieldCompression.php) und vom Theme (custom-fields.php). Die Inhalte sind trotzdem vollständig.

## Quellen
- https://www.autosecurite.be/en/permis-de-conduire/permis/category-a1-a2/
- https://www.autosecurite.be/en/permis-de-conduire/permis/category-am/
- https://www.autosecurite.be/uploads/2026/03/2500333-FM2110-Brochure-AM.pdf
- https://www.autosecurite.be/en/driving-licence/
- https://www.touring.be/fr/articles/comment-obtenir-le-permis-moto-wallonie
- https://www.vlaanderen.be/mobiliteit-en-openbare-werken/auto-en-motor/rijbewijzen-en-rijopleiding/rijbewijs-a1-a2-of-a-voor-motoren-vanaf-50-cc/rijbewijs-a1-a2-of-a-behalen-stap-voor-stap
- https://www.police.be/5998/fr/actualites/securite-routiere-equipements-de-protection-a-moto-et-autres-vehicules
- https://www.touring.be/fr/articles/lequipement-du-motard
- https://www.politie.be/5388/sites/5388/files/media/file/2025-05/Fietspad%20-%20fietssuggestiestrook%20-%20fietsstraat_0.pdf
- https://www.vlaanderen.be/mobiliteit-en-openbare-werken/te-voet-fiets-bromfiets/speedpedelec
- https://www.vlaanderen.be/nummerplaat-voor-bromfiets-lichte-vierwieler-of-speedpedelec
- https://www.readytoroad.be/theorie/moto/theorie-permis-moto-partie-1/ (nicht amtlich)
- https://newmobility.news/2025/10/06/belgiums-new-road-code-delayed-to-2027/

## Urteil
**Freischaltbereit: ja, unter Vorbehalt.** Technisch ist der Kurs vollständig und geprüft. Vorher sollte Siggi die Zweifel 1 und 2 kurz bestätigen (Profiltiefe Motorrad, Mofa Klasse B in der Aufstellfläche) und die neuen Module 1, 2, 10 und 15 einmal querlesen. Danach kann er den Kurs öffentlich stellen und ein Produkt anlegen.
