# PSI

  **1. Nazwa** 
Tasker – zarządzanie zadaniami
  
  **Opis projektu**
Cel: Aplikacja do zarządzania zadaniami z logowaniem użytkowników
Technologie: HTML5, CSS3, JavaScript, PHP, MySQL (XAMPP)
Wariant: A – czyste technologie, bez frameworków

  **2. Struktura katalogów**
/tasker
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── includes/
│   └── db.php
├── style.css
└── README.md

  **Funkcje aplikacji**
- Rejestracja użytkownika z walidacją danych
- Logowanie i sesje
- Panel z listą zadań użytkownika
- Dodawanie zadań
- Usuwanie zadań
- Licznik zadań i statystyki
- Wylogowanie
- Usunięcie konta

  **4. Struktura bazy danych**
  
**Tabela users:**
Kolumna	Typ	Uwagi
id	INT AUTO_INCREMENT	PRIMARY KEY
username	VARCHAR(255)	UNIQUE, NOT NULL
email	VARCHAR(255)	UNIQUE, NOT NULL
password	VARCHAR(255)	NOT NULL

**Tabela tasks:**
Kolumna	Typ	Uwagi
id	INT AUTO_INCREMENT	PRIMARY KEY
user_id	INT	FOREIGN KEY
content	TEXT	NOT NULL
  
  **5. Sposób uruchomienia**
- Wypakuj folder tasker do htdocs w XAMPP
- Uruchom XAMPP Control Panel, włącz Apache i MySQL
- Wejdź w przeglądarkę: http://localhost/phpmyadmin
- Stwórz bazę tasker
- Zaimportuj strukturę bazy (możesz stworzyć plik .sql lub opisać ręcznie)
- Otwórz aplikację przez http://localhost/tasker/register.php

