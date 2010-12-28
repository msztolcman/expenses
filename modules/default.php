<?php
if (!defined ('EXPENSES')) exit;

# @package
# @descr
#   Moduł ładowany domyślnie gdy inny moduł nie jest określony

class ExpDefault extends ExpAbstract {
    # @struct $_tpl_file
    # @descr [string]
    #   Nazwa pliku szablonu.

    protected $_tpl_file    = 'default.tpl';

    # @struct $_errors
    # @descr [array]
    #   Lista komunikatów błędów

    protected $_errors      = array ();

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
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => '',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }
}

