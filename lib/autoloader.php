<?php
if (!defined ('EXPENSES')) exit;

# @package ExpAutoload
# @descr
#   Klasa z metodami statycznymi autoloadera.
abstract class ExpAutoload {

    # @function exceptions
    # @descr
    #   Autoloader dla wyjątków.
    #
    # @input
    #   * 0: class_name - (string) nazwa klasy do załadowania
    #
    # @output
    #   -
    public static function exceptions ($class_name) {
        if (preg_match ('/^ExpException/', $class_name)) {
            include ExpConfig::FS_ROOT.'lib/exceptions.php';
        }
    }

    # @function modules
    # @descr
    #   Autoloader dla modułów aplikacji.
    #
    # @input
    #   * 0: class_name - (string) nazwa klasy do załadowania
    #
    # @output
    #   -
    public static function modules ($class_name) {
        ## zamieniamy camel-case nazw klas na notacje uzywana w plikach, czyli podkreslniki
        $module_file = preg_replace ('/(?!^)([A-Z])/', '_$1', $class_name);
        $module_file = strtolower ($module_file);

        $path = ExpConfig::FS_ROOT . 'modules/' . substr ($module_file, 4) . '.php';
        if (is_file ($path)) {
            include ($path);
        }
    }

    # @function notfound
    # @descr
    #   Tworzy pustą klasę o podanej nazwie.
    #
    #   Pozwala zapobiec bezpardonowemu wywaleniu się PHP w przypadku użycia klasy która
    #   nie została wcześniej zdefiniowana.
    #   Poniżej po załadowaniu modułu sprawdzamy czy załadowany moduł dziedziczy po ExpAbstract,
    #   jak nie to rzucamy wyjątkiem ExpExceptionModuleNotFound.
    #
    # @input
    #   * 0: class_name - (string) nazwa klasy do załadowania
    #
    # @output
    #   -
    public static function notfound ($class_name) {
        if (substr ($class_name, 0, 7) != 'PHPTAL_') {
            eval ('class '.$class_name.' {}');
        }
    }

}

