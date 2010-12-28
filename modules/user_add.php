<?php
if (!defined ('EXPENSES')) exit;

# @package
# @descr
#   Moduł dodawania użytkownika

class ExpUserAdd extends ExpAbstract {
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
    # @descr [array]
    #   Lista modulow do ktorych ma dostep uzytkownik

    protected $_user_permissions    = array ();

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

    # @function on_get
    # @descr
    #   Uzupełnienie domyślnych danych
    #
    # @input
    #   -
    #
    # @descr
    #   -

    public function on_get () {
        $this->_user_permissions = $this->merge_user_permissions ();
    }

	# @function on_post
	# @descr
	# 	Dodawanie użytkownika.
	# 	Wykonuje testy na poprawność danych.
	#
	# 	Jeśli nazwa jest puste, wstawia: [ - ]
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#
	# @related
	# 	delay_unparse
	# 	ExpAbstract::hash_password

    public function on_post () {
        $this->user_name            = (!$_POST['user_name'] ? '[ - ]' : $_POST['user_name']);
        $this->user_email           = $_POST['user_email'];
        $this->user_login           = $_POST['user_login'];
        $this->user_status          = $_POST['user_status'];
        $this->user_enabled         = $_POST['user_status'] == 'enabled';
        $this->user_password        = $_POST['user_password_new'];
        $this->user_role            = $_POST['user_role'];
        $this->user_is_admin        = $_POST['user_role'] == 'admin';
        $this->_user_permissions    = $this->merge_user_permissions ($_POST['user_permissions']);

        ## haslo - minimum 6 znakow
        if (strlen ($this->user_password) < 6) {
            ## TODO: resources
            $this->_errors[] = 'Za krotkie haslo.';
        }
        else if ($this->user_password != $_POST['user_password_repeat']) {
            ## TODO: resources
            $this->_errors[] = 'Wpisane hasła są różne.';
        }

        ## login - minimum 3 znaki
        if (strlen ($this->user_login) < 3) {
            ## TODO: resources
            $this->_errors[] = 'Musisz podać minimum 3 literowy login.';
        }

        ## musi byc email
        if (!$this->user_email) {
            ## TODO: resources
            $this->_errors[] = 'Musisz podać adres email.';
        }

        ## wlasciwy status
        if (!in_array ($this->user_status, array ('enabled', 'disabled'))) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwy status użytkownika';
        }

        ## wlasciwa rola
        if (!in_array ($this->user_role, array ('admin', 'user'))) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwa rola użytkownika';
        }

        ## uprawnienia
        $permissions = array ();
        $modules     = $this->get_modules_list ();
        foreach ($this->_user_permissions as $perms) {
            if (!in_array ($perms['name'], $modules)) {
                $this->_errors[] = 'Nieznany moduł uprawnień: '. $perms['name'];
                continue;
            }

            $permissions[$perms['name']] = (bool) $perms['value'];
        }

        if (!count ($this->_errors)) {
            $stmt = $GLOBALS['sql']->prepare ("
                INSERT INTO
                    `exp_users`
                SET
                    `user_password`     = ?,
                    `user_login`        = ?,
                    `user_name`         = ?,
                    `user_email`        = ?,
                    `user_status`       = ?,
                    `user_role`         = ?,
                    `user_permissions`  = ?,
                    `user_date_add`     = NOW()");

            $result = $stmt->execute (array (
                self::hash_password ($this->user_login, $this->user_password),
                $this->user_login,
                $this->user_name,
                $this->user_email,
                $this->user_status,
                $this->user_role,
                serialize ($permissions)
            ));

            if (!$result) {
                ## TODO: resources
                $this->_errors[] = 'Wystąpił błąd SQL: '.$stmt->errorInfo ();
            }
            else if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
                location (urldecode ($_REQUEST['b']));
            }
            else {
                location (null, array ('module' => 'users'));
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
            'mode_new'          => true,
            'is_admin'          => isset ($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0,
            'b'                 => (isset ($_REQUEST['b']) ? $_REQUEST['b'] : ''),
            'user_permissions'  => $this->_user_permissions,
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Dodawanie użytkownika',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

