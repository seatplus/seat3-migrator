# Migration Package for seat3 to seatplus

[![Latest Version on Packagist](https://img.shields.io/packagist/v/seatplus/seat3-migrator.svg?style=flat-square)](https://packagist.org/packages/seatplus/seat3-migrator)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/seatplus/seat3-migrator/run-tests?label=tests)](https://github.com/seatplus/seat3-migrator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/seatplus/seat3-migrator/Check%20&%20fix%20styling?label=code%20style)](https://github.com/seatplus/seat3-migrator/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/seatplus/seat3-migrator.svg?style=flat-square)](https://packagist.org/packages/seatplus/seat3-migrator)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

This installation guide assumes you are using a functioning dockerized seatplus version. Since this will be a cli only package, access to the shell is mandatory. 

### Create a backup from seat3

Create a backup from seat 3 on your old instance

```shell
docker-compose exec mariadb sh -c 'exec mysqldump "$MYSQL_DATABASE" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD"' | gzip > seat_backup.sql.gz
```

### Prepare your instance

First we are going to create a container for the seat3 backup. Add the following to the `.env` file:

```dotenv
# .env

DB_BACKUP_DATABASE=seat_backup
```

then add the backup container to your `docker-compose.yml` a

```yaml
# docker-compose.yml

services:
    ...
    ### seatBackup  ##############################################
    seatBackup:
      image: mariadb:10.3
      restart: always
      environment:
          MYSQL_RANDOM_ROOT_PASSWORD: "yes"
          MYSQL_USER: ${DB_USERNAME}
          MYSQL_PASSWORD: ${DB_PASSWORD}
          MYSQL_DATABASE: ${DB_BACKUP_DATABASE}
      networks:
          - backend
```
Note: We are using the same DB username and user password as your normal seatplus db container does. However, we use the DB name you specified in the `env` file

### Import backup to the newly created seatBackup container

```shell
zcat seat_backup.sql.gz | docker-compose exec -T seatBackup sh -c 'exec mysql "$MYSQL_DATABASE" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD"'
```

You can install the package via composer:

```bash
composer require seatplus/seat3-migrator
```



You can publish and run the migrations with:

```env
php artisan vendor:publish --provider="Seatplus\Seat3Migrator\Seat3MigratorServiceProvider" --tag="seat3-migrator-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Seatplus\Seat3Migrator\Seat3MigratorServiceProvider" --tag="seat3-migrator-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$seat3-migrator = new Seatplus\Seat3Migrator();
echo $seat3-migrator->echoPhrase('Hello, Spatie!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Felix Huber](https://github.com/seatplus)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
