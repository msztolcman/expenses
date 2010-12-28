<?php
if (!defined ('EXPENSES')) exit;

# @package ExpCategoryAdd
# @descr
#   Moduł dodawania kategorii

class ExpCategoryAdd extends ExpAbstract {
    # @struct
    # @descr [bool]
    #   Czy kategoria jest aktywna.

    public $cat_enabled     = true;

    # @struct
    # @descr [string]
    #   Nazwa dodawanej kategorii.

    public $cat_name        = '';

    # @struct
    # @descr [string]
    #   Status kategorii.

    public $cat_status      = 'enabled';

    # @struct
    # @descr [array]
    #   Lista komunikatów błędów.

    protected $_errors      = array ();

    # @struct
    # @descr [string]
    #   Nazwa pliku szablonu.

    protected $_tpl_file    = 'category_add.tpl';

    # @function on_post
    # @descr
    #   Dodawanie kategorii do DB.
	# 	Wykonuje testy na poprawność danych.
	#
	# @input
	#   -
	#
	# @output
	#   -

    public function on_post () {
        $this->cat_name         = trim ($_POST['cat_name']);
        $this->cat_status       = $_POST['cat_status'];
        $this->cat_enabled      = $_POST['cat_status'] == 'enabled';

        ## musi być podana nazwa
        if (!$this->cat_name) {
            ## TODO: resources
            $this->_errors[] = 'Musisz podać nazwę kategorii.';
        }

        ## czy wartosc statusu jest wlasciwa
        if (!in_array ($this->cat_status, array ('enabled', 'disabled'))) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwy status kategorii.';
        }

        if (!count ($this->_errors)) {
            $stmt = $GLOBALS['sql']->prepare ("
                INSERT INTO
                    `exp_categories`
                SET
                    `cat_name`      = ?,
                    `cat_status`    = ?
            ");

            $result = $stmt->execute (array (
                $this->cat_name,
                $this->cat_status
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
            'page_subtitle' => 'Dodawanie kategorii',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

