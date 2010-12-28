<?php
if (!defined ('EXPENSES')) exit;

# @function phptal_tales_css_odd
# @descr
#   Modyfikator do użytku w szablonie PHPTAL.
#
#   W zależności od podanej wartości (true/false) zwraca klasę css row_odd (w przypadku gdy
#   repeat/ZMIENNA/odd zwróci true), lub row_even.
# @input
#   * 0: src - sciezka do konkretnego elementu
#   * 1: nothrow - informacja przekazywana do PHPTAL::phptal_tales ()
#
# @output
#   * [string] kod php w postaci napisu do użytku w PHPTAL
#
# @related
#   phptal_tales

function phptal_tales_css_odd ($src, $nothrow) {
    $src = trim ($src);
    return '('.phptal_tales ($src, $nothrow) .'? "row_odd" : "row_even")';
}

# @function phptal_tales_date_format
# @descr
#   Modyfikator do użytku w szablonie PHPTAL.
#
#   Formatuje datę do użytku w szabonach. Chwilowo tylko sprawdza czy data ma wartość, jeśli
#   nie to zwraca myślniki, jeśli tak to jej nie modyfikuje.
# @input
#   * 0: src - sciezka do konkretnego elementu
#   * 1: nothrow - informacja przekazywana do PHPTAL::phptal_tales ()
#
# @output
#   * [string] kod php w postaci napisu do użytku w PHPTAL
#
# @related
#   phptal_tales

function phptal_tales_date_format ($src, $nothrow) {
    $src    = trim ($src);
    $path   = phptal_tales ($src, $nothrow);
    return '('. $path . ' && '. $path .' != "0000-00-00" ? '. $path .' : "-----")';
}

function _parse_uri_data ($str) {
    $str = trim ($str);

    $rxp = '/
        ^
        \[([^\]]+)\]
        $
    /x';
    if (!preg_match ($rxp, $str, $match)) {
        throw new Exception ('Bledny string');
    }

    $ret = array ();
    foreach (explode (',', $match[1]) as $data) {
        list ($k, $v) = explode ('=', $data, 2);
        $ret[$k] = $v;
    }

    return $ret;
}

function phptal_tales_uri_data ($src, $nothrow) {
    $sglobals_names = explode (';', $src);
    $src            = array_pop ($sglobals_names);
    $parsed         = _parse_uri_data ($src);
    $sglobals       = array ();
    $ret            = array ();
    $skip           = array ();

    ## zbieramy dane o kluczach jakie chcemy pominac
    foreach ($parsed as $k => $v) {
        if ($v[0] == '!') {
            $skip[$k] = 1;
        }
    }

    ## tworzymy dane z superglobala jakiego nam kazano
    static $sglobals_av = array ('POST' => 1, 'GET' => 1, 'COOKIE' => 1, 'SESSION' => 1);
    foreach ($sglobals_names as $k) {
        $k = strtoupper ($k);
        if (isset ($sglobals_av[$k])) {
            $sglobals = array_merge ($sglobals, $GLOBALS['_'.$k]);
        }
    }

    ## bezpieczone polaczenie superglobali w wartosc jaka mozna wrzucic do linka
    foreach ($sglobals as $k => $v) {
        if (isset ($skip[$k])) {
            continue;
        }

        if (is_array ($v)) {
            $k = $k .'[]';
            foreach ($v as $v1) {
                $ret[] = $k .'='. $v1;
            }
            continue;
        }

        $ret[] = $k .'='. $v;
    }

    foreach ($parsed as $k => $v) {
        if ($v[0] == '$') {
            $v = phptal_tales (substr ($v, 1), $nothrow);
        }
        else if ($v[0] == '!') {
            continue;
        }

        $ret[] = $k .'='. $v;
    }

    return '"'. join ('&', $ret) .'"';
}

