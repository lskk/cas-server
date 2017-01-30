# Cas (Central Authentication Service) Server

Laravel implement of CAS server

Fork from : [Simple Cas Server](https://github.com/leo108/simple_cas_server/)

## Protocol

* [CAS protocol](https://apereo.github.io/cas/4.2.x/protocol/CAS-Protocol-Specification.html)
* [Wikipedia](https://en.wikipedia.org/wiki/Central_Authentication_Service)

## Requirements

* PHP 5.5.9+
* [composer](https://getcomposer.org/)
* npm
* gulp

## Installation

1. `git clone https://github.com/lskk/cas-server`
2. `cd cas-server`
3. `composer install`
4. `npm install`
5. `gulp`

## Basic Usage

1. Edit `.env` file in the project's root directory, change the options's value that start with `DB_`
2. `./artisan migrate`
3. `./artisan db:seed`
4. `./artisan serve`
5. visit [http://localhost:8000](http://localhost:8000)

## Configuration

All configurations are set in `.env` file

### Application Settings

`APP_LOCATE`: application Language, `en` | `cn`

### CAS Settings

`CAS_ALLOW_RESET_PWD`: allow user reset password by email, `true` | `false`. if set to `true`, you should configure mail sending options

`CAS_TICKET_LEN`: ticket length

`CAS_TICKET_EXPIRE`: ticket ttl, time in seconds

`CAS_LOCK_TIMEOUT`: lock time while validating a ticket, time in microseconds

## License

[MIT](http://opensource.org/licenses/MIT)
