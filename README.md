GitLab API for Nette
====================

Balík slouží jako transportní vrstva mezi konkrétní aplikací a GitLabem.

Pomocí tohoto balíku můžete jednoduchým způsobem pokládat dotazy do GitLabu, detekovat chybové hlášení v Tracy baru a sledovat vytížení požadavků.

Požadavky typu `GET` se automaticky cachují na `12 hodin`, pokud není řečeno jinak.

Požadavky typu `POST`, `PUT`, `DELETE` a další změnové akce se necachují vůbec a vždy přenášíme veškerá data znovu.

![GitLab Tracy panel](/src/gitlab-api-tracy.png "GitLab Tracy panel")

Instalace
---------

Použijte příkaz Composeru:

```shell
composer require baraja-core/gitlab-api
```

Dále je potřeba nastavit konfiguraci služby pro Nette v NEON souboru.

Výchozí minimální konfigurace:

```yaml
services:
   gitLabAPI:
      factory: Baraja\GitLabApi\GitLabApi(%gitLab.token%)

parameters:
   gitLab:
      token: 123-abcDEFghiJKL-789

tracy:
   bar:
      - Baraja\GitLabApi\GitLabApiPanel
```

API token musíte vždy změnit pro Váš uživatelský účet!

Konfigurace
-----------

Do sekce `parameters` je potřeba vložit defaultní API token pro spojení s GitLabem:

Příklad:

```neon
parameters:
   gitLab:
      token: 123-abcDEFghiJKL-789
```

Volitelně lze nastavit použití Nette Cache:

```yaml
services:
   gitLabAPI:
      factory: Baraja\GitLabApi\GitLabApi(%gitLab.token%)
      setup:
         - setCache(@cache.storage)
```

Propojení s vlastní GitLab instalací
------------------------------------

V některých případech je potřeba propojit API na vnitřní firemní síť, kde je GitLab hostován. K tomu slouží metoda `setBaseUrl()` s cestou k doméně.

Předaným parametrem může být například řetězec `'https://gitlab.com/api/v4/'`.