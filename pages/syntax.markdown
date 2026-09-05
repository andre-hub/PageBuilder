## Syntax

Jede Seite ist eine Datei mit der Endung `.markdown` im Quellverzeichnis.
Der Inhalt wird über `league/commonmark` in HTML umgesetzt; unterstützt werden die CommonMark-Grundelemente und die aktivierte
Tabellenerweiterung: Überschriften mit `#`, Absätze,
Listen, Links, Betonungen, Codeblöcke mit dreifachen Backticks, Tabellen
und Zitate mit `>`. Für Abbildungen kommt zusätzlich eine eigene, knappe
Blocksyntax hinzu, die unten beschrieben ist.

Das Template erzeugt die H1 aus dem Seitentitel. Ohne `PageTitles`-Eintrag
kann eine ATX-Überschrift (`#` bis `######`) als erste nichtleere Quellzeile
den Titel liefern; diese Zeile wird dann aus dem Fließtext entfernt.
Bei einem konfigurierten Titel bleibt sie dagegen stehen. Weitere
Inhaltsabschnitte beginnen sinnvollerweise mit `##`.

H2 bis H4 erhalten Sprungmarken. Tabellen werden in einen fokussierbaren
Scrollbereich gesetzt, damit breite Tabellen bei passendem CSS nicht die
gesamte Seite verbreitern. Weitere Markdown-Erweiterungen wie Aufgabenlisten
sind nicht pauschal aktiviert.

### Abbildungen: `@bild`

Eine Abbildung mit Bildunterschrift steht als eigener Absatz, beginnend
mit `@bild`, gefolgt vom Dateinamen ohne Auflösungszusatz und ohne
Endung, dem Alternativtext und der Bildunterschrift, jeweils durch einen
senkrechten Strich getrennt:

```
@bild wege | Karte mit mehreren Wegen | Wege durch Themen und Gedanken
```

Daraus entsteht eine `<figure>` mit eingebettetem `<img>` (inklusive
`srcset` über alle konfigurierten Auflösungen) und einer `<figcaption>`
mit dem dritten Feld. Der Alternativtext wird als Text in das `alt`-Attribut übernommen.
Für inhaltliche Bilder sollte er die relevante Bildaussage beschreiben,
nicht lediglich die Bildunterschrift wiederholen. Technisch erzwingt der
Parser keinen nichtleeren Alternativtext: fehlt das Feld, entsteht
`alt=""`. Ein rein dekoratives Bild kann einen leeren Alternativtext haben;
die redaktionelle Entscheidung nimmt der Generator nicht ab.

Die Bildunterschrift wird als Text ausgegeben, nicht als weiteres Markdown.
Sie erscheint nur, wenn das dritte Feld nicht leer ist und die Blockart
Bildunterschriften erlaubt. Ein senkrechter Strich im dritten Feld bleibt
Teil der Bildunterschrift.

### Trennbilder: `@trenner`

Ein Trennbild gliedert eine lange Seite optisch, ohne selbst Teil des
Argumentationsflusses zu sein. Es hat keine Bildunterschrift, deshalb
entfällt das dritte Feld:

```
@trenner abschnittsende | Ornamentales Trennmotiv in Blautönen
```

### Dateinamen und Auflösungen

Beide Blöcke verweisen auf einen **Grundnamen** ohne Endung und ohne
Auflösungszusatz. Auf der Festplatte liegen davon mehrere Fassungen in
unterschiedlicher Breite, jede mit dem Grundnamen, einem Bindestrich und
einem Auflösungskürzel:

```
wege-sml.webp
wege-mid.webp
wege-lrg.webp
wege-xl.webp
```

Welche Kürzel es gibt, welche Breite jedes davon hat und welche Fassung
im `src`-Attribut als Standardgröße verwendet wird, legt die Konfiguration
fest (siehe [Konfiguration](/konfiguration)). Für den Text im Markdown
genügt in jedem Fall der Grundname — `@bild wege | …`, nicht
`@bild wege-lrg | …`. So bleibt die Quelle unabhängig davon lesbar, ob
später eine weitere Auflösung ergänzt wird.

Das gewählte Bildformat, hier `.webp`, ist ebenfalls Teil der
Konfiguration und nicht Teil der Syntax im Text.

### Zulässige Namen

Der Grundname muss mit einem ASCII-Buchstaben oder einer Ziffer beginnen;
danach sind ASCII-Buchstaben, Ziffern, Punkt, Bindestrich und Unterstrich
erlaubt. Das schließt Pfadwechsel und ähnliche
Überraschungen im erzeugten `src`-Attribut von vornherein aus. Ein
unzulässiger Name oder ein unbekanntes Schlüsselwort vor dem `@` führt zu
keinem Fehler — die Zeile bleibt dann ein gewöhnlicher Textabsatz und
fällt beim Korrekturlesen der gebauten Seite sofort auf.

### Inhaltsverzeichnisse

Ein Inhaltsverzeichnis entsteht **nicht** auf jeder Seite, sondern nur
dann, wenn eine Seite zwei Schwellen gleichzeitig erreicht: eine
Mindestlänge des verbleibenden Markdown-Quelltexts in Bytes und eine Mindestanzahl an
Überschriften zweiter und dritter Ordnung. Beide Schwellen lassen sich
pro Site in der Konfiguration einstellen; erst wenn eine Seite tatsächlich
lang genug und gegliedert genug ist, lohnt sich die zusätzliche
Navigationshilfe.

Ist ein Inhaltsverzeichnis fällig, setzt der Generator es automatisch
hinter den ersten Absatz der Seite — steht direkt danach ein `@bild` oder
`@trenner`, rückt das Verzeichnis hinter dieses Einstiegsbild, weil es
noch zur Einleitung gehört. Wer die Platzierung selbst bestimmen will,
schreibt an die gewünschte Stelle im Markdown-Quelltext den Platzhalter
`[TOC]` als eigenen Absatz; ist der Platzhalter einmal gesetzt, unterbleibt
die automatische Platzierung, unabhängig von Länge und Gliederung der
Seite.

Ohne ausdrücklichen Platzhalter bekommt ein zu kurzer oder zu wenig
gegliederter Text kein Inhaltsverzeichnis — das ist beabsichtigt: Auf einer kurzen Seite wäre ein
Verzeichnis reiner Leerlauf zwischen Einleitung und dem einzigen Absatz,
den es gäbe.

In der Demo liegen die Schwellen bei 4500 Bytes und vier H2-/H3-Abschnitten;
die gegebenenfalls als Seitentitel entfernte Überschrift zählt dabei
nicht mit. Das Verzeichnis enthält H2 und H3, nicht H4. Schon ein
Vorkommen von `[TOC]` in einem Codebeispiel verhindert aktuell die
automatische Einfügung; das ist eine Grenze der Quelltextheuristik.

### Bilddateien und vertrauenswürdige Inhalte

PageBuilder erzeugt nur die Bildauszeichnung mit `src`, `srcset`, `sizes`,
`loading` und konfigurierten Maßen. Bilder werden weder skaliert noch
konvertiert; alle referenzierten Varianten müssen separat bereitliegen.

Roh-HTML ist nicht allgemein gesperrt. Verarbeite deshalb nur
vertrauenswürdige eigene Quellen. Der Builder ist kein HTML-Sanitizer und gewährleistet keine sichere
Einbettung beliebigen fremden Markdowns.
