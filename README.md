GitLab API for Nette
====================

![Integrity check](https://github.com/baraja-core/gitlab-api/workflows/Integrity%20check/badge.svg)

This package serves as a transport layer between a specific application and GitLab.

With this package, you can easily submit queries to GitLab, detect error messages in the Tracy bar, and monitor request load.

Requests of type `GET` are automatically cached for `12 hours` unless told otherwise.

Requests like `POST`, `PUT`, `DELETE` and other change actions are not cached at all and we always retransmit all data.

![GitLab Tracy panel](/src/gitlab-api-tracy.png "GitLab Tracy panel")

Installation
---------

Use the Composer command:

```shell
composer require baraja-core/gitlab-api
```

Next, you need to set the service configuration for Nette in the NEON file.

Default minimum configuration:

```yaml
services:
   gitLabAPI:
      factory: baraja\GitLabApi\GitLabApi(%gitLab.token%)

parameters:
   gitLab:
      token: 123-abcDEFghiJKL-789

tracy:
   bar:
      - Baraja\GitLabApi\GitLabApiPanel
```

You must always change the API token for your user account!

Configuration
-----------

In the `parameters` section, you need to enter the default API token to connect to GitLab:

Example:

```yaml
parameters:
   gitLab:
      token: 123-abcDEFghiJKL-789
```

Optionally, you can set to use Nette Cache:

```yaml
services:
   gitLabAPI:
      factory: baraja\GitLabApi\GitLabApi(%gitLab.token%)
      setup:
         - setCache(@cache.storage)
```

Linking to a custom GitLab installation
------------------------------------

In some cases, you need to link the API to the internal corporate network where GitLab is hosted. This is done by using the `setBaseUrl()` method with a domain path.

The passed parameter can be, for example, the string `'https://gitlab.com/api/v4/'`.
