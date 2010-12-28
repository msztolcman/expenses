<?php
if (!defined ('EXPENSES')) exit;

# @package ExpItems
# @descr
#   Moduł wyświetlający listę zakupów

class ExpItems extends ExpAbstract implements IteratorAggregate {
    # @struct $_categories
    # @descr
    #   Lista dostępnych kategorii

    protected $_categories  = array ();

    # @struct $_date_start
    # @descr
    #   Początek okresu z jakiegos pobieramy dane

    protected $_date_start  = '';

    # @struct $_date_end
    # @descr
    #   Koniec okresu z jakiegos pobieramy dane

    protected $_date_end    = '';

    # @struct $_search
    # @descr
    #   Kryteria wyszukiwania wprowadzone przez usera

    protected $_search      = '';

    # @struct $_tpl_file
    # @descr [string]
    #   Nazwa pliku szablonu.

    protected $_tpl_file    = 'items.tpl';

    # @struct $_sort
    # @descr [string]
    #   Kolejność sortowania (rosnąco/malejąco)

    protected $_sort        = 'asc';

    # @struct $_order
    # @descr [string]
    #   Pole po którym sortujemy

    protected $_order       = 'item_id';

    # @struct $_where
    # @descr
    #   Warunek używany przy pobieraniu elementów - potrzebny zachowany osobno, bo używany w dwóch miejscach.

    protected $_where       = "
                    `i`.`item_date_buy` >= ?
                AND
                    `i`.`item_date_buy` < ?
    ";

    # @struct $_where_params
    # @descr
    #   Parametry przekazywane do warunków z ExpItems::$_where

    protected $_where_params    = array ();

    # @function init
    # @descr
    #   Ustalenie sortowania, oraz zakresów dat
    # @input
    #   -
    #
    # @output
    #   -
    #
    # @related
    #   is_sql_field_name

    public function init () {
        ## sortowanie
        if (isset ($_GET['sort']) && $_GET['sort'] == 'desc') {
            $this->_sort   = 'desc';
        }
        if (isset ($_GET['order']) && is_sql_field_name ($_GET['order'], 'exp_items', array ('cat_name'))) {
            $this->_order  = $_GET['order'];
        }
        else {
            $this->_order = 'item_id';
        }

        ## filtry: daty
        if (isset ($_GET['date_start']) && preg_match ('#(\d\d)-(\d\d)-(\d\d\d\d)#', date_prepare ($_GET['date_start']), $match)) {
            $this->_date_start = "$match[3]-$match[2]-$match[1]";
        }
        else {
            $this->_date_start = strftime ('%Y-%m-%d', time ());
        }

        if (isset ($_GET['date_end']) && preg_match ('#(\d\d)-(\d\d)-(\d\d\d\d)#', date_prepare ($_GET['date_end']), $match)) {
            $this->_date_end = "$match[3]-$match[2]-$match[1]";
        }
        else {
            $date_start_parts = explode ('-', $this->_date_start);
            $this->_date_end = strftime ('%Y-%m-%d', mktime (0, 0, 0, $date_start_parts[1], $date_start_parts[2], $date_start_parts[0]) + 86400);
        }

        ## filtry: kategorie
        ## lista kategorii
        $stmt = $GLOBALS['sql']->prepare ('
            SELECT
                `cat_id`,
                `cat_name`
            FROM
                `exp_categories`
            USE KEY (`name_status`)
            WHERE
                `cat_status` = "enabled"
            ORDER BY
                `cat_name` ASC
        ');

        $result = $stmt->execute ();
        if (!$result) {
            ## TODO: resources
            $this->_errors[] = 'Wystąpił błąd SQL: '.$stmt->errorInfo ();
        }
        else {
            while (($row = $stmt->fetch (PDO::FETCH_ASSOC))) {
                $this->_categories[] = array (
                    'cat_id'        => $row['cat_id'],
                    'cat_name'      => $row['cat_name'],
                    'cat_selected'  => (!isset ($_GET['categories']) || in_array ($row['cat_id'], $_GET['categories'])),
                );
            }
        }

        ## filtry: wyszukiwanie po nazwie
        if (isset ($_GET['search']) && strlen ($_GET['search'])) {
            $this->_search = $_GET['search'];
        }

        ## warunki
        $this->_where_params = array (
            $this->_date_start,
            $this->_date_end,
        );

        if ($this->_search) {
            $this->_where .= '
                AND
                    `i`.`item_name` RLIKE ?';
            $this->_where_params[] = '^'. search_convert ($this->_search) .'$';
        }

        if (isset ($_GET['categories'])) {
            $this->_where .= '
                AND
                    `i`.`cat_id` IN (';

            $this->_where .= join (',', array_fill (0, count ($_GET['categories']), '?'));

            $this->_where .= ')';

            foreach ($_GET['categories'] as $v) {
                $this->_where_params[] = (int) $v;
            }
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
            'current_order' => $this->_order,
            'items'         => $this,
            'date_start'    => date_reverse ($this->_date_start),
            'date_end'      => date_reverse ($this->_date_end),
            'value'         => $this->value (),
            'permissions'   => $_SESSION['permissions'],
            'categories'    => $this->_categories,
            'search'        => $this->_search,
        );
        $root_args  = array (
            ## TODO: resources
            'page_subtitle' => sprintf ('Lista zakupów dokonanych między %s a %s',
                date_reverse ($this->_date_start),
                date_reverse ($this->_date_end)
            ),
            'page_content'  => $this->_tpl,
        );

        $this->__prepare ($args, $root_args);
    }

    # @function value
    # @descr
    #   Policzenie wartości wyświetlanych zakupów
    #
    # @input
    #   -
    #
    # @output
    #   -

    public function value () {
        $stmt = $GLOBALS['sql']->prepare ("
            SELECT
                `i`.`item_value`
            FROM
                `exp_items` AS `i`
            WHERE
                ". $this->_where
        );

        $stmt->execute ($this->_where_params);

        $sum = 0.0;
        while (($row = $stmt->fetch (PDO::FETCH_ASSOC))) {
            $sum += $row['item_value'];
        }

        return number_format ($sum, 2);
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
                `i`.`item_id`,
                `i`.`cat_id`,
                `i`.`item_name`,
                `i`.`item_quant`,
                `i`.`item_quant_unit`,
                `i`.`item_value`,
                DATE_FORMAT(`i`.`item_date_buy`, '%d-%m-%Y') AS `item_date_buy`,

                `c`.`cat_name`
            FROM
                `exp_items` AS `i`
            LEFT JOIN
                    `exp_categories` AS `c`
                ON
                    `i`.`cat_id` = `c`.`cat_id`
            WHERE
                ". $this->_where ."
            ORDER BY
                ". $this->_order ."
                " . strtoupper ($this->_sort)
        );

        return new ExpIterator ($stmt, $this->_where_params);
    }
}

