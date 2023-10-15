# Installation <a id="module-checkreports-installation"></a>

## Requirements <a id="module-checkreports-installation-requirements"></a>

* Icinga Web 2 (&gt;= 2.10.3)
* PHP (&gt;= 7.3)

The Icinga Web 2 `monitoring` or `icingadb` module needs to be configured and enabled.

## Installation from .tar.gz <a id="module-checkreports-installation-manual"></a>

Download the latest version and extract it to a folder named `checkreports`
in one of your Icinga Web 2 module path directories.

## Enable the newly installed module <a id="module-checkreports-installation-enable"></a>

Enable the `checkreports` module either on the CLI by running

```sh
icingacli module enable checkreports
```

Or go to your Icinga Web 2 frontend, choose `Configuration` -&gt; `Modules`, chose the `checkreports` module
and `enable` it.

It might afterwards be necessary to refresh your web browser to be sure that newly provided styling is loaded.