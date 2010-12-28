<?php
## marker dla inkludowanych plikow
define ('EXPENSES', 1);

## konfiguracja ogolna
ini_set ('include_path',
    ini_get ('include_path')
        . PATH_SEPARATOR
        . dirname (__FILE__)
        . DIRECTORY_SEPARATOR
        . 'lib'
);
if (!ini_get ('session.auto_start')) {
    session_start ();
}
## uzupełniamy brakujące informacje
if (!isset ($_ENV['REQUEST_URI'])) {
    $_ENV['REQUEST_URI'] = $_ENV['SCRIPT_NAME'];
    if ($_ENV['QUERY_STRING']) {
        $_ENV['REQUEST_URI'] .= '?'. $_ENV['QUERY_STRING'];
    }

    if ($_ENV['REQUEST_URI'][0] != '/') {
        $_ENV['REQUEST_URI'] = '/'. $_ENV['REQUEST_URI'];
    }
}

## inkludujemy wymagane zawsze biblioteki
require 'config.php';
require 'functions.php';
require 'autoloader.php';
require 'PHPTAL.php';
require 'phptal_additions.php';

## ustawienia typowe dla debug mode
if (ExpConfig::DEBUG) {
    ini_set ('display_errors', true);
    error_reporting (E_ALL | E_STRICT);
}

## rejestrujemy poszczególne funkcje autoloaderów
spl_autoload_register ('ExpAutoload::modules');
spl_autoload_register ('ExpAutoload::exceptions');
spl_autoload_register ('ExpAutoload::notfound');

# @struct
# @descr
#   lista modułów do których zawsze mamy dostęp

$modules_enabled    = array ('Default', 'Login');


## utworzenie instancji szablonu głównego
$tpl = create_tpl ('index.tpl');
## TODO: resources
$tpl->page_title = 'Expenses';

## konfiguracja bazy danych
$sql = new PDO (sprintf ('mysql:host=%s;dbname=%s', ExpConfig::DB_HOST, ExpConfig::DB_USER), ExpConfig::DB_NAME, ExpConfig::DB_PASS);
$sql->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql->setAttribute (PDO::ATTR_CASE, PDO::CASE_LOWER);

$sql->query ('SET NAMES "utf8"');

## wylogowanie
if (isset ($_REQUEST['logout'])) {
    $_SESSION = array ();
    location ();
}

## szukamy modułu do wywołania
if (!isset ($_REQUEST['module']) || !$_REQUEST['module']) {
    $_REQUEST['module'] = 'default';
}
else {
    $_REQUEST['module'] = preg_replace ('/[^a-zA-Z0-9_]+/', '', $_REQUEST['module']);
}

try {
    ## ustalamy nazwę klasy
    $module_name    = name_us_to_cc ($_REQUEST['module']);
    $class_name     = 'Exp'. $module_name;

    ## tworzymy obiekt
    $module             = new $class_name ($tpl);

    if (! ($module instanceof ExpAbstract)) {
        ## TODO: resources
        throw new ExpExceptionModuleNotFound (sprintf ('Nie znaleziono modułu %s (%s).', $class_name, $_REQUEST['module']));
    }

    ## logowanie
    $module->authenticate ();

    ## sprawdzamy czy mamy uprawnienia do modułu
    if (
        !in_array ($module_name, $modules_enabled) &&
        count ($_SESSION) &&
        !$_SESSION['is_admin'] &&
        !$_SESSION['permissions'][$module_name]
    ) {
        throw new ExpExceptionNoPermission (sprintf ('Brak uprawnień do modułu %s.', $module_name));
    }

    ## dodatkowe testy
    $execute = true;
    if (method_exists ($module, 'validate')) {
        $execute = $module->validate ();
    }

    ## dodatkowa inicjalizacja
    if (method_exists ($module, 'init')) {
        $module->init ();
    }

    ## akcja pochodzaca z metody
    if (method_exists ($module, ($method_action = 'on_'.strtolower ($_ENV['REQUEST_METHOD'])))) {
        $module->$method_action ();
    }

    ## wykonujemy moduł
    $module->execute ();

    ## przypisujemy do głównego szablonu informację czy jesteśmy zalogowani
    $tpl->js_file       = strtolower ($_REQUEST['module']);
    $tpl->logged_in     = $module->is_logged_in ();
    $tpl->permissions   = $_SESSION['permissions'];
    $tpl->HTTP_ROOT     = ExpConfig::HTTP_ROOT;

    ## wyrzucamy na ekran
    echo $tpl->execute ();
}

catch (ExpExceptionModuleNotFound $e) {
    echo $e->getMessage ();
}
catch (ExpExceptionNoPermission $e) {
    echo $e->getMessage ();
}

catch (Exception $e) {
    if (ExpConfig::DEBUG) {
        cgi_dump ($e);
    }
    else {
        ## TODO: resources
        echo 'Wystapil blad. Czekaj na reakcje deweloperow...';
        @mail (ExpConfig::ADMIN_EMAIL, 'Exp error!', print_r ($e, 1));
    }
}

exit ();

