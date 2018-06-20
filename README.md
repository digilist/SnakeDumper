# SnakeDumper
SnakeDumper is a tool to create a reasonable development dump of your production database **which does not contain any sensitive data**. It works similar to mysqldumper (or related tools), but applies a set of data converters and filters to your data. (If there aren't any filters and converteres configured it works exactly like any other dumper.)

_Please note that the SnakeDumper is currently in an early preview phase. It is written to work with any SQL-compatible database, but is at the moment only tested against MySQL and SQLite._ The vision is to support a wide range of database systems later (including NoSQL).

## Features

### Filter
SnakeDumper allows to filter the data that will be dumped to keep the development database small. Therefore, there are various filters, that can be applied before the data is loaded from the database.

You can
- apply regular SQL conditions (equals, unequals, lower than, ...),
- limit the number of rows,
- change the order of the selected rows (e.g. to select only the latest entries),
- and apply a custom SQL to be fully flexible.

If you apply those filters, SnakeDumper will keep the referential integrity of your database dump so that all foreign keys stay valid. All rows  that have references on rows that were excluded from the dump will be skipped. Example: There is a table with customers and billings. You decide that you only want to dump the latest 100 customers. SnakeDumper will then automatically dump only those billings that belong to the considered customers.

Furthermore, you can configure SnakeDumper to skip whole tables or the contents of some tables.

### Data Converter

The main objective of the SnakeDumper is to build reasonable development dumps that do not contain any sensitive data. Therefore, there are are various data converts that allow to alter the dumped data and replace all sensitive information with other random (or static) data.

There are already a lot of converters. Here are just a few examples:
- Random first and last name
- Random company names
- Random number
- Empty string
- Static value

And generally, you can use anything that is offered by the awesome faker library.

## Installation
You can install the SnakeDumper with Composer (`digilist/snakedumper`) or download the phar: http://digilist.de/snakedumper.phar

## Usage
To use the SnakeDumper you have to create a configuration file which defines how to convert your database (see Example Configuration). You can run SnakeDumper with one of the following commands (depending on your installation method):

```
php bin/snakedumper dump ./demo.yml
```
```
php snakedumper.phar dump ./demo.yml
```

To get started please take a look at the [docs](docs/index.md).

### Example Configuration

There are a lot of configuration options for the SnakeDumper available. The best way to get started is by looking at the example configuration file ([demo.yml](demo.yml)). 

## Testing

The test suite needs a running MySQL server at the moment. You can use the following Docker command to start one:

```bash
docker run -it -p 3306:3306 --rm -e MYSQL_ALLOW_EMPTY_PASSWORD=1 mysql:5.7 --character-set-server=utf8 --collation-server=utf8_unicode_ci
```

Alternatively, there is a [docker-compose.yml](docker-compose.yml) which you can use to start a Docker container for testing.

### Security / Caution!

Please note that some configuration parameters are passed directly to the database server. Although this tool does not perform any changes on your data, it is still possible to alter your data with invalid configuration parameters (e.g. by defining a custom query which performs updates). So please do not configure this tool with any kind of user provided data! We do not perform any security checks at the moment! Use it at your own risk, we give absolutely no warranty.

Furthermore, SnakeDumper does not guarantee that there is no sensitive data left in your dump. You - as a user of SnakeDumper - are responsible for the correct configuration and usage.

## How to contribute
We are open for every type of contribution. You can test the SnakeDumper, report bugs, propose new features or help us with the development. Feel free to create a new issue or open a pull request :smiley:

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
