<?php
if (!defined ('EXPENSES')) exit;

# @package ExpItemAdd
# @descr
# 	Moduł dodawania zakupu

class ExpItemAdd extends ExpAbstract {
	# @struct $item_name
	# @descr [string]
	# 	Nazwa zakupu

    public $item_name       = '';

	# @struct $item_note
	# @descr [string]
	# 	Opis zakupu

    public $item_note       = '';

	# @struct $item_quant
	# @descr [string]
	# 	Ilość

    public $item_quant      = 1.0;

	# @struct $item_quant_unit
	# @descr [string]
	# 	Jednostka

    public $item_quant_unit = 'szt';

	# @struct $item_value
	# @descr [string]
	# 	Wartość zakupu (cała wartość, nie za sztukę/kg/l)

    public $item_value      = 0.0;

	# @struct $item_date_buy
	# @descr [string]
	# 	Data dokonania zakupu

    public $item_date_buy   = '';

	# @struct $cat_id
	# @descr [string]
	# 	ID Kategorii

    public $cat_id          = -1;

	# @struct $_categories
	# @descr [string]
	# 	Lista aktywnych kategorii

    public $_categories     = array ();

	# @struct $_quant_units
	# @descr [string]
	# 	Lista dostępnych jednostek

    public $_quant_units    = array ();

	# @struct $_tpl_file
	# @descr [string]
	# 	Nazwa pliku szablonu.

    protected $_tpl_file    = 'item_add.tpl';

	# @struct $_errors
	# @descr [array]
	# 	Tablica komunikatów błędów powstałych podczas tworzenia notatki.

    protected $_errors      = array ();

	# @function init
	# @descr
	# 	Pobranie listy dostępnych kategorii, oraz listę dostępnych jednostek
	# @input
	# 	-
	#
	# @output
	# 	-

    public function init () {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `cat_id`,
                `cat_name`
            FROM
                `exp_categories`
            USE KEY (`name_status`)
            WHERE
                `cat_status` 	= 'enabled'
            ORDER BY
                `cat_name`
        ");

        $stmt->execute ();
        $this->_categories = $stmt->fetchAll (PDO::FETCH_ASSOC);
        $stmt->closeCursor ();

        if (!$this->_categories) {
            location (null, array ('module' => 'category_add'));
        }

        $stmt = $GLOBALS['sql']->prepare ('
            SHOW COLUMNS FROM
                `exp_items`
            LIKE
                "item_quant_unit"
        ');
        $stmt->execute ();
        $stmt = $stmt->fetch (PDO::FETCH_ASSOC);
        $stmt = substr ($stmt['type'], 6, -2);
        $this->_quant_units = explode ("','", $stmt);
    }

    # @function on_get
    # @descr
    #   Uzupełnienie domyślnej wartości daty zakupu
    #
    # @input
    #   -
    #
    # @output
    #   -

    public function on_get () {
        ## uzupelniamy date zakupu o taka jaka juz zostala podana przy poprzednim zakupie
        if (isset ($_GET['item_date_buy'])) {
            $this->item_date_buy = $_GET['item_date_buy'];
        }
        else {
            $this->item_date_buy = strftime ('%d-%m-%Y', time ());
        }
    }

	# @function on_post
	# @descr
	# 	Zapisanie danych zakupu
	# 	Wykonuje testy na poprawność danych
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#

    public function on_post () {
        $this->item_name        = trim ($_POST['item_name']);
        $this->item_note        = trim ($_POST['item_note']);
        $this->item_quant       = float_prepare ($_POST['item_quant']);
        $this->item_quant_unit  = trim ($_POST['item_quant_unit']);
        $this->item_value       = float_prepare ($_POST['item_value']);
        $this->item_date_buy    = date_prepare ($_POST['item_date_buy']);
        $this->cat_id           = (int) $_POST['cat_id'];

        if ($this->cat_id < 0 || !in_select_options ($this->cat_id, 'cat_id', $this->_categories)) {
            $this->_errors[] = 'Błędna kategoria';
        }

        if (!strlen ($this->item_name)) {
            ## TODO: resources
            $this->_errors[] = 'Brak nazwy produktu.';
        }

        else if (strlen ($this->item_name) > 255) {
            ## TODO: resources
            $this->_errors[] = 'Zbyt długa nazwa produktu (nie może przekroczyć 255 znaków)';
        }

        if (strlen ($this->item_note) > 65536) {
            ## TODO: resources
            $this->_errors[] = 'Zbyt długi dodatkowy opis produktu (nie może przekroczyć 65536 znaków: obecna długość opisu: )'.
                strlen ($this->item_note);
        }

        if ($this->item_quant <= 0) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwa ilość produktu';
        }

        if (!in_array ($this->item_quant_unit, $this->_quant_units)) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwa jednostka produktów';
        }

        if ($this->item_value <= 0) {
            ## TODO: resources
            $this->_errors[] = 'Niewłaściwa wartość produktów';
        }

        if (
            !preg_match ('#^(\d\d)-(\d\d)-(\d\d\d\d)$#', $this->item_date_buy, $match) ||
            ($match[1] < 1 || $match[1] > 31) ||
            ($match[2] < 1 || $match[2] > 12) ||
            ($match[3] < 2010 || $match[3] > 2100)
        ) {
            ## TODO: resources
            $this->_errors[] = 'Błędna data zakupu';
        }

        if (!count ($this->_errors)) {
            $stmt = $GLOBALS['sql']->prepare ("
                INSERT INTO
                    `exp_items`
                SET
                    `cat_id`            = ?,
                    `user_id`           = ?,
                    `item_name`         = ?,
                    `item_note`         = ?,
                    `item_quant`        = ?,
                    `item_quant_unit`   = ?,
                    `item_value`        = ?,
                    `item_date_buy`     = ?,
                    `item_date_add`     = NOW()
            ");

            $result = $stmt->execute (array (
                $this->cat_id,
                $_SESSION['user_id'],
                $this->item_name,
                $this->item_note,
                $this->item_quant,
                $this->item_quant_unit,
                $this->item_value,
                date_reverse ($this->item_date_buy)
            ));

            if (!$result) {
                ## TODO: resources
                $this->_errors[] = 'Wystąpił błąd SQL: '.$stmt->errorInfo ();
            }
            else if (isset ($_REQUEST['b']) && $_REQUEST['b']) {
                location (urldecode ($_REQUEST['b']));
            }
            else {
                location (null, array ('module' => 'item_add', 'item_date_buy' => $this->item_date_buy));
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
            'errors'        => $this->_errors,
            'categories'    => $this->_categories,
            'item'          => $this,
            'quant_units'   => $this->_quant_units,
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => 'Dodawanie produktu',
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }

}

