# Database to PlantUML

This utility renders a graphical 2D visualisation of a database.

Currently, the only supported frontends are **PostgreSQL** and **MySQL**.

There are these backends:
* `commonmark`
* `plantuml`
* `plantumlsinglefile`
 
The `plantuml` and `plantumlsinglefile` backend allow to generate visualisations into the
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

# License

BSD-3-License, but seriously, do what ever you want!

# PlantUMLSingleFile

This will generate separate files for:
* the definition of a table
  * filename: `table__[database]__[table].iuml` (include-file)
* its relations
  * filename: `relations__[database]__[table]__[referenced_table].iuml` (include-file)
* complete table definition
  * filename: `table__[database]__[table].puml`

and then output a puml-definition with includes to all of those table and relation-files.

The idea behind that is that this way it is possible to reuse single table definitions in other contexts.
e.g. by putting tables into groups

## Usage


```sh
$ bin/database-to-plantuml -d 'mysql:dbname=employees' -u root -s employees -b PlantUMLSingleFile | \
      java -jar resource/plantuml.jar -verbose -pipe > output.png
```
