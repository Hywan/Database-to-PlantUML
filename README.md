# Database to PlantUML

This utility renders a graphical 2D visualisation of a database.

Currently, the only supported frontends are **PostgreSQL** and
**MySQL**. There are 2 backends: `commonmark` and `plantuml`. The
`plantuml` backend allows to generate visualisations into the
following formats:

  * PNG,
  * SVG,
  * EPS,
  * PDF,
  * VDX,
  * XMI,
  * HTML,
  * TXT,
  * UTXT,
  * LaTeX.

# Installation

With [Composer](https://getcomposer.org/), simply run the following command:

```sh
$ composer install
```

If you would like to use it as a dependency of your project, then:

```sh
$ composer require hywan/database-to-plantuml
```

To use the `plantuml` backend, you can use the JAR in `resource/plantuml.jar`.

# Examples with…

## … PostgreSQL

Taking as an example the famous `employees` use case:

```sh
# Import the schema.
$ psql -f resource/samples/pgsql-employees.sql postgres

# Generate the visualisation.
$ bin/database-to-plantuml -d 'pgsql:dbname=employees' -u hywan -s employees | \
      java -jar resource/plantuml.jar -verbose -pipe > output.png
```

![Output with PostgreSQL](https://cldup.com/UMsPg3WKh0.png)

## … MySQL

With the same `employees` use case:

```sh
# Import the schema.
$ mysql -u root < resource/samples/mysql-employees.sql

# Generate the visualisation.
$ bin/database-to-plantuml -d 'mysql:dbname=employees' -u root -s employees | \
      java -jar resource/plantuml.jar -verbose -pipe > output.png
```

![Output with MySQL](https://cldup.com/Cgn7bqdEz5.png)

Note: Outputs differ because the `employees` examples are not exactly
the same. They are here to illustrate the tool only.

# Errors ...

Sometimes things happen...

## ... could not find driver

Check if php has the good PDO module installed :
```bash
$ php -m |grep -i pdo
PDO
pdo_mysql
```

# License

BSD-3-License, but seriously, do what ever you want!
