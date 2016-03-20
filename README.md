# SnakeDumper
SnakeDumper is a tool to create reasonable development dumps of your production database **which do not contain any personal data**. It works similar to mysqldumper (or related tools), but applies a set of data converters and filters to your data. (If no filters and converteres are used, it works exactly like any other database dumper.)

_Please note that the SnakeDumper is currently in an early preview phase. It is written to work with any SQL-compatible database, but is at the moment only tested against MySQL and SQLite._

## Features
- Create an SQL Dump of your production database **without personal data**
- **Data converters** remove / change personal data to
  - a random first name, last name
  - an empty string
  - NULL
  - fixed Value
  - etc.
- **Data filters** help to keep your development database small
  - Regular **SQL Conditions** (equals, unequals, lower than, ...)
  - Order of the result set (to select for example only the latest entries)
  - Limit the number of rows
  - SnakeDumper ensures the referential integrity of the database dump (e.g. if only the latest 100 customers should be dumped, SnakeDumper will only dump their billings but not the billings of other customers)
  - Or define a custom query for more advanced use cases (e.g. you want to change the table structure of the development dump)
- Dump only a set of white listed tables or ignore single tables
- Ignore the contents of specific tables (dump only the table structure)

## Installation
You can install the SnakeDumper with Composer (`digilist/snakedumper`).

## Usage
To use the SnakeDumper you have to create a configuration file which defines how to convert your database (see Example Configuration). You can run SnakeDumper with the following command:

```
php bin/snakedumper dump ./demo.yml
```

### Example Configuration
There are a lot of configuration options for the SnakeDumper available. The best way to get started is by looking at the example configuration file ([demo.yml](demo.yml)). For other available options, please take a look at the [docs/](docs).

### Security / Caution!
Please note that some configuration parameters are passed directly to the database server. Although this tool does not perform any changes on your data, it is still possible to alter your data with invalid configuration parameters (e.g. by defining a custom query which performs updates). So please do not configure this tool with any kind of user provided data! We do not perform any security checks at the moment! Use it at your own risk, we give absolutely no warranty.

## How to contribute
We are option for every type of contribution. You can test the SnakeDumper, report bugs, propose new features or help us with the development. Feel free to create a new issue or open a pull request :-)

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
