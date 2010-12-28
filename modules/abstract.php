<?php
if (!defined ('EXPENSES')) exit;

# @package ExpAbstract
# @descr
#   Klasa abstrakcyjna dla wszystkich modułów projektu. Każdy z modułów musi dziedziczyć po tej klasie.
#
#   Dostarcza metodę do autoryzacji użytkownika, oraz metody upraszczające zabawy z PHPTal.
#
#   Należy utworzyć w każdej podklasie metodą execute (), która zapewni całkowitą kompilację szablonów.
#   Przykład takiej metody jest w @example.
#
# @example
#     public function execute () {
#         // właściwości i dane dla szablonu bieżącego modułu
#         $args       = array (
#             'sort'        => ($this->__sort == 'asc' ? 'desc' : 'asc'),
#             'categories'  => $this,
#         );
#
#         // właściwości i dane dla szablonu głównego (index.tpl) - te dwa klucze są obowiązkowe:
#         $root_args  = array (
#             // podtytuł w tagu <title />
#             'page_subtitle' => 'Lista kategorii',
#             // szablon do przypisania jako treść strony
#             'page_content'  => $this->_tpl,
#         );
#
#         // wywołanie tej metody zapewnia prawidłowe przypisanie danych do szablonu.
#         $this->__prepare ($args, $root_args);
#     }

abstract class ExpAbstract {
    # @struct $_logged_in
    # @descr [bool]
    #   informacja o tym czy dany user jest zalogowany

    protected $_logged_in;

    # @struct $_tpl
    # @descr [PHPTAL]
    #   Instancja szablonu bieżącego modułu

    protected $_tpl;

    # @struct $_tpl_root
    # @descr [PHPTAL]
    #   Instancja szablonu modułu głównego

    protected $_tpl_root;

    # @struct $_tpl_file
    # @descr [string]
    #   Nazwa pliku szablonu

    protected $_tpl_file;

    # @function execute
    # @descr
    #   Funkcja do nadpisania w klasach potomnych. Przykład jest w opisie klasy.
    # @input
    #   -
    # @output
    #   -

    abstract function execute ();

    # @function __construct
    # @descr
    #   Konstruktor.
    #
    #   Przypisuje do obiektu szablon główny, i tworzy szablon modułu.
    #
    # @related
    #   create_tpl

    public function __construct ($tpl_root = null) {
        $this->_tpl         = create_tpl ($this->_tpl_file);
        $this->_tpl_root    = $tpl_root;
    }

    # @function is_logged_in
    # @descr
    #   Zwraca informację czy dany user jest zalogowany.
    #
    # @input
    #   -
    # @output
    #   * 0: [bool]

    public function is_logged_in () {
        return $this->_logged_in;
    }

    # @function hash_password
    # @descr [static]
    #   Tworzy hash hasła na podstawie soli (ExpConfig::PASS_SALT) i podanego przez usera hasła.
    # @input
    #   * 0: login - (string) login użytkownika
    #   * 1: password - (string) hasło użytkownika
    #
    # @output
    #   * [string] hash hasła

    public static function hash_password ($login, $password) {
        return sha1 (ExpConfig::PASS_SALT .'!'. $login .'!'. $password);
    }

    # @function authenticate
    # @descr
    #   Autoryzuje usera lub sprawdza czy user jest już zalogowany.
    #
    #   Poza zwróceniem informacji, jeśli user jest dopiero autoryzowany na podstawie danych z $_GET lub $_POST,
    #   zapisuje dane w sesji.
    #   Ustawia także $ExpAbstract::_logged_in.
    #
    #   Jeśli user dopiero jest logowany, i istnieje $_GET['b'], po prawidłowej autoryzacji jest kierowany pod wskazany
    #   w $_GET['b'] adres.
    #
    # @input
    #   -
    # @output
    #   * 0: [bool]
    #
    # @related
    #   ExpAbstract::__authorize
    #   location

    public function authenticate () {
        $affect_b = false;
        $this->_logged_in = false;

        ## próba autoryzacji na podstawie danych w $_POST
        if (isset ($_POST['user_login']) && isset ($_POST['user_password'])) {
            $this->_logged_in = $this->__authorize ($_POST['user_login'], self::hash_password ($_POST['user_login'], $_POST['user_password']));
            $affect_b = true;
        }

        ## próba autoryzacji na podstawie danych w $_GET - tylko w przypadku połączenia szyfrowanego (https)
        else if (
            isset ($_ENV['HTTPS']) &&
            in_array (strtolower ($_ENV['HTTPS']), array ('1', 'on')) &&
            isset ($_GET['user_login']) &&
            isset ($_GET['user_password'])
        ) {
            $this->_logged_in = $this->__authorize ($_GET['user_login'], $_GET['user_password']);
            $affect_b = true;
        }

        ## sprawdza czy user jest już zalogowany/zautoryzowany
        else if (isset ($_SESSION['user_id'])) {
            $this->_logged_in = true;
        }

        if (!$this->_logged_in) {
            if (!($this instanceof ExpLogin)) {
                location (null, array (
                    'module'    => 'login',
                    'b'         => ($_ENV['SCRIPT_URL'] == $_ENV['REQUEST_URI'] ? urlencode ('/?module=default') : urlencode ($_ENV['REQUEST_URI']))
                ));
            }
        }
        else if (isset ($_REQUEST['b']) && $affect_b) {
            location (urldecode ($_REQUEST['b']));
        }
        else {
            return true;
        }
    }

