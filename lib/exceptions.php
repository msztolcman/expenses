<?php
if (!defined ('EXPENSES')) exit;

# @package ExpException
# @descr
#   Podstawowy (bazowy) wyjątek aplikacji

class ExpException extends Exception {}

# @package ExpExceptionModuleNotFound
# @descr
#   Wyjątek rzucany w momencie gdy zażądano modułu który nie został znaleziony.

class ExpExceptionModuleNotFound extends ExpException {}

# @package ExpExceptionNoPermission
# @descr
#   Wyjątek rzucany w momencie gdy zażądano dostępu do modułu do ktorego użytkownik nie ma uprawnień.

class ExpExceptionNoPermission extends ExpException {}

