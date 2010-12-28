<?php
if (!defined ('EXPENSES')) exit;

# @package ExpUserDel
# @descr
# 	Moduł usuwania użytkowników

class ExpUserDel extends ExpAbstract {
	# @struct $user_id
	# @descr [string]
	# 	ID użytkownika

    public $user_id         = '';

	# @struct $_tpl_file
	# @descr [string]
	# 	Nazwa pliku szablonu.

    protected $_tpl_file    = '';

	# @struct $_errors
	# @descr [array]
	# 	Tablica komunikatów błędów powstałych podczas usuwania użytkownika.

    protected $_errors      = array ();

    # @function validate
    # @descr
    #   Walidacja danych wejściowych - zawsze musimy mieć $_GET['user_id']

    public function validate () {
        if (!isset ($_GET['user_id']) || !is_numeric ($_GET['user_id'])) {
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
        $stmt = $GLOBALS['sql']->prepare ('DELETE FROM `exp_users` WHERE `user_id` = ?');
        $stmt->execute (array ($_GET['user_id']));

        if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
            location (urldecode ($_REQUEST['b']));
        }
        else {
            location (null, array ('module' => 'users'));
        }
    }
}

