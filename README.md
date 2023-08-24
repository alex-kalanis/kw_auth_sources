# kw_auth_sources

[![Build Status](https://app.travis-ci.com/alex-kalanis/kw_auth_sources.svg?branch=master)](https://app.travis-ci.com/github/alex-kalanis/kw_auth_sources)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alex-kalanis/kw_auth_sources/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alex-kalanis/kw_auth_sources/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/alex-kalanis/kw_auth_sources/v/stable.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_auth_sources)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.3-8892BF.svg)](https://php.net/)
[![Downloads](https://img.shields.io/packagist/dt/alex-kalanis/kw_auth_sources.svg?v1)](https://packagist.org/packages/alex-kalanis/kw_auth_sources)
[![License](https://poser.pugx.org/alex-kalanis/kw_auth_sources/license.svg?v=1)](https://packagist.org/packages/alex-kalanis/kw_auth_sources)
[![Code Coverage](https://scrutinizer-ci.com/g/alex-kalanis/kw_auth_sources/badges/coverage.png?b=master&v=1)](https://scrutinizer-ci.com/g/alex-kalanis/kw_auth_sources/?branch=master)

Authentication to site - where the data with accounts are stored and how to work with them.

These libraries represent internal getters and setters to access accounts on target
site. Just configure where to find them and it's possible to process all basic yet
necessary things. It's based on real *nix files and structures.

There are three parts. First one is usual accounts itself. Second one is groups
\- limit access to things in accordance with preset groups on accounts. And third
one is system classes. That limits access too, but it is written directly into
the code of each part and it is not need to rely on group ids and if they exists.
So groups limits interactions between users and system classes between user and
system.

It has variants and interfaces for possibility to use single account file, multiple
account files, certificates with passwords and groups. The account files can be
switched to different implementations. Or database can be used as data source for
both accounts and groups. And either example one on kw_mapper or your own
implementation.

Your system probably do not need the same things as mine. So some things can be
ignored and another can be available under "extra".

## PHP Installation

```
{
    "require": {
        "alex-kalanis/kw_auth_sources": "3.0"
    }
}
```

(Refer to [Composer Documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction) if you are not
familiar with composer)


## PHP Usage

1.) Use your autoloader (if not already done via Composer autoloader)

2.) Add some external packages with connection to the local or remote services.

3.) Connect the "kalanis\kw_auth_sources" into your app. Extends it for setting your case. For details use example.

4.) Just call \kalanis\kw_auth_sources\Access\Factory in your code

## Caveats

It's build for standalone usage - nothing more need than basics and dependencies.
