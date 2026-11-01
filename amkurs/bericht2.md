# Kurs Motorrad & Moped (de 44318 / fr 44499) – Bericht 2 (1.11.2026)

## Was gemacht wurde
- **Gesichert:** Vor jeder Änderung wurde der alte Stand gesichert, 44 Einträge in `wp-content/uploads/tr-import/backup-am2-2026-11-01.jsonl`.
- **Geändert:** 40 Lektionen, Quizze und Simulationen, jeweils de und fr. Geändert wurden nur Inhalt und Titel, dazu Fragen und Quiz-Optionen.
- **Status:** unverändert. Der Kurs ist privat; Themen, Lektionen und Quizze sind wie vorher veröffentlicht.
- Es gibt kein WooCommerce-Produkt.
- `wpo_cache_flush()` ist ausgeführt.
- **Quellen nachgezogen** in `amkurs/`:
  - geändert: `new1.py`, `new3.py`, `build_am.py`
  - neu: `patches2.py`
  - Auf GitHub (Commit 5f8091f) liegen jetzt auch die Quellen unter `amkurs/src/` und das Update-Skript `amkurs/update-am2.php`. Es ändert die Inhalte in place und lässt Status und WPML unverändert.
- **Fehler in der Quelle behoben:** In `patches.py` war der Schlüssel `(42304,4)` doppelt vergeben. Dadurch stand in Modul 5 noch zweimal „Das ist eine bekannte Prüfungsfrage“ (de) und einmal „question d'examen bien connue“ (fr). Beides ist jetzt entfernt.
- **Neuer Stand des Kurses:**
  - je Sprache 147 Lektionen, 938 Kacheln
  - Modul 1: 16 Fragen, Modul 2: 22 Fragen
  - Simulation A: 351 Fragen im Pool, 50 werden gezogen. Simulation AM: 309 im Pool, 40 werden gezogen.
  - Jede Frage hat genau eine richtige Antwort.
  - Alle 612 Bild-URLs existieren auf dem Server.
  - de und fr sind gleich aufgebaut.
  - Im uploads-Ordner liegt kein Kursinhalt.

## Die Punkte im Einzelnen
1. **Profiltiefe.** Der KB vom 10.10.1974 (Art. 24) nennt keine Mindesttiefe, nur „ausreichende Tragfähigkeit“. Amtlich belegt sind nur die 1,6 mm in den Hauptrillen: So viel verlangt Autosécurité für das Prüfungsmotorrad (FM2921).
   - Kachel und Merksatz: Die 1 mm für Mofas sind gestrichen. Neu steht dort „Hauptrillen mindestens 1,6 mm (Vorgabe von Autosécurité), guter Mindestwert für jede Fahrt“.
   - Frage zur Profiltiefe: umformuliert auf die Vorgabe von Autosécurité, mit neuen Antwortoptionen.
   - Frage „Mitte abgefahren“: Erklärung vorsichtiger formuliert.
2. **Aufstellfläche.** Amtlich steht sie im Code de la route, **Art. 77.6** (nicht 77.5), und im Schild F14: „zone avancée pour cyclistes et conducteurs de cyclomoteurs à deux roues“. Das gilt also für zweirädrige Mofas der Klassen A und B. Die police.be-Seite zur ZAC sagt dasselbe. Der Kurs ist an allen Stellen stimmig (M3-L3, M3-L10, F14, Fragen 19 und 37). Nichts geändert.
3. **Mindestalter**, laut KB 23.3.1998 Art. 18 (Stand 28.8.2026) und Autosécurité:
   - AM ab 16, A1 ab 18, A2 ab 20, A ab 24 oder ab 22 mit 2 Jahren A2
   - Stufenzugang: jeweils 4 Stunden; mit Führerschein B seit 2 Jahren + 4 Stunden → Code 372 (A1-Fahrzeuge, nur in Belgien)
   - Für 2025/2026 ist keine belgische Änderung in Kraft.
   - Unterschied: Für Inhaber eines in einem anderen EU-Land erworbenen Führerscheins gelten niedrigere Alter (A1 16, A2 18, A 20). Das steht nicht im Kurs, weil es nur einen Sonderfall betrifft.
   - Neu im Kurs: der Hinweis auf Code 372.
   - Bestätigt: Mit Fahrgast muss der Mofafahrer 18 sein (Code Art. 8.2, ab 16 nur ohne Fahrgast).
