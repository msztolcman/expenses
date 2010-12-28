<?php
if (!defined ('EXPENSES')) exit;

# @package ExpLogin
# @descr
# 	Moduł ekranu logowania

class ExpLogin extends ExpAbstract {
	# @struct $_tpl_file
	# @descr [string]
	# 	Nazwa pliku szablonu modułu.

    protected $_tpl_file = 'login.tpl';

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
        $args = array (
            'user_login'    => isset ($_COOKIE['user_login']) ? $_COOKIE['user_login'] : '',
        );

        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Zaloguj się!',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

