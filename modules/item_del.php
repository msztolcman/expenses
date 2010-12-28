<?php
if (!defined ('EXPENSES')) exit;

# @package ExpItemDel
# @descr
# 	Moduł usuwania zakupu

class ExpItemDel extends ExpAbstract {
	# @struct $item_id
	# @descr [string]
	# 	ID zakupu

    public $item_id         = '';

	# @struct $_tpl_file
	# @descr [string]
	# 	Nazwa pliku szablonu.
    protected $_tpl_file    = '';


	# @struct $_errors
	# @descr [array]
	# 	Tablica komunikatów błędów powstałych podczas usuwania zakupu

    protected $_errors      = array ();

    # @function validate
    # @descr
    #   Sprawdzenie czy jest wymagany klucz item_id w $_GET

    public function validate () {
        if (!isset ($_GET['item_id']) || !is_numeric ($_GET['item_id'])) {
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
	# 	ExpAbstract::__prepare

    public function execute () {
        $stmt = $GLOBALS['sql']->prepare ('DELETE FROM `exp_items` WHERE `item_id` = ?');
        $stmt = $stmt->execute (array ($_GET['item_id']));

        if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
            location (urldecode ($_REQUEST['b']));
        }
        else {
            location (null, array ('module' => 'items'));
        }
    }
}

