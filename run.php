<?php

// Icinga IDO Reports | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Checkreports {

    use Icinga\Application\Icinga;

    /** @var \Icinga\Application\Modules\Module $this */

    $this->provideHook('reporting/Report', 'Icinga\\Module\\Checkreports\\ProvidedHook\\Reporting\\Monitoring\\ServiceCheckReport');
    $this->provideHook('reporting/Report', 'Icinga\\Module\\Checkreports\\ProvidedHook\\Reporting\\Icingadb\\ServiceCheckReport');
}
