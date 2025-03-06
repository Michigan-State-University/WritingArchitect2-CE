# WritingArchitect2

## Installation

WritingArchitect2 is a PHP web application with MySQL as the database.

The script to initialize the database can be found in `./init_db/001_initial.sql`. This will seed the database with all the necessary information and a default administrator account with the following credentials:

-   username: `admin`
-   password: `changeme`

> [!CAUTION]
> The password for the administrator account should be changed as soon as possible to avoid unauthorized access.

The hostname, username, and password for the MySQL database should be filled in `./includes/Database.php`.

As for the PHP application, the `pdo_mysql` extension should be enabled.

## Security

The application implements a 90 minutes lock-out which automatically signs out the account after 90 minutes of inactivity. As you're navigating between different functionality of the application, this 90 minutes countdown is reset/extended.
