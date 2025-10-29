<?php

require_once 'AppController.php';
class SecurityController extends AppController
{
    public function login()
    {
        // TODO pobieramy z formularza email, hasło usera, następnie sprawdzamy w bazie danych czy istnieje taki user
        // jeśli nie istnieje to zwracamy odpowiednie komunikaty
        // jesli istnieje to przekierujemy go do dashboardu
        return $this->render('login');
    }
}