<?php
if (!defined ('EXPENSES')) exit;

# @package ExpCategoryDel
# @descr
# 	Moduł usuwania kategorii

class ExpCategoryDel extends ExpAbstract {
	# @struct $cat_id
	# @descr [string]
	# 	ID kategorii

    public $cat_id         = '';

	# @struct $_tpl_file
	# @descr [string]
	# 	Nazwa pliku szablonu.

    protected $_tpl_file    = '';

	# @struct $_errors
	# @descr [array]
	# 	Tablica komunikatów błędów powstałych podczas usuwania kategorii.

    protected $_errors      = array ();

    # @function validate
    # @descr
    #   Walidacja danych wejściowych - zawsze musimy mieć $_GET['cat_id']

    public function validate () {
        if (!isset ($_GET['cat_id']) || !is_numeric ($_GET['cat_id'])) {
            location (null, array ('module' => 'default'));
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
    #
	# 	ExpAbstract::__prepare
    public function execute () {
        $stmt = $GLOBALS['sql']->prepare ('DELETE FROM `exp_categories` WHERE `cat_id` = ?');
        $stmt->execute (array ($_GET['cat_id']));

        if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
            location (urldecode ($_REQUEST['b']));
        }
        else {
            location (null, array ('module' => 'categories'));
        }
    }
}

