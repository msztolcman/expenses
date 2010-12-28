<?php
if (!defined ('EXPENSES')) exit;

# @package ExpConfig
# @descr
#   Klasa przechowująca konfigurację.
abstract class ExpConfig {
    # @const DB_HOST
    # @descr [string]
    #   Host dla bazy danych.
    const DB_HOST       = '';

    # @const DB_USER
    # @descr [string]
    #   Użytkownik do połączenia z bazą danych.
    const DB_USER       = '';

    # @const DB_PASS
    # @descr [string]
    #   Hasło do bazy danych.
    const DB_PASS       = '';

    # @const DB_NAME
    # @descr [string]
    #   Nazwa bazy danych.
    const DB_NAME       = '';

    # @const HTTP_ROOT
    # @descr [string]
    #   Adres pod jakim dostępny jest aplikacja
    const HTTP_ROOT     = '';

    # @const FS_ROOT
    # @descr [string]
    #   Ścieżka do katalogu na systemie plików gdzie trzymane są pliki aplikacji
    const FS_ROOT       = '/';

    # @const DEBUG
    # @descr [string]
    #   Informacja czy chcemy dostawać na ekran komunikaty błędów (true - system testowy) czy na
    #   adres email ADMIN_EMAIL (false - system produkcyjny).
    const DEBUG         = false;

    # @const PASS_SALT
    # @descr [string]
    #   Sól używana do kryptowania hasła.
    const PASS_SALT     = '';

    # @const ADMIN_EMAIL
    # @descr [string]
    #   Adres email na jaki będą wysyłane komunikaty błędów przy ExpConfig::DEBUG ustawionym na true.
    #   Nigdy nie będzie wyświetlony użytkownikom.
    const ADMIN_EMAIL   = '';
}

