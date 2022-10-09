# Title

[![Build Status](https://github.com/assoconnect/php-smtp-toolbox/actions/workflows/build.yml/badge.svg)](https://github.com/assoconnect/php-smtp-toolbox/actions/workflows/build.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=assoconnect_php-smtp-toolbox&metric=alert_status)](https://sonarcloud.io/dashboard?id=assoconnect_php-smtp-toolbox)

Set of SMTP tools for PHP including implementations of email addresses validation for various IPS.

## Installation

```
composer require assoconnect/php-smtp-toolbox
```

## Unsupported providers
### @free.fr
The server always responds the same answer for valid and invalid email addresses: 
* `250 2.1.0 Ok` to the `MAIL FROM` command
* `250 2.1.5 Ok` to the `RCPT TO` command
