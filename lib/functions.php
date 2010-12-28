<?php
if (!defined ('EXPENSES')) exit;

# @function location
# @descr
#   Wysyła nagłowek Location.
#   Funkcja buduje z podanych parametrów query string. Pierwszym parametrem jest zawsze ścieżka.
#   Jeśli nie jest podana, lub ma wartość boolean false (np. null) to domyślnie ścieżką będzie
#   ExpConfig::HTTP_ROOT.
#
#   Klucze i wartości drugiego parametru są zawsze urlencodowane.
# @input
#   * 0: url - (string|false) [opt] ścieżka
#   * 1: qs - (array|false) [opt] tablica asocjacyjna, z których budowane są parametry query stringa
#   * 2: allow_internal - (bool) [opt] jeśli true, to pozwoli na zbudowanie nagłówka zaczynającego się od '/' (bez tego zawsze na początek wstawi ExpConfig::HTTP_ROOT)
# @output
#   -
# @example
# location (null, array ('var1' => 'a', 'var2' => 'b')) # -> [ExpConfig::HTTP_ROOT]?var1=a&var2=b
# location ('http://onet.pl/path1/path2/', array ('var1' => 'a', 'var2' => 'b')) # -> http://onet.pl/path1/path2?var1=a&var2=b
# @related
#   ExpConfig::HTTP_ROOT
#   header

function location ($url = null, $qs = null, $allow_internal=false) {
    if (!$url || (!$allow_internal && substr ($url, 0, 1) == '/')) {
        $url = ExpConfig::HTTP_ROOT . (!$url ? '' : $url);
    }

    if (is_array ($qs) && count ($qs)) {
        $qs_data = array ();
        foreach ($qs as $k => $v) {
            $qs_data[] = urlencode ($k) .'='. urlencode ($v);
        }

        if (count ($qs_data)) {
            $url .= (strpos ($url, '?') === false ? '?' : '&');
            $url .= join ('&', $qs_data);
        }
    }

    header ('Location: ' . $url);
    exit;
}

# @function create_tpl
# @descr
#   Tworzy instancję szablonu PHPTAL z przystosowanymi dla nas ustawieniami i zwraca ją.
#
#   Szablon jest przystosowany do wypuszczania dokumentu XHTML w kodowaniu UTF-8. Zakłada że
#   szablony są w katalogu ExpConfig::FS_ROOT/tpl, a wersja skompilowana zapisywana będzie w
#   ExpConfig::FS_ROOT/tpl_cache.
#
#   Jeśli ExpConfig::DEBUG jest ustawiona na true, to cache nie będzie używany.
#
# @input
#   * 0: tpl_file - (string) nazwa pliku szablonu
#   * 1: options - (array) [opt] opcje jakie powinny być rzekazane do nowotworzonego szablonu. Rozpoznawane klucze:
#       ** encoding
#       ** output_mode
#
# @output
#   * [PHPTAL] instancja PHPTAL
#
# @related
#   PHPTAL

function create_tpl ($tpl_file, $options=array ()) {
    $tpl = new PHPTAL ($tpl_file);
    if (!isset ($options['encoding'])) {
        $options['encoding'] = 'UTF-8';
    }
    if (!isset ($options['output_mode'])) {
        $options['output_mode'] = PHPTAL::XHTML;
    }

    $tpl
        ->setEncoding ($options['encoding'])
        ->setOutputMode ($options['output_mode'])
        ->setTemplateRepository (ExpConfig::FS_ROOT . 'tpl')
        ->setPhpCodeDestination (ExpConfig::FS_ROOT . 'tpl_cache')
        ->setForceReparse (ExpConfig::DEBUG);

    return $tpl;
}

# @function is_sql_field_name
# @descr
#   Sprawdza czy podana ciąg znaków jest prawidłową nazwą kolumny (składa się ze znaków a-z0-9_ ew. otoczonych
#   backtickami, lub w wersji z nazwą tabeli oddzielone kropką).
#
#   Jeśli trzeba, sprawdza w DB czy taka kolumna istnieje. Można również podać listę dostępnych kolumn jako tablicę
#   (przydatne jeśli w zapytaniu używamy jakichś kolumn wirtualnych).
#
#   Jeśli drugi parametr (table) będzie miał wartość true, to podana nazwa zostanie sprawdzona w podanej w parametrze
#   virt_cols tablicy. Jeśli będzie to napis, potraktujemy to jako nazwę tabeli, w której także sprawdzamy czy taka
#   kolumna istnieje (pod warunkiem, że jest podana w virt_cols, która ma wyższy priorytet).
# @input
#   * 0: fields - (string) nazwa kolumny
#   * 1: table - (string|bool) [opt] nazwa tabeli w której sprawdzamy czy kolumna istnieje, lub wartość true mówiąca żeby sprawdzić nazwy w tablicy podanej jako następny parametr.
#   * 2: virt_cols - (array) [opt] dozwolone nazwy kolumn
#
# @output
#   0: [bool]