    # @function __authorize
    # @descr
    #   Sprawdza autoryzację w bazie danych - szuka usera po danych podanych jako arumenty.
    #
    # @input
    #   * 0: login - (string) login użytkownika
    #   * 1: password - (string) hash hasła użytkownika utworzony używając funkcji ExpAbstract::hash_password ()
    #
    # @output
    #   * 0: [bool] informacja o tym czy podany user może zostać zautoryzowany

    protected function __authorize ($login, $password) {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `user_id`,
                `user_role`,
                `user_login`,
                `user_name`,
                `user_permissions`,
                IF(`user_role` = 'admin', 1, 0) AS `is_admin`
            FROM
                `exp_users`
            USE KEY(`login_password_status`)
            WHERE
                    `user_login`    = ?
                AND
                    `user_password` = ?
                AND
                    `user_status`   = 'enabled'
        ");
        $stmt->execute (array ($login, $password));

        $user_data = $stmt->fetch (PDO::FETCH_ASSOC);
        $stmt->closeCursor ();

        if ($user_data && is_array ($user_data)) {
            $_SESSION['user_id']    = $user_data['user_id'];
            $_SESSION['user_role']  = $user_data['user_role'];
            $_SESSION['is_admin']   = $user_data['is_admin'];
            $_SESSION['user_login'] = $user_data['user_login'];
            $_SESSION['user_name']  = $user_data['user_name'];

            ## cookie z loginem - aby się samo uzupełniało w formularzu
            $hostname               = parse_url (ExpConfig::HTTP_ROOT);
            @setcookie ('user_login', $login, time () + 3600 * 24 * 365, $hostname['path'], $hostname['host']);

            ## lista dostępnych modułów - domyślnie brak praw dostępu do każdego z nich
            $_SESSION['permissions']    = array ();
            foreach ($this->get_modules_list () as $item) {
                $_SESSION['permissions'][$item] = false;
            }

            $permissions = unserialize ($user_data['user_permissions']);
            if (is_array ($permissions)) {
                foreach ($permissions as $item => $value) {
                    if (isset ($_SESSION['permissions'][$item])) {
                        $_SESSION['permissions'][$item] = $value;
                    }
                }
            }

            return true;
        }
        else {
            $_SESSION = array ();
            return false;
        }
    }

    # @function __apply_tpl_args
    # @descr
    #   Przypisywanie danych do instancji szablonu
    #
    # @input
    #   * 0: tpl - (PHPTAL) instancja szablonu obiektu
    #   * 1: args - (array) tablica asocjacyjna, gdzie kluczem jest właściwość, a wartością przypisywana wartość.
    #           Jeśli podana wartość jest obiektem PHPTAL (instancją innego szablonu), wtedy jako wartość jest
    #           przypisywany wynik działania metody execute () podanej instancji szablonu
    #
    # @output
    #   -

    protected function __apply_tpl_args ($tpl, $args) {
        foreach ($args as $key => $value) {
            if (is_object ($value) && $value instanceof PHPTAL) {
                $tpl->$key = $value->execute ();
            }
            else {
                $tpl->$key = $value;
            }
        }
    }

    # @function __prepare
    # @descr
    #   Dostarczenie danych do szablonów - głównego, i modułu.
    #
    # @input
    #   * 0: args - (array) dane dla szablonu modułu
    #   * 1: root_args - (array) dane dla szablonu głównego
    #
    # @output
    #   -
    # @related
    #   ExpAbstract::__apply_tpl_args

    protected function __prepare ($args, $root_args = null) {
        $this->__apply_tpl_args ($this->_tpl, $args);

        if (!is_null ($this->_tpl_root) && !is_null ($root_args)) {
            $this->__apply_tpl_args ($this->_tpl_root, $root_args);
        }
    }

    # @function get_modules_list
    # @descr
    #   Zwraca listę wszystkich dostępnych modułów (zawartych w katalogu modules).
    #
    # @input
    #   -
    #
    # @output
    #   * (ARRAY) lista dostępnych modułów aplikacji

    protected function get_modules_list () {
        $modules_disabled = array ('Abstract', 'Iterator', 'Login', 'Default');

        ## lista dostępnych modułów - domyślnie brak praw dostępu do każdego z nich
        $modules    = glob (ExpConfig::FS_ROOT . DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'*');
        $ret        = array ();
        foreach ($modules as $item) {
            $item   = explode (DIRECTORY_SEPARATOR, $item);
            $item   = array_pop ($item);
            $item   = name_us_to_cc (substr ($item, 0, -4));

            if (in_array ($item, $modules_disabled)) {
                continue;
            }

            $ret[]  = $item;
        }

        return $ret;
    }

    # @function merge_user_permissions
    # @descr
    #   Zwraca listę modułów w postaci używanej przez szablony, wraz z nałożonymi informacjami o uprawnieniach
    #   znajdującymi się w przekazanej tablicy.
    #
    # @input
    #   * 0: perms - (ARRAY) [opt] tablica, gdzie kluczem jest nazwa modułu, a wartością infomracja czy user ma upranwineia do tegoż modułu
    #
    # @output
    #   * (ARRAY) tablica w postacji gotowej do użycia w szablonie. Każdy z elementów jest tablicą z kluczami:
    #       ** name - (STRING) nazwa modułu
    #       ** value - (BOOL) uprawnienia do tego modułu

    protected function merge_user_permissions ($perms = array ()) {
        $ret                = array ();
        foreach ($this->get_modules_list () as $v) {
            $ret[] = array (
                'name'  => $v,
                'value' => (
                    isset ($GLOBALS['modules_enabled'][$v])
                        ? 1
                        : isset ($perms[$v])
                            ? $perms[$v]
                            : false
                ),
            );
        }

        return $ret;
    }

}

