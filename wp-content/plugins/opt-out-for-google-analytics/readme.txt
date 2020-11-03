=== Google Analytics Opt-Out (DSGVO / GDPR) ===
Contributors: schweizersolutions
Tags: google analytics, opt-out, dsgvo, gdpr, analytics
Requires at least: 3.5
Tested up to: 5.3.2
Requires PHP: 5.4
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://schweizersolutions.com/

Erlaubt es dem Benutzer sich aus dem Google Analytics Tracking auszuschließen (Opt-Out). DSGVO / GDPR.

== Description ==

Die Datenschutz Grundverordnung (DSGVO, EU-DSGVO, GDPR) sieht vor, dass ein Seitenbesucher die Möglichkeit haben muss, der Erfassung durch Google Analytics zu widersprechen.
Bisher war dieses nur per Browser Addon, oder komplizierten JavaScript-Code-Einbindungen auf der eigenen Webseite möglich. Mit diesem Plugin geht dies kinderlich und der Benutzer hat auch noch die Möglichkeit den Widerspruch rückgängig zu machen.

**Funktionsumfang**

* **Vollständige Integration der neuen WordPress DSGVO-Funktionen**
* Wöchentliche Überprüfung, ob die Einstellungen noch datenschutzkonform sind!
* Kompatibel mit dem Google Tag Manager
* **KEIN weiteres Plugin** nötig um den Google Analytics Code auf der Webseite zu verwenden. Dieser kann von diesem Plugin direkt eingebunden werden.
* Seitenbesucher kann das Google Analytics Tracking für sich deaktivieren und auch nachträglich wieder aktivieren
* Linktext für den Aktivierungs- und Deaktivierungslink kann individuell verändert werden
* Es kann ein Popup eingerichtet werden, welches nach dem Klick des Links erscheint
* Der UA-Code kann manuell eingetragen werden oder autom. von einem Google Analytics Tracking Plugin ausgelesen werden (siehe kompatible Plugins)
* HTML5 Local Storage Fallback: Löscht ein Nutzer seine Cookies, so kann der Opt-Out Cookie wiederhergestellt werden, wenn der Local Storage vom Browser nicht zusätzlich gelöscht wurde.
* Wordpress Multisite kompatibel
* Vollständig kompatibel mit dem Gutenberg Editor
* Kompatibel mit: [Advanced Custom Fields](https://de.wordpress.org/plugins/advanced-custom-fields/), [WPML](https://wpml.org/?utm_source=wordpressorg&utm_medium=opt-out-for-google-analytics), [Polylang](https://de.wordpress.org/plugins/polylang/) und [Loco Translate](https://de.wordpress.org/plugins/loco-translate/)
* Funktioniert auch auf dem Smartphone, sofern der Browser Cookies unterstützt.
* Optische Anpassungen durch benutzerdefinierte CSS Codes, die nur zusammen mit dem Shortcode geladen werden (optimierte Ladezeit)
* Übersetzungsdateien für andere Sprachen vorhanden

**Regelmäßige Überprüfung, ob die Einstellungen noch datenschutzkonform sind!**

Schon einmal die Seite mit der Datenschutzerklärung bearbeitet und den Opt-Out Shortcode ausversehen gelöscht? Letztens noch das Plugin für das Google Analytics Tracking gelöscht, anschließend neu installiert und die IP-Anonymisiserung vergessen wieder zu aktivieren?

Um hier die höchste Sicherheit zu gewähren, prüft das Plugin regelmäßig die Einstellungen. Sollte eine Einstellung nicht mehr passen, dann erscheint im WP Admin (Backend / Dashboad) eine Fehlermeldung oder Du bekommst eine E-Mail zugesendet.
Die Häufigkeit der Überprüfung kannst Du frei wählen. Dazu stehen dir folgende Intervalle zur Verfügung: täglich, wöchentlich oder monatlich.

Folgende Einstellungen werden geprüft:

* Opt-Out Funktion aktiviert
* Opt-Out Shortcode auf der Seite vorhanden
* Seite mit dem Shortcode öffentlich zugänglich (Veröffentlicht und kein Passwortschutz)
* Gültiger UA-Code gefunden (Es wird nur die Formatierung geprüft)
* IP-Anonymisierung ativiert (Funktioniert nur in Verbindung mit einem kompatiblen Plugin oder der TRacking-Code ist im Plugin hinterlegt)

**Integrierte Kompatibilität mit folgenden Plugins**

Es ist keine Voraussetzung die aufgelistet Plugins zu verwenden! Das Google Analytics Opt-Out Plugin ist auch kompatibel mit anderen Plugins und kann auch genutzt werden, wenn der Google Analytics Code selber eingefügt wurde.
Mit der integrierten Kompatibilität erleichtern wir die Arbeit, weil der aktuelle UA-Code automatisch ausgelesen und aktuell gehalten wird. Dadurch muss dieser nicht händisch korrigiert werden.

* [MonsterInsights Pro](https://www.monsterinsights.com/?utm_source=wordpressorg&utm_medium=opt-out-for-google-analytics)
* [Google Analytics for WordPress by MonsterInsights](https://wordpress.org/plugins/google-analytics-for-wordpress/)
* [Google Analytics Dashboard for WP by ExactMetrics (formerly GADWP)](https://wordpress.org/plugins/google-analytics-dashboard-for-wp/)
* [Google Analytics Dashboard Plugin for WordPress by Analytify](https://wordpress.org/plugins/wp-analytify/)
* [GA Google Analytics](https://wordpress.org/plugins/ga-google-analytics/)
* [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/)

**Vorhandene Übersetzungen**

* Deutsch (de_DE)
* Englisch (en_EN)

**AUTOMATISCH aktuelle Datenschutzerklärung**

Den Überblick zu behalten, bei den ganzen DSGVO-Gesetzesänderungen ist nicht leicht. Besonders nicht neben dem Kerngeschäft. Deswegen bieten wir Dir mit unserem Partner [easyRechtssicher](https://schweizer.to/datenschutzgenerator) einen Datenschutz-Generator an.
Die Datenschutzerklärung wird EINMALIG angelegt und automatisch in WordPress selbst auf dem neusten Stand gehalten. Kein erneutes ausfüllen von Formularen und das Kopieren und Einfügen von Datenschutzerklärungen auf die Seite, es läuft völlig automatisiert.
Mehr Infos dazu hier: [https://schweizer.to/datenschutzgenerator](https://schweizer.to/datenschutzgenerator)

**Gefällt dir?**
Es motiviert uns sehr, weiter an unseren kostenlosen Plugins zu arbeiten, wenn Du uns eine [positive Bewertung](https://wordpress.org/support/plugin/opt-out-for-google-analytics/reviews/#new-post) hinterlässt.

**Coded with love by** [Schweizer Solutions GmbH](https://schweizersolutions.com/?utm_source=wordpressorg&utm_medium=plugin)

== Installation ==

**Installation über Wordpress**

1. Gehe ins Dashboard: `Plugins > Installaieren`
2. Suche nach: `Opt Out for Google Analytics`
3. Klicke dort auf den grauen Button `Installieren`
4. Aktiviere das Plugin

**Manuelle Installation**

1. Lade das Verzeichnis `ga-opt-out` ins `/wp-content/plugins/` Verzeichnis deiner Wordpress Insallation
2. Aktiviere das Plugin

**Konfiguration**

1. Gehe ins Dashboard: `Einstellungen > GA Opt-Out`
2. Aktiviere das Plugin, falls nicht aktiviert
3. Wähle den UA-Code aus oder trag ihn ins Feld ein
4. Änderungen speichern, fertig!

**Anmerkung zum UA-Code:**

Solltest du den Google Analytics Tracking-Code manuell eingetragen haben, z.B. bei einem Wordpress-Theme, dann musst du den UA-Code ins Eingabefeld eintragen.
Hast du eins, der drei kompatiblen Plugins aktiviert, dann ist dieser Punkt auch auswählbar. Dadurch wird automatisch der aktuelle UA-Code von diesem Plugin ausgelesen und du musst diesen nicht ins Eingabefeld eintragen.

== Frequently Asked Questions ==

= Warum sollte ich dieses Plugin verwenden? =

Die Datenschutz Grundverordnung (DSGVO, EU-DSGVO, GDPR) sieht vor, dass ein Seitenbesucher die Möglichkeit haben muss, der Erfassung durch Google Analytics zu widersprechen.
Bisher war dieses nur per Browser Addon, oder komplizierten JavaScript-Code-Einbindungen auf der eigenen Webseite möglich.
Mit diesem Plugin geht dies kinderlich und der Benutzer hat auch noch die Möglichkeit den Widerspruch rückgängig zu machen.

= Ist es der gleiche Opt-Out Code wie von e-recht24.de? =

Ja, weil wir uns ebenfalls an die Vorgaben von Google halten. Weitere Informationen zu den Vorgaben: https://developers.google.com/analytics/devguides/collection/analyticsjs/user-opt-out

= Ich habe den Google Analytics Tracking-Code über das Theme / ein Plugin hänidsch eingefügt. Kann ich dieses Plugin trotzdem nutzen? =

Dieses Plugin kann auch genutzt werden, wenn der Tracking-Code händisch beim Theme oder mit Hilfe eines Plugins eingefügt wurde.

= Muss die Datenschutzerklärung angepasst werden? AUTOMATISCH aktualisieren? =

Ja, es ist empfehlenswert bei der Datenschutzerklärung das Opt-Out für Google Analytics anzubieten.

Den Überblick zu behalten, bei den ganzen DSGVO-Gesetzesänderungen ist nicht leicht. Besonders nicht neben dem Kerngeschäft. Deswegen bieten wir Dir mit unserem Partner [easyRechtssicher](https://schweizer.to/datenschutzgenerator) einen Datenschutz-Generator an.
Die Datenschutzerklärung wird EINMALIG angelegt und automatisch in WordPress selbst auf dem neusten Stand gehalten. Kein erneutes ausfüllen von Formularen und das Kopieren und Einfügen von Datenschutzerklärungen auf die Seite, es läuft völlig automatisiert.
Mehr Infos dazu hier: [https://schweizer.to/datenschutzgenerator](https://schweizer.to/datenschutzgenerator)

= Wie lange behält das Austragen seine Gültigkeit? =

Klickt der Seitenbesucher auf den Opt-Out Link, um Google Analytics für Ihn zu deaktiveren, dann wird ein Cookie gesetzt. Mit diesem Cookie weiß das System, dass dieser Seitenbesucher auf der Webseite nicht getrackt werden soll.

Dieses Cookie ist nur in dem Browser gültig, mit dem der Seitenbesucher auf der Webseite war und auf den Opt-Out Link geklickt hat. Nutzt dieser einen anderen Browser, müsste er auch hier noch mal auf den Link klicken.

Leert der Seitenbesucher seine Browserdaten (Cookies, Downloadverlauf etc.), dann ist das Cookie ebenfalls gelöscht und der Seitenbesucher müsste erneut auf den Opt-Out Link klicken.

= Wie deaktiviere ich das Plugin? =

Den Status vom Plugin kannst Du unter "Einstellungen > GA Opt-Out" deaktivieren, dort muss nur der Haken bei "Opt-Out Funktion aktivieren" entfernt werden.
Das ganze Plugin deaktivierst du unter "Plugins > Installierte Plugins", indem Du dort bei dem Google Analytics Opt-Out Plugin auf "Deaktivieren" klickst.
Nach dem Deaktivieren kannst Du mit einem Klick auf "Löschen" das ganze Plugin komplett entfernen.

= Wo kann ich den Shortcode verwenden? =

Den Shortcode `[gaoo_optout]` kannst du in den Beiträgen, auf den Seiten und bei den Widgets (Text-Widget) verwenden.

= Kann ich als Entwickler ins Plugin eingreiffen? =

Ja, du kannst. Dazu haben wir entsprechende Filter und Action Hooks eingebaut.

`// Bevor der Shortcode aufgelöst wird
add_action( 'gaoo_before_shortcode', 'my_before_shortcode', 10, 2);

function my_before_shortcode( $ua_code, $current_status ) {
	// $ua_code - Der verwendete UA-Code "UA-XXXXX-Y"
	// $current_status - Der aktuelle Status vom Seitenbesucher: activate oder deactivate
}

// Nachdem der Shortcode aufgelöst wird
add_action( 'gaoo_after_shortcode', 'my_after_shortcode', 10, 2);

function my_after_shortcode( $ua_code, $current_status ) {
	// $ua_code - Der verwendete UA-Code "UA-XXXXX-Y"
	// $current_status - Der aktuelle Status vom Seitenbesucher: activate oder deactivate
}

// Bevor der JS-Code, zum Deaktivieren von GA, ausgegeben wird
add_action( 'gaoo_before_head_script', 'my_before_script', 10, 1);

function my_before_script( $ua_code ) {
	// $ua_code - Der verwendete UA-Code "UA-XXXXX-Y"
}

// Nachdem der JS-Code, zum Deaktivieren von GA, ausgegeben wird
add_action( 'gaoo_after_head_script', 'my_after_script', 10, 1);

function my_after_script( $ua_code ) {
	// $ua_code - Der verwendete UA-Code "UA-XXXXX-Y"
}

// Der Verwendete UA-Code
add_filter( 'gaoo_get_ua_code', 'my_ua_code', 10, 2 );

function my_ua_code( $ua_code, $ga_plugin ) {
	// $ua_code - Der verwendete UA-Code "UA-XXXXX-Y"
	// $ga_plugin - Ausgewählte Quelle für den UA-Code
}

// Ob die Seite nach dem Klick neu geladen werden soll
add_filter( 'gaoo_force_reload', 'my_force_reload', 10, 1);

function my_force_reload( $force ) {
	// $force - "true" = Neuladen erzwingen; "false" = nicht neuladen
}
`

= Mein Theme löst keine Shortcodes auf. Wie kann ich die Funktion über PHP nutzen? =

Es kommt vor, dass einige selbstentwickelten Themes keine Shortcodes auflösen und das Plugin dadurch nicht funktioniert.
Um den Shortcode trotzdem ausführen zu lassen, muss im PHP-Code vom Theme, an der gewünschten Stelle dieser Code verwendet werden:

`echo do_shortcode('[ga_optout]');`

= Es wird angezeigt, dass die Einstellungen nicht korrekt sind, obwohl ich diese geändert habe! =

Sollten die Einstellungen z.B. bei einem kompatiblen Google Analytics Tracking-Plugin geändert worden sein, dann wird diese Änderung erst bei der autom. Prüfung (wöchentl.) auffallen.

Es besteht die Möglichkeit, die Prüfung vorher durchzuführen. Dazu müsste unter "Einstellungen > GA Opt-Out" der Button "Änderungen speichern" geklickt werden.

Dadurch werden die neuen Einstellungen eingelesen und enstprechend für eine Woche gespeichert, bis zur nächsten autom. Prüfung.

= Wie kann ich auf die Klicks reagieren, z.B. mit einem anderen Plugin? =

Es wird das JavaScript-Event `gaoptout` auf das `window` Objekt gefeuert, auf welches reagiert werden kann.

Klickt der Seitenbesucher auf den Link, um einen Opt-Out durchzführen, dann wird ein `false` als Wert übergeben. Sollte dieser sich umentscheiden und ein Opt-In durchführen, dann wird ein `true` übergeben.

Beispielcode:
`jQuery(window).on('gaoptout', function (e) { console.log(e.detail); });`

= Wie verändere ich das Aussehen von dem Link? =

Du hast die Möglichkeit über CSS die Darstellung von dem Link, in den Einstellungen des Plugins, zu verändern. Dazu stehen dir folgende CSS-Klassen zur Verfügung:

**Der Link selbst:**
`#gaoo-link { ... }`

**Der Link, wenn der Seitenbesucher dem Tracking widersprochen hat:**
`.gaoo-link-activate { ... }`

**Der Link, wenn der Seitenbesucher dem Tracking NICHT widersprochen hat:**
`.gaoo-link-deactivate { ... }`

= Wie kann ich die Statusmeldungen an mehrere E-Mail-Adressen schicken? =

Du kannst ins Eingabefeld für die E-Mail durch ein Komma getrennt mehrere Empfängeradressen eintragen, die die Statusmeldung erhalten sollen.
Beispiel: `webmaster@beispiel.de,admin@beispiel.de,dev@beispiel.de`

= Ich erhalte keine E-Mails über die Statusprüfung? =

Die Statusprüfung läuft in dem eingestellten Intervall und versendet nur E-Mails, wenn mindestens eins Punkt auf rot steht.
Sollten dennoch keine E-Mails ankommen, so kann es folgende Ursachen haben:

- Die E-Mail ist in Deinen Spam-Ordner gelandet. Bitte prüf das vorher und speicher die Absenderadresse in dein Kontaktbuch, dadurch verhinderst Du das die E-Mails im Spam landen.
- Du hast in der wp-config.php den Cronjob von WordPress deaktiviert. Stell bitte sicher, dass der serverseitige Cronjob korrekt funktioniert.
- Du nutzt ein Caching Plugin auf der Installation. Sollte kein serverseitigee Cronjob eingerichtet sein, dann prüft WordPress bei jedem Seitenaufruf ob Aufgaben anstehen. Wird die Seite aus dem Cache aufgerufen, dann erfolgt dies nicht mehr und es kann kein Cronjob angestoßen werden.
- Du hast zu wenige Seitenbesucher, die in zu großen Zeitabständen Deine Webseite aufrufen. Dadurch wird der Cronjob von WordPress nicht angestoßen.

Unsere Empfehlung ist daher:

- Richte einen serverseitigen Cronjob ein
- Trage die Absenderadresse in dein Kontaktbuch ein

= Google Tag Manager verwenden =

Der Opt-Out Cookie wird nach Vorgaben von Google gesetzt. Somit müsste eigtl. beim GTM (Google Tag Manager) keine Anpassung stattfinden.
Sollte der Google Analytics Code aber gar nicht mitgeladen werden, so muss beim GTM geprüft werden, ob der Cookie gesetzt ist oder ob der Wert in der "Local Storage" entsprechend gesetzt ist.
Auf dieser Basis kann im GTM entschieden werden, ob der Code geladen werden soll oder nicht.

Ist kein Eintrag oder Cookie verohanden, dann ist kein Opt-Out erfolgt. Dies ist ebenfalls der Fall, wenn der Wert "false" zurückgeben wird.
Der Opt-Out ist erst erfolgt, wenn der Wert "true" zurückgegeben wird.

Spezifischer Cookie, mit dem entsprechendem UA-Code: ga-disable-UA-XXXXX-YY
Generischer Cookie: ga-opt-out

Spezifischer Eintrag in der Local Storage:  ga-disable-UA-XXXXX-YY
Generischer Eintrag in der Local Storage: ga-opt-out

= Haftung / Disclaimer =

Die Verwendung dieses Plugins erfolgt auf eigener Gefahr. Der Webseitenbetreiber muss die Funktionalität des Plugins selber sicherstellen können.
Dazu muss unteranderem geprüft werden, ob nach dem Klick auf den Link, ein Cookie in diesem Format gesetzt wurde: ga-disable-UA-XXXXXXXX-YY
Unterstützend, für den Google Tag Manager, wird noch ein Cookie mit dem Namen "ga-opt-out" gesetzt.

== Screenshots ==

1. Einstelungsübersicht des Plugins (Dashboard: Einstellungen > GA Opt-Out)
2. Shortcode im Texteditor eingefügt
3. Aktivierungs-/Deaktivierungslink mit Popup, auf der Seite
4. Beispiel für eine Status E-Mail

== Changelog ==

= 1.5 =
* Added: Support for Site Kit by Google
* Added: Output the current UA-Code in status check
* Added: Send status mail to WordPress admin mail (auto-sync)
* Updated: Compatibility to new version of "Google Analytics Dashboard for WP by ExactMetrics (formerly GADWP)", with backward support for older versions
* Updated: Compatibility to new version of "Google Analytics Dashboard Plugin for WordPress by Analytify", with backward support for older versions
* Fixed: German translations

= 1.4 =
* Added: Support for Gutenberg
* Added: Support for ACF Plugin, to check if the shortcode exist in the fields
* Added: Send e-mail if the status check has detected an error.
* Added: The intervall for the status check can now be changed.
* Added: Add  Google Analytics to the whole website if the option "UA-Code" is set to manual.
* Added: Custom JavaScript event on window object to allow other plugins to react if a user clicks on the link.
* Added: User can add custom css in the settings, which is only loaded if the shortcode is used.
* Fixed: Used wrong version variable to compare if WordPress has the new GDPR features.
* Fixed: Only redirect after activation if plugin is activated single and not in bulk
* Fixed: apply_filters for the link text did not affect on initinal page load.
* Updated: Changed the link to the new data processing agreement for Google Analytics page.
* Improved: Edit link from the "Privacy Policy Page" select has now the link to the current selected page, to open
* Improved: Some usability features & security fixes
* Improved: Move json data generation into a static function, so developers can enqueue scripts on every site they want.

= 1.3 =
* Added: Support for WordPress 4.9.6 GDPR - Icons for the shortcode on the privacy page & sync. the page id for the shortcode.
* Added: Check if the page with the shortcode is accessibile
* Added: MonsterInsights Pro support
* Added: WPML & Polylang compatibility
* Added: HTML5 local storage fallback. Cookies deleted, but the local storage not, restore cookie.
* Added: Possibility to disable the notice on dashboard, the settings aren't right.
* Added: Possibility to force page reload, after link click.
* Added: "Google Tag Manager" as an option.
* Improved Google Tag Manager support: Set generic cookie named "ga-opt-out" if user clicked opt-out. Allows yout to copy and paste the code in GTM, no need to change the UA-Code.
* Fix: PHP error generated on older PHP (<5.3) versions
* Several code and usability optimazation

= 1.2 =
* Fixed: Activation immediately after installation generated a warning message
* Changed: The message, if the settings aren't data protection compliant, is now only visible to the admins and super admins
* Added: Backward compatibility for older "Google Analytics Dashboard for WP (GADWP)" versions
* Added: Some status checks are now linked to the specific page. You are now one click away to enable the ip anonymization!

= 1.1 =
* Removed "Google Analytics by Yoast" integration
* Fixed "Google Analytics Dashboard for WP (GADWP)" integration
* Added "GA Google Analytics" integration
* Added "Google Analytics for WordPress by MonsterInsights" integration
* Added validation check for the UA code
* Added monitoring feature: Check weekly if the settings are data protection compliant. If not, a message appears in WP admin.
* Several code, security and usability optimazation

= 1.0 =
* 15. Februar 2016
* Initial Release