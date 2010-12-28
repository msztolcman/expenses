<?php
if (!defined ('EXPENSES')) exit;

# @package ExpCategories
# @descr
#   Moduł wyświetlający listę kategorii

class ExpCategories extends ExpAbstract implements IteratorAggregate {
    # @struct $_order
    # @descr [string]
    #   Nazwa pola po którym sortujemy.

    protected $_order        = 'cat_id';

    # @struct $_sort
    # @descr [string]
    #   Kierunek sortowania (rosnąco/malejąco)

    protected $_sort         = 'asc';

    # @struct $_statuses
    # @descr [array]
    #   Mapowanie kodow statusow na wyświetlane nazwy.
    # @todo
    #   resurces

    protected $_statuses     = array (
        'enabled'   => 'włączony',
        'disabled'  => 'wyłączony',
    );

    # @struct $_tpl_file
    # @descr
    #   Nazwa pliku szablonu.

    protected $_tpl_file    = 'categories.tpl';

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
        if (isset ($_GET['order']) && is_sql_field_name ($_GET['order'], 'exp_categories')) {
            $this->_order  = $_GET['order'];
        }
        else {
            $this->_order = 'cat_id';
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
            'sort'          => ($this->_sort == 'asc' ? 'desc' : 'asc'),
            'categories'    => $this,
            'b'             => urlencode ($_ENV['REQUEST_URI']),
            'permissions'   => $_SESSION['permissions'],
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Lista kategorii',
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
                `cat_id`,
                `cat_name`,
                `cat_status`,
                IF(`cat_status` = 'enabled', 1, 0) AS `cat_enabled`,
                (SELECT COUNT(*) FROM `exp_items` WHERE `exp_items`.`cat_id` = `exp_categories`.`cat_id`) AS `cat_del_deny`
            FROM
                `exp_categories`
            ORDER BY
                " . $this->_order . "
            " . strtoupper ($this->_sort) );

        $hooks = array ('cat_status' => array ($this, 'hook_status'));

        return new ExpIterator ($stmt, array (), $hooks);
    }

    # @function hook_status
    # @descr
    #   Modyfikuje zwracaną z DB wartość statusu projektu, rzutuje go poprzez tablicę $ExpProjects::_statuses
    #   na wartość wyświetlaną.
    #
    # @input
    #   * 0: data - oryginalna wartość z DB
    #
    # @output
    #   * 0: [string] zmapowana wartość
    #
    # @related
    #   $ExpProjects::_statuses

    public function hook_status ($data) {
        return $this->_statuses[$data];
    }
}

