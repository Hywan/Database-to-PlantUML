# Database to PlantUML

This utility renders a graphical 2D visualisation of a database.

Currently, the only frontend is MySQL. There are 2 backends:
`commonmark` and `plantuml`. The `plantuml` backend allows to generate
visualisations into the following formats:

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

To use the `plantuml` backend, you can use the JAR in `./resource/plantuml.jar`.

# Example

Taking
[the `employee` test database from MySQL](https://github.com/datacharmer/test_db),
the visualisation as a PNG looks like this:

```sh
$ ./bin/database-to-plantuml -s employees | java -jar ./resource/plantuml.jar -verbose -pipe > output.png
```

![Example](https://cldup.com/Cgn7bqdEz5.png)

# License

BSD-3-License, but seriously, do what ever you want!