4. **Theorieprüfung.** Laut KB 23.3.1998, Anhang 4 B:
   - AM: 40 Fragen, bestanden ab 33
   - A1/A2/A: 50 Fragen, bestanden ab 41
   - **Wallonie (und Flandern):** Wer bei A1/A2/A zwei oder mehr Fragen zu Verstößen 3. oder 4. Grades oder zur Geschwindigkeitsüberschreitung falsch beantwortet, ist durchgefallen. „5 Punkte“ für schwere Fehler gibt es nicht.
   - Diese Sonderregel steht jetzt im Kurs: in M1-L4, M15-L1, in den Merksätzen von M1 und M15, in der Erklärung zu M1-Q6 und in einer neuen Frage (nur in Simulation A).
   - Die Simulationen zählen nur Punkte. Das steht jetzt im Beschreibungstext.
   - **Zeitlimits:** Eine amtliche Zeitvorgabe habe ich nicht gefunden. Deshalb habe ich die Zeitlimits entfernt (vorher 13 bzw. 10 Minuten). Die Beschreibung sagt jetzt „ohne Zeitlimit“.
   - Bestehensgrenze bleibt 82 % (41/50 bzw. 33/40).
   - Aus M15-Q13 gestrichen: „Wer zu hoch klickt, kassiert 5 Fehlerpunkte“.
5. **Weitere Punkte, nur wo amtlich belegt:**
   - **Technische Kontrolle:** neu in M1-L6, mit Merksatz und Frage (nur Simulation A). Laut AGW vom 18.11.2022 und wallonie.be: seit 1.1.2023 vor der Zulassung auf einen neuen Halter und nach einem Unfall mit Schäden an Rahmen, Lenkung, Federung oder Bremsen. Gilt für Motorräder über 125 cm³ bzw. elektrisch über 11 kW. Keine regelmäßige Kontrolle.
   - **Anhänger:** neu in M2-L9, mit Merksatz und Frage (nur Simulation A). Laut KB 1974 Art. 15, 16 § 2bis, 13.6bis und KB 1998 Art. 2: mit A1/A2/A erlaubt; hinter dem Motorrad höchstens 1 m breit, hinter dem Mofa 0,75 m; keine Personen im Anhänger; 2 rote Rückstrahler mindestens 40 cm hoch.
   - **Nebelleuchten:** neu in M2-L4, laut KB 1974 Tabelle III am Motorrad erlaubt, aber keine Pflicht.
   - **Rückstrahler:** Der Satz zu Aufklebern war nicht belegt und ist gestrichen. Die Frage fragt jetzt nach dem Rückstrahler hinten.
   - **ABS:** jetzt korrekt als EU-Pflicht formuliert (VO 168/2013: neue Modelle ab 2016, alle neuen Motorräder ab 2017).
   - **Weiter nicht aufgenommen**, weil nicht amtlich belegt: Klapphelm, Zusatz- und Fernscheinwerfer, Ladungsmaße am Motorrad, Tempo mit Anhänger.
6. **Strenge Durchsicht der Module 1, 2, 10 und 15** (Fakten, de/fr in tu-Form, keine Prüfungstipps):
   - Belegt: Wegkapitäne (Art. 43ter, Kapitäne ab 25 Jahren), Busspur beim Abbiegen (Art. 72.5 3° b), 2 Zusatzstunden nach 2 Fehlversuchen und 4 Stunden AM (KB 1998 Art. 15), Fahrverbot am Wochenende nachts unter 24 Jahren (Art. 9), AM-Prüfung auf dem Gelände.
   - M1: Der Satz „gewerbliche Gütertransporte verboten“ beim provisorischen Führerschein war nicht belegt und ist gestrichen.
   - Alkohol, wie von Siggi bestätigt: Testverweigerung wird überall wie die höchste Stufe bestraft. „Bedenkzeit wie früher“ ist ersetzt durch „keine Bedenk- oder Wartezeit“ (M12-L1, M15-Q9). Der fr-Merksatz in M12 war nur auf dem Server korrigiert; jetzt ist er auch in der Quelle richtig.
   - Prüfungstipps und Pkw-Reste entfernt:
     - Lektionstitel „Kleine Zahlen, die oft gefragt werden“ heißt jetzt „Blinkfrequenz“.
     - M15-Q26: „im Auto mitzuführen“ ist ersetzt durch „dabeizuhaben“.
     - M15-Q28: „auf einem Bild“ ist umformuliert.
     - M15-Q19: „Traktoren auf 40 km/h ausgelegt“ ist gestrichen.
   - Kein „vous/votre/vos“ und keine -ez-Form mehr in fr.
