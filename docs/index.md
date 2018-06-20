# SnakeDumper Documentation

## Introduction

SnakeDumper is a tool that was designed to create reasonable development database dumps for from your production database. At the moment it supports only SQL (and was actually only tested with MySQL, but should be supported by any other database - or require only a small set of changes). However, the vision is that it should support NoSQL databases as well - one day.

The goal of SnakeDumper is to provide developers with a development dump of a production database that does not contain any sensitive data.

## A little warning

Please note that some configuration parameters are passed directly to the database server. Although this tool does not perform any changes on your data, it is still possible to alter your data with invalid configuration parameters (e.g. by defining a custom query which performs updates). So please do not configure this tool with any kind of user provided data! We do not perform any security checks at the moment! Use it at your own risk, we give absolutely no warranty.

Furthermore, SnakeDumper does not guarantee that there is no sensitive data left in your dump. You - as a user of SnakeDumper - are responsible for the correct configuration and usage.

## Usage

You can use SnakeDumper by defining a configuration file for your dump (see next section). You can then pass the configuration file as argument to `snakedumper`.

You can create a dump like this:

```
php bin/snakedumper dump ./demo.yml
```

```
php snakedumper.phar dump ./demo.yml
```

You can override most of the connection and output settings via command line arguments. Please execute `snakedumper dump --help` to see the available options.

## Configuration File

SnakeDumper can be configured with a YAML file. You can find a demo here [here](../demo.yml).

The configuration file consists of the following options:
- `dumper`: Which dumper to use (required)
- `output`: The output configuration (required)
- `database`: Database connection configuration (required)
- `table_white_list`: List of tables that should be dumped (optional)
- `tables`: Table specific dump configuration (optional, but when you define nothing it's a simple database dump without data conversion)

In the following we will give a little explanation to each option section.

### dumper

As SnakeDumper supports only SQL at the moment, the only available option at the moment is `Sql`.

### output

Available options:
- `rows_per_statement` (int): How many rows should be grouped into a single insert statement
- `file` (string): The filepath where the generated dump should be stored
- `gzip` (bool): indicates whether the generated dump should be gzipped (true/false)

**Hint**: If you want to test/debug your configure, just use php://stdout as filepath to see the generated dump in your terminal.

### database

Available options (all string):
- `driver`: Database driver to use (depending on your database platform). You can find a list of available drivers [here](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.7/reference/configuration.html#connecting-using-a-url).
- `host`
- `user`
- `password`
- `dbname`
- `charset`

### table_white_list

By default SnakeDumper dumps all available tables. By defining a white list you can configure explicitly which tables you want to include in your dump.

### tables

Here you can define table specific configuration. The following options are available:

- `ignore_table` (bool): Set to true to ignore this table in the dump
- `ignore_content` (bool): Set to true to dump the table's structure, but ignore its contents
- `order_by` (string, e.g `id ASC`): Define the ordering of the queried data, e.g. to select only the most recent records
- `limit` (int): Number of rows that should be selected from this table
- `converters`: Rules that should be applied to the dumped data. See below for more details
- `filters`: Conditions that must apply to the selected rows to be included in the dump (e.g. select only customers from a specific country). See below for more details.
- `query` (string): If the available options are not sufficient, you can also define a custom query to select the data that should be dumped

## Filters / Conditions

There are a number of filters that you can use to reduce the amount of dumped data. Most filters are based on standard SQL conditions.

A filter is an array with three items: [operator, column, value].

Your filters can then look like this:
```yaml
    filters:
        - [in, 'id', [1, 2, 3]] # only select rows which id is 1, 2 or 3
        - [like, 'name', 'Markus %'] # only select rows which column "name" starts with Markus
```

Currently, the following operators are available:
- eq
- neq
- lt
- lte
- gt
- gte
- isNull
- isNotNull
- like
- notLike
- in
- notIn

## Converters

There are lots of data converts available that help to convert any sensitive information into arbitrary generated data. You can define converters for a table under the `converters` key. Each column can have multiple converters which are applied in sequence.

The configuration looks similar to the following:

```yaml
    converters:
        name:
            - JohnDoe
        street:
            - StreetName
        company_name:
            - Faker:
                formatter: company

```

You can find all available Converters [here](https://github.com/digilist/SnakeDumper/tree/master/src/Converter). In the future I might provide a list with examples for each converter, but currently there is none. So please take a look at the converters to see if they accept parameters.

By using the faker converter you can use any available data generator that is part of the famous [Faker library](https://github.com/fzaninotto/Faker).

## Relations between tables & referential integrity

It is typical for a relational database to have relations between tables. For example, you can have customers and billings. If you know exclude some customers you do not want to have the billings of those customers included in your dump.

Consider the following example:

Table `customers`:

 id | first_name | last_name
---: | ---: | ---:
1 | Markus | Fasselt
2 | Linus | Torvalds
3 | Rasmus | Lerdorf

Table `billings`:

id | customer_id | amount
---: | ---: | ---:
1 | 1 | 20
2 | 1 | 30
3 | 2 | 10
4 | 3 | 10
5 | 3 | 42
6 | 3 | 20

When you now define, that you only want to have the first to customers (id 1 and 2), the result should be the following:

Table `customers`:

id | first_name | last_name
---: | ---: | ---:
1 | Markus | Fasselt
2 | Linus | Torvalds

Table `billings`:

id | customer_id | amount
---: | ---: | ---:
1 | 1 | 20
2 | 1 | 30
3 | 2 | 10

As you can see, customer 3 is not included in the customers table. Furthermore, the billings do not contain the billings of customer 3.

This feature is a part of SnakeDumper and allows you to create small development dumps that keep the referential integrity in the database.

Note: SnakeDumper cannot handle cyclic references between tables at the moment. This only works in acyclic databases.

## Custom queries

There isn't much to say about custom queries. You can write any `SELECT` query you like and all dumped rows will be included into the dump.

You can include the magic `$autoConditions` string into your query to consider also conditions defined under the `filters` configuration key and also automatic relational filters to keep the data integrity.
