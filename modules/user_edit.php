<?php
if (!defined ('EXPENSES')) exit;

# @package
# @descr
#   Moduł dodawania użytkownika.

class ExpUserEdit extends ExpAbstract {
    # @struct $user_name
    # @descr [string]
    #   Ksywa.

    public $user_name               = '';

    # @struct $user_email
    # @descr [string]
    #   Adres email.

    public $user_email              = '';

    # @struct $user_login
    # @descr [string]
    #   Login.

    public $user_login              = '';

    # @struct $user_status
    # @descr [string]
    #   Status.

    public $user_status             = 'disabled';

    # @struct $user_enabled
    # @descr [bool]
    #   Czy user jest aktywny,

    public $user_enabled            = false;

    # @struct $user_role
    # @descr [string]
    #   Rola użytkownika.

    public $user_role               = 'user';

    # @struct $user_is_admin
    # @descr [bool]
    #   Czy ma uprawnienia administratora.

    public $user_is_admin           = false;

    # @struct $_user_permissions
    # @descr [bool]
    #   Lista modulow do ktorych ma dostep uzytkownik

    protected $_user_permissions    = false;

    # @struct $_tpl_file
    # @descr [string]
    #   Nazwa pliku szablonu.

    protected $_tpl_file            = 'user_add.tpl';

    # @struct $_errors
    # @descr [array]
    #   Lista komunikatów błędów

    protected $_errors              = array ();

    # @struct $user_password
    # @descr [string]
    #   Hasło.

    private $user_password          = null;

    # @function init
    # @descr
    #   Test czy w jesteśmy adminem. Jeśli tak, to pozwalamy na edycję danych usera z $_GET['user_id'] (jeśli podane),
    #   w innym wypadku edytujemy własne dane.
    #
    #   Gdy nie ma $_GET['user_id'] lub nie jestesmy adminem, do $_GET['user_id'] jest podstawiana
    #   wartość z $_SESSION['user_id'].
    # @input
    #   -
    #
    # @output
    #   -

    public function init () {
        if (!isset ($_GET['user_id']) || !$_SESSION['is_admin']) {
            $_GET['user_id'] = $_SESSION['user_id'];
        }
    }

    # @function on_get
    # @descr
    #   Pobieramy dane usera którego edytujemy
    #
    # @input
    #   -
    #
    # @descr
    #   -

    public function on_get () {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `user_name`,
                `user_email`,
                `user_login`,
                `user_role`,
                IF(`user_role` = 'admin', 1, 0) AS `user_is_admin`,
                `user_permissions`,
                `user_status`,
                IF(`user_status` = 'enabled', 1, 0) AS `user_enabled`
            FROM
                `exp_users`
            WHERE
                `user_id` = ?");
        $stmt->execute (array ($_GET['user_id']));

        list (
            $this->user_name,
            $this->user_email,
            $this->user_login,
            $this->user_role,
            $this->user_is_admin,
            $this->_user_permissions,
            $this->user_status,
            $this->user_enabled
        ) = $stmt->fetch (PDO::FETCH_NUM);
        $stmt->closeCursor ();

        $this->_user_permissions = unserialize ($this->_user_permissions);
        if (!is_array ($this->_user_permissions)) {
            $this->_user_permissions = array ();
        }
        $this->_user_permissions = $this->merge_user_permissions ($this->_user_permissions);
    }

	# @function on_post
	# @descr
	# 	Zapisanie danych użytkownika.
	# 	Wykonuje testy na poprawność danych.
	#
	# 	Jeśli nazwa jest pusta, wstawia: [ - ]
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#
	# @related
	# 	ExpAbstract::hash_password

    public function on_post () {
        $this->user_name            = (!$_POST['user_name'] ? '[ - ]' : $_POST['user_name']);
        $this->user_email           = $_POST['user_email'];
        $this->user_status          = $_POST['user_status'];
        $this->user_enabled         = $_POST['user_status'] == 'enabled';
        $this->user_login           = $_POST['user_login'];
        if ($_SESSION['is_admin']) {
            $this->_user_permissions    = $this->merge_user_permissions ($_POST['user_permissions']);
        }

        ## jeśli wpisane coś w hasło, tzn że chce je zmienić - jeśli puste nie sprawdzamy
        if ($_POST['user_password_new']) {
            ## minimum 6 znaków
            if (strlen ($_POST['user_password_new']) < 6) {
                ## TODO: resources
                $this->_errors[] = 'Za krotkie haslo.';
            }
            else if ($_POST['user_password_new'] != $_POST['user_password_repeat']) {
                ## TODO: resources
                $this->_errors[] = 'Wpisane hasła są różne.';
            }
        }

        ## musi być email
        if (!$this->user_email) {
            ## TODO: resources
            $this->_errors[] = 'Musisz podać adres email.';
        }

        ## właściwy status
        if (!in_array ($this->user_status, array ('enabled', 'disabled'))) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwy status użytkownika';
        }

        ## uprawnienia
        if ($_SESSION['is_admin']) {
            $permissions = array ();
            $modules     = $this->get_modules_list ();
            foreach ($this->_user_permissions as $perms) {
                if (!in_array ($perms['name'], $modules)) {
                    $this->_errors[] = 'Nieznany moduł uprawnień: '. $perms['name'];
                    continue;
                }

                $permissions[$perms['name']] = (bool) $perms['value'];
            }
        }


        if (!count ($this->_errors)) {
            $stmt = $GLOBALS['sql']->prepare ("
                UPDATE
                    `exp_users`
                SET
                    ". ($_POST['user_password_new'] ?  "`user_password` = ?,\n" : '') ."
                    `user_name`    = ?,
                    `user_email`    = ?,
                    ". ($_SESSION['is_admin'] ? "`user_permissions`  = ?," : '') ."
                    `user_status`   = ?
                WHERE
                    `user_id` = ?");

            $params = array (
                $this->user_name,
                $this->user_email,
            );

            if ($_SESSION['is_admin']) {
                $params[] = serialize ($permissions);
            }

            $params[] = $this->user_status;
            $params[] = $_GET['user_id'];

            if ($_POST['user_password_new']) {
                array_unshift ($params, self::hash_password ($_POST['user_login'], $_POST['user_password_new']));
            }
            $result = $stmt->execute ($params);

            if (!$result) {
                ## TODO: resources
                $this->_errors[] = 'Wystąpił błąd SQL: '.$stmt->errorInfo ();
            }
            else if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
                location (urldecode ($_REQUEST['b']));
            }
            else if ($_SESSION['is_admin']) {
                location (null, array ('module' => 'users'));
            }
            else {
                location ();
            }
        }
    }

	# @function execute
	# @descr
	# 	Implementacja metody abstrakcyjnej klasy bazowej.
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#
	# @related
	# 	ExpAbstract::__prepare

    public function execute () {
        $args       = array (
            'errors'            => $this->_errors,
            'user_data'         => $this,
            'mode_new'          => false,
            'is_admin'          => $_SESSION['is_admin'],
            'https_root'        => (substr (ExpConfig::HTTP_ROOT, 0, 5) == 'https' ? ExpConfig::HTTP_ROOT : 'https'.substr (ExpConfig::HTTP_ROOT, 4)),
            'b'                 => (isset ($_REQUEST['b']) ? $_REQUEST['b'] : ''),
            'user_permissions'  => $this->_user_permissions,
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Edycja profilu',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

