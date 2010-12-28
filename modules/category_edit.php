<?php
if (!defined ('EXPENSES')) exit;

# @package ExpCategoryEdit
# @descr
#   Moduł edycji kategorii

class ExpCategoryEdit extends ExpAbstract {
    # @struct $cat_name
    # @descr [string]
    #   Nazwa kategorii

    public $cat_name        = '';

    # @struct $cat_status
    # @descr [string]
    #   Status kategorii

    public $cat_status      = 'enabled';

    # @struct $cat_enabled
    # @descr [bool]
    #   Czy kategoria jest aktywna

    public $cat_enabled     = true;

    # @struct $_errors
    # @descr [array]
    #   Lista komunikatów błędów

    protected $_errors       = array ();

    # @struct $_tpl_file
    # @descr [string]
    #   Nazwa pliku szablonu

    protected $_tpl_file    = 'category_add.tpl';

    # @function validate
    # @descr
    #   Sprawdzenie czy mamy dostępne w $_GET ID kategorii
    #
    # @input
    #   -
    #
    # @output
    #   -

    public function validate () {
        if (!isset ($_GET['cat_id'])) {
            location ();
        }

        $_GET['cat_id'] = (int) $_GET['cat_id'];
    }

    # @function on_get
    # @descr
    #   Odczytanie danych edytowanej kategorii
    #
    # @input
    #   -
    #
    # @output
    #   -

    public function on_get () {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `cat_name`,
                `cat_status`,
                IF(`cat_status` = 'enabled', 1, 0) AS `cat_enabled`
            FROM
                `exp_categories`
            WHERE
                `cat_id` = ?");

        $stmt->execute (array ($_GET['cat_id']));

        list (
            $this->cat_name,
            $this->cat_status,
            $this->cat_enabled
        ) = $stmt->fetch (PDO::FETCH_NUM);

        $stmt->closeCursor ();
    }

    # @function on_post
    # @descr
    #   Zapisanie zmian do kategorii
    # @input
    #   -
    #
    # @output
    #   -

    public function on_post () {
        $this->cat_name         = trim ($_POST['cat_name']);
        $this->cat_status       = $_POST['cat_status'];
        $this->cat_enabled      = $_POST['cat_status'] == 'enabled';

        ## musimy miec nazwe kategorii
        if (!$this->cat_name) {
            $this->_errors[] = 'Musisz podać nazwę kategorii.';
        }

        ## odpowiedni status
        if (!in_array ($this->cat_status, array ('enabled', 'disabled'))) {
            $this->_errors[] = 'Niewłaściwy status kategorii.';
        }

        if (!count ($this->_errors)) {
            $stmt = $GLOBALS['sql']->prepare ("
                UPDATE
                    `exp_categories`
                SET
                    `cat_name`         = ?,
                    `cat_status`       = ?
                WHERE
                    `cat_id`           = ?
            ");

            $result = $stmt->execute (array (
                $this->cat_name,
                $this->cat_status,
                $_GET['cat_id']
            ));

            if (!$result) {
                ## TODO: resources
                $this->_errors[] = 'Wystąpił błąd SQL: '.$stmt->errorInfo ();
            }
            else if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
                location (urldecode ($_REQUEST['b']));
            }
            else {
                location (null, array ('module' => 'categories'));
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
            'errors'    => $this->_errors,
            'category'  => $this,
            'b'         => (isset ($_REQUEST['b']) ? $_REQUEST['b'] : ''),
        );

        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Edycja kategorii',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