function is_sql_field_name ($fields, $table = null, $virt_cols = array ()) {
    $fields = explode ('.', $fields);
    if (count ($fields) > 2) {
        return false;
    }

    if ($table && count ($fields) == 2 && trim ($fields[0], '`') != $table) {
        return false;
    }

    foreach ($fields as $field) {
        if (
            ($field[0] == '`' && substr ($field, -1) != '`') ||
            ($field[0] != '`' && substr ($field, -1) == '`')
        ) {
            return false;
        }

        if ($field[0] == '`') {
            $field = substr ($field, 1, -1);
        }

        if (!preg_match ('/^[a-z0-9_]+$/', strtolower ($field))) {
            return false;
        }
    }

    if ($table) {
        $fields = isset ($fields[1]) ? $fields[1] : $fields[0];
        if (in_array ($fields, $virt_cols)) {
            return true;
        }

        else if (!is_bool ($table)) {
            ## po uprzednich testach wiemy ze w nazwie kolumny nie ma znaczkow inne niz [ascii] \w, wiec mozemy
            ## spokojnie doklejac do zapytania
            $stmt = $GLOBALS['sql']->query ('SHOW COLUMNS FROM `'. $table .'` LIKE "'. $fields .'"');
            if (!$stmt->fetch (PDO::FETCH_ASSOC)) {
                return false;
            }
        }
    }

    return true;
}

# @function cgi_dump
# @descr
#   Dumpuje zawartość podanych zmiennych.
# @input
#   * 0..n: wartości do zdumpowania.
# @output
#   -
# @related
#   print_r

function cgi_dump () {
    $args = func_get_args ();
    echo '<pre>';
    foreach ($args as $arg) {
        print_r ($arg);
        echo "\n";
    }
    echo '</pre>';
}

# @function prepare_text_for_save
# @descr
#   Przygotowanie treści notatek lub opisów do zapisania w bazie, mając na względzie formatowanie wiki jakie
#   dopuszczamy. Generalnie zamienia wszystkie niebezpieczne znaki na encje
#
# @input
#   * 0: txt - (string) ciąg znaków przeznaczony do dodania do bazy
#
# @output
#   * [string] przeparsowany i zabezpieczony ciąg znaków

function prepare_text_for_save ($txt) {
    return htmlspecialchars ($txt, ENT_COMPAT, 'UTF-8');
}

# @function date_prepare
# @descr
#   Przeksztalca date pobrana od uzytkownika (ktory moze ja podac w wielu formatach - w sensie uzyc roznych
#   separatorow) na jeden format uznawany przez nas (czyli docelowo jest to zawsze: 'dd-mm-yyyy')
#
# @input
#   * 0: txt - (string) data przyjeta od uzytkownika
#
# @output
#   * [string] data w formacie 'dd-mm-yyyy'

function date_prepare ($date) {
    return str_replace (array (' ', '/', ',', '.'), '-', trim ($date));
}

# @function float_prepare
# @descr
#   Pozwala uzytkownikowi na podanie liczby z separatorem ',' jako oddzielajacym czesc dziesietna od calkowitej
#
# @input
#   * 0: txt - (float) wrtosc pobrana od uzytkownika
#
# @output
#   * [float] poprawna wartosc float

function float_prepare ($float) {
    return (float) str_replace (',', '.', $float);
}

# @function in_select_options
# @descr
#
# @input
#   * 0: search - (MISC) szukana wartość
#   * 1: key - (MISC) klucz pod jakim szukamy w tablicy $data
#   * 2: data - (ARRAY) przeszukiwana tablica
#
# @output
#   * (BOOL) informacja, czy znaleziono szukaną wartość

function in_select_options ($search, $key, $data) {
    foreach ($data as $v) {
        if ($search == $v[$key]) {
            return true;
        }
    }

    return false;
}

# @function date_reverse
# @descr
#
# @input
#   * 0: date - (STRING) data w formacie rozdzielanym myślnikami
#
# @output
#   * (STRING) zmodyfikowana data

function date_reverse ($date) {
    return join ('-', array_reverse (explode ('-', $date)));
}

# @function name_us_to_cc
# @descr
#   Konwersja nazwy zapisanej w postaci z podkreślnikami do CamelCase
#
# @input
#   * 0: str - (STRING) napis z podkreśleniami
#
# @output
#   * (STRING) napis po konwersji

function name_us_to_cc ($str) {
    $str    = str_replace ('_', ' ', $str);
    $str    = ucwords ($str);
    return str_replace (' ', '', $str);
}

# @function search_convert
# @descr
#   Podany napis konwertuje do postaci wyszukiwania regexpem, z użyciem globów.
#
#   Zabezpiecza metaznaki wyrażeń regularnych, a gwiazdki zamienia na .*.
#
# @input
#   * 0: str - (STRING) napis
#
# @output
#   * 0: (STRING) skonwertowany napis

function search_convert ($str) {
    $str = preg_quote ($str);
    $str = str_replace ('\*', '.*', $str);
    return $str;
}

