<?php
if (!defined ('EXPENSES')) exit;

# @package ExpIterator
# @descr
# 	Klasa implementująca interface Iterator. Stosowana jako zwracana wartość w klasach
# 	implementujących IteratorAggregate::getIterator ().

class ExpIterator implements Iterator {

	# @struct $_stmt
	# @descr [PDOStatement]
	# 	Wynik działania PDO::prepare () lub PDO::query () dla zapytania któremu implementujemy
	# 	iterator.

    protected $_stmt;

	# @struct $_stmt_params
	# @descr [array]
	# 	Parametry dla metody PDOStatement::execute (), przekazywane jako bindingi dla zapytania.

    protected $_stmt_params = array ();

	# @struct $_hooks
	# @descr [array]
	# 	Jeśli chcemy aby zwracane wartości przy każdej iteracji były modyfikowane, tutaj wkładamy
	# 	odwołania do funkcji modyfikujących dane. Kluczem musi być nazwa modyfikowanego pola,
	# 	wartością dane przekazywane jako pierwszy parametr do call_user_func ()

    protected $_hooks       = array ();

	# @struct $_data
	# @descr [array]
	# 	Dane z pojedynczej iteracji (zazwyczaj wynik zwrócony przez PDOStatement::fetch ())

    protected $_data;

	# @struct $_position
	# @descr [int]
	# 	Numer kolejny zwracanego zestawu danych.

    protected $_position    = 0;

	# @function __construct
	# @descr
	# 	Konstruktor.
	#
	# 	Zapamiętuje podane parametry.
	#
	# @input
	# 	* 0: stmt - (PDOStatement) "spreparowane" zapytanie
	# 	* 1: stmt_params - (array) [opt] dane dla PDOStatement::execute ()
	# 	* 2: hooks - (array) [opt] modyfikatory zestawu danych
	#
	# @output
	# 	-

    public function __construct ($stmt, $stmt_params = array (), $hooks = array ()) {
        $this->_stmt        = $stmt;
        $this->_stmt_params = $stmt_params;
        $this->_hooks       = $hooks;
    }

	# @function rewind
	# @descr
	# 	Implementacja metody interfejsu.
	#
	# 	Wykonuje zapytanie i ustawia pierwszy zestaw danych.
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#
	# @related
	# 	PDOStatement::execute
	# 	ExpIterator::next

    public function rewind () {
        $this->_stmt->execute ($this->_stmt_params);
        $this->_position = 0;
        $this->next ();
        return;
    }

	# @function current
	# @descr
	# 	implementacja metody interfejsu.
	#
	# 	Pobiera dane (PDOStatement::fetch ()) i ew. wykonuje poszczególne hooks.
	#
	# @input
	# 	-
	#
	# @output
	# 	0: [array] pobrany zestaw danych
	#
	# @related
	# 	call_user_func
	# 	$ExpIterator::_hooks

    public function current () {
        $data = $this->_data;

        if (count ($this->_hooks)) {
            foreach ($this->_hooks as $key => $hook) {
                $data[$key] = call_user_func ($hook, $data[$key]);
            }
        }

        return $data;
    }

	# @function key
	# @descr
	# 	Implementacja metody interfejsu.
	#
	# 	Zwraca bieżącą pozycję.
	#
	# @input
	# 	-
	#
	# @output
	# 	0: [int] pozycja

    public function key () {
        return $this->_position;
    }

	# @function next
	# @descr
	# 	Implementacja metody interfejsu.
	#
	# 	Pobiera następny zestaw danych.
	#
	# @input
	# 	-
	#
	# @output
	# 	-
	#
	# @related
	# 	PDOStatement::fetch()

    public function next () {
        ++$this->_position;
        $this->_data = $this->_stmt->fetch (PDO::FETCH_ASSOC);
        return;
    }

	# @function valid
	# @descr
	# 	Implementacja metody interfejsu.
	#
	# 	Sprawdza czy istnieją dane do zwrócenia (czyli czy mamy co pobierać).
	#
	# @input
	# 	-
	#
	# @output
	# 	* 0: [bool]
	#
	# @related
	# 	$ExpIterator::_data

    public function valid () {
        return is_array ($this->_data);
    }

}