7. **Schlussprüfung:**
   - de und fr gleich aufgebaut, Quizlösungen in beiden Sprachen gleich.
   - Bilder vollständig, keine neuen Bilder.
   - Die verbleibenden Erwähnungen von „Auto/Pkw“ sind inhaltlich gewollt (Vorfahrt, Mobilität, Schilder) und keine Pkw-Reste.

## Für Siggi zu entscheiden / zu wissen
- **Kurs B (live) hat noch den alten Stand „schwerer Fehler = 5 Punkte“.** Das steht dort in Fragen und Kacheln, z. B. Kacheln 42285#7 und 42684#4, Fragen 353 und 370. Außerdem enthält Kurs B noch Prüfungstipps, z. B. 42304#4 „bekannte Prüfungsfrage“ und Q370 „Fehlerpunkte“.
  - Laut KB 23.3.1998, Anhang 4 B gilt in der Wallonie für die Klassen B und A die Regel „2 Fehler zu Verstößen 3./4. Grades oder Tempoüberschreitung = durchgefallen“.
  - Der Kurs B sollte angepasst werden; ich habe ihn nicht angefasst.
- **Profil für Mofas:** Eine amtliche Mindesttiefe gibt es nicht; die Angabe „1 mm“ ist deshalb gestrichen. Will Siggi einen Wert aus eigener Praxis nennen?
- **Simulationen ohne Zeitlimit:** Soll es trotzdem ein gekennzeichnetes Übungs-Zeitlimit geben?
- Danach: Kurs öffentlich stellen und Produkt anlegen (macht der Hauptagent).

## Quellen
- KB 10.10.1974 (techn. Bedingungen Mofas/Motorräder): https://www.codedelaroute.be/fr/reglementation/1974101007~kgzr2j7nub
- KB 23.3.1998 (Führerschein): https://www.codedelaroute.be/fr/reglementation/1998014078~w8ylf1lyws
- Code de la route (KB 1.12.1975), Art. 8.2, 36, 43ter, 72.5, 77.6, F14: https://www.codedelaroute.be/fr/reglementation/1975120109~hra8v386pu
- AGW 18.11.2022 techn. Kontrolle 2-/3-Räder: https://wallex.wallonie.be/files/pdfs/19/77038_Arr%c3%aat%c3%a9_du_Gouvernement_wallon_relatif_au_contr%c3%b4le_technique_des_v%c3%a9hicules_%c3%a0_deux_ou_trois_roues_et_des_quadricycles_et_modifiant_l'arr%c3%aat%c3%a9_royal_du_10_octobre_1974_portant_r%c3%a8glement_g%c3%a9n%c3%a9ral_sur_l_01-01-2023-.pdf
- https://www.wallonie.be/fr/demarches/soumettre-son-vehicule-2-ou-3-roues-et-quadricycles-au-controle-technique-lors-de-la-revente-un-particulier-ou-apres-accident
- Autosécurité, techn. Bedingungen Prüfungsmotorrad: https://www.autosecurite.be/uploads/2025/05/FM2921-Conditions-techniques-a-respecter-pour-votre-moto-le-jour-de-lexamen.pdf
- https://www.autosecurite.be/en/permis-de-conduire/permis/category-a1-a2/
- https://www.autosecurite.be/en/permis-de-conduire/permis/category-am/
- https://www.autosecurite.be/uploads/2026/03/2500333-FM2110-Brochure-AM.pdf
- Police, ZAC: https://www.police.be/5337/node/19610
