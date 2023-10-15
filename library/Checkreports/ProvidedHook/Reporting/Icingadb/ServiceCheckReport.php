<?php

/* originally from Icinga DB Web | (c) 2022 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Checkreports\ProvidedHook\Reporting\Icingadb;

use Icinga\Application\Icinga;
use Icinga\Module\Icingadb\Model\Service;
use Icinga\Module\Icingadb\Redis\VolatileStateResults;
use Icinga\Module\Reporting\ReportRow;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\Form;
use ipl\Stdlib\Filter;

use function ipl\I18n\t;

class ServiceCheckReport extends CheckReport
{

    public function initConfigForm(Form $form)
    {
        parent::initConfigForm($form);
    }

    public function getName()
    {
        $name = t('Service Check Report');
        if (Icinga::app()->getModuleManager()->hasEnabled('monitoring')) {
            $name .= ' (Icinga DB)';
        }

        return $name;
    }

    public function getData(Timerange $timerange, array $config = null)
    {
        return $this->fetchReportData($config);
    }

    protected function createReportRow($config, $row)
    {
        $service_displayname = $row->display_name;
        if ($config['regexfilter'] != "") {
            $service_displayname = preg_replace($config['regexfilter'], '', $service_displayname);
        }
        return (new ReportRow())
            ->setDimensions([$row->host->display_name, $service_displayname, $this->getServiceState($row->state->hard_state), $row->state->last_update->format("d.m.Y H:i")])
            ->setValues([$row->state->output . "\n" . $row->state->long_output]);

    }

    protected function fetchChecks($config, $filter = null)
    {

        $query = Service::on($this->getDb())->with([
            'state',
            'icon_image',
            'host',
            'host.state'
        ]);

        $query
            ->setResultSetClass(VolatileStateResults::class); #to get redis data
        if ($filter !== null) {
            $query->filter(Filter::all(
                Filter::any($filter)
            ));
        }

        $query->resetOrderBy()->orderBy('host.display_name')->orderBy('display_name');

        $this->applyRestrictions($query);

        return $query;

    }

    protected function getServiceState($state)
    {
        if (intval($state) == 0) {
            return "OK";
        }
        if (intval($state) == 1) {
            return "WARNING";
        }
        if (intval($state) == 2) {
            return "CRITICAL";
        }
        if (intval($state) == 3) {
            return "UNKNOWN";
        }
        return "NOT FOUND";
    }

}
