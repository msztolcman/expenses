<?php
if (!defined ('EXPENSES')) exit;

# @package ExpUsers
# @descr
#   Moduł wyświetlający listę użytkowników.

class ExpUsers extends ExpAbstract implements IteratorAggregate {
    # @struct $_order
    # @descr [string]
    #   Nazwa pola po którym sortujemy.

    protected $_order       = 'user_id';

    # @struct $_sort
    # @descr [string]
    #   Kierunek sortowania (rosnąco/malejąco)

    protected $_sort        = 'asc';

    # @struct $_statuses
    # @descr [array]
    #   Mapowanie kodow statusow na wyświetlane nazwy.

    protected $_statuses    = array (
        ## TODO: resources
        'enabled'   => 'włączony',
        'disabled'  => 'wyłączony',
    );

    # @struct $_tpl_file
    # @descr
    #   Nazwa pliku szablonu.

    protected $_tpl_file    = 'users.tpl';

    # @struct $_user_roles
    # @descr
    #   Mapowanie typów użytkowników na nazwy wyświetlane.

    protected $_user_roles  = array (
        ## TODO: resources
        'user'      => 'użytkownik',
        'admin'     => 'administrator',
    );

    # @function init
    # @descr
    #   Ustalenie sortowania.
    # @input
    #   -
    #
    # @output
    #   -
    #
    # @related
    #   is_sql_field_name

    public function init () {
        if (isset ($_GET['sort']) && $_GET['sort'] == 'desc') {
            $this->_sort   = 'desc';
        }
        if (isset ($_GET['order']) && is_sql_field_name ($_GET['order'], 'proj_users')) {
            $this->_order  = $_GET['order'];
        }
        else {
            $this->_order = 'user_id';
        }
    }

    # @function execute
    # @descr
	# 	Implementacja metody abstrakcyjnej klasy bazowej.
	#
	# @input
	#   -
	#
	# @output
	#   -
	#
	# @related
	#   ExpAbstract::__prepare

    public function execute () {
        $args       = array (
            'sort'      => ($this->_sort == 'asc' ? 'desc' : 'asc'),
            'users'     => $this,
            'b'         => (isset ($_ENV['REQUEST_URI']) ? urlencode ($_ENV['REQUEST_URI']) : ''),
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Lista użytkowników',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }

    # @function getIterator
    # @descr
    #   Implementacja interfejsu IteratorAggregate.
    #
    #   Przygotowuje zapytanie dla iteratora ExpIterator.
    #
    # @input
    #   -
    #
    # @output
    #   * 0: [ExpIterator] gotowy iterator
    #
    # @related
    #   ExpIterator

    public function getIterator () {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `user_id`,
                `user_name`,
                `user_email`,
                `user_login`,
                `user_status`,
                `user_role`,
                `user_date_add`,
                (SELECT COUNT(*) FROM `exp_items` WHERE `exp_items`.`user_id` = `exp_users`.`user_id`) AS `user_del_deny`
            FROM
                `exp_users`
            ORDER BY
                ". $this->_order ."
            " . strtoupper ($this->_sort));

        $hooks = array (
            'user_status'   => array ($this, 'hook_status'),
            'user_role'     => array ($this, 'hook_user_role'),
        );

        return new ExpIterator ($stmt, array (), $hooks);
    }

    # @function hook_status
    # @descr
    #   Modyfikuje zwracaną z DB wartość statusu użytkownika, rzutuje go poprzez tablicę $ExpUsers::_statuses
    #   na wartość wyświetlaną.
    #
    # @input
    #   * 0: data - oryginalna wartość z DB
    #
    # @output
    #   * 0: [string] zmapowana wartość
    #
    # @related
    #   $ExpUsers::_statuses

    public function hook_status ($data) {
        return $this->_statuses[$data];
    }

    # @function hook_user_role
    # @descr
    #   Modyfikuje zwracaną z DB wartość roli użytkownika, rzutuje go poprzez tablicę $ExpUsers::_user_roles
    #   na wartość wyświetlaną.
    #
    # @input
    #   * 0: data - oryginalna wartość z DB
    #
    # @output
    #   * 0: [string] zmapowana wartość
    #
    # @related
    #   $ExpUsers::_user_roles

    public function hook_user_role ($data) {
        return $this->_user_roles[$data];
    }
}

