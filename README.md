# Online bookstore project
This is a pet project for my PHP university course. The project meets the requirements:

* More than 2/3 of the project is server-loaded, it uses more than one data-collection form and more than one filtering/search form.
* The project has two secure components, access to which is allowed only via registration or log-in.
* The project has two roles implemented: USER and ADMIN.
* Admin role has additional functionalities granted: (creation, update, deletion) of entities.
* Security-wise:
    * All input forms are secured by validation on server side before inserting in a DB statement (regex + use of prepared statements)
    * The access to the restricted parts of the project is done via password-based authenitification.
    * All of the passwords are stored hashed in the DB
    * The access to the restricted parts of the project relies on the use of SESSIONS and session variables to prevent users from bypassing authentification.

The project uses PHP and JS as backend, HTML + Tailwind as Frontend and MySQL as DB.


The project is intended to run under XAMPP (c:/xampp/htdocs/PHP).

## Requirements
- Windows with XAMPP installed
- PHP (bundled with XAMPP) — recommended 7.4+ or 8.x
- MySQL/MariaDB (bundled with XAMPP)

## Quick setup
1. Place project files in:
    ```
    c:\xampp\htdocs\PHP
    ```
2. Start XAMPP services: Apache and MySQL.
3. Open in browser:
    ```
    http://localhost/PHP/
    ```

## Project structure (actual)
- php/ — project root
    - public/ — web-accessible files (web root)
    - secure/ — protected application code
        - user/ — user-facing secure pages
        - admin/ — admin-facing secure pages
    - src/
        - img/ — common icons/images
            - covers/ — book cover images
        - scripts/ — utility scripts 
    - README.md — this file
    - index.php — index file

## Quick database import
- Create the DB 'bookstore' via phpMyAdmin (http://localhost/phpmyadmin) and import the provided SQL schema in /src/scripts:
  ```
  c:\xampp\mysql\bin\mysql -u root -p database_name < schema.sql
  ```

## Troubleshooting
- 403/404: confirm files are in c:\xampp\htdocs\PHP and Apache document root is correct.
- Port conflicts: change Apache port in XAMPP if 80 is in use.
- Permission issues: ensure files are readable by Apache.