<?php

/* originally from Icinga DB Web | (c) 2022 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Checkreports\ProvidedHook\Reporting\Icingadb;

use Icinga\Module\Checkreports\ProvidedHook\Reporting\Common\CheckReportHook;
use Icinga\Module\Icingadb\Common\Auth;
use Icinga\Module\Icingadb\Common\Database;
use Icinga\Module\Reporting\ReportRow;
use ipl\Web\Filter\QueryString;

use function ipl\I18n\t;

/**
 * Base class for host and service SLA reports
 */
abstract class CheckReport extends CheckReportHook
{
    use Auth;
    use Database;


    /**
     * Create and return a {@link ReportRow}
     *
     * @param mixed $row Data for the row
     *
     * @return ReportRow|null Row with the dimensions and values for the specific report set according to the data
     *                        expected in {@link createRepportData()} or null for no data
     */
    abstract protected function createReportRow($config, $row);

    abstract protected function fetchChecks($config, $filter = null);

    protected function fetchReportData(array $config = null)
    {
        $rd = $this->createReportData();
        $rows = [];

        $filter = trim((string)$config['filter']) ?: '*';
        $filter = $filter !== '*' ? QueryString::parse($filter) : null;

        foreach ($this->fetchChecks($config, $filter) as $row) {
            $rows[] = $this->createReportRow($config, $row);
        }


        $rd->setRows($rows);

        return $rd;
    }


}
