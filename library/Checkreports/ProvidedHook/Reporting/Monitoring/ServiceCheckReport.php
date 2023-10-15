<?php

// originally from Icinga IDO Reports | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Checkreports\ProvidedHook\Reporting\Monitoring;

use Icinga\Module\Reporting\ReportRow;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\Form;
use function ipl\I18n\t;

class ServiceCheckReport extends CheckReport
{
    public function getName()
    {
        $name = t('Service Check Report');
        return $name;
    }

    protected function createReportRow($config, $row)
    {
        $service_displayname = $row->service_display_name;
        if ($config['regexfilter'] != "") {
            $service_displayname = preg_replace($config['regexfilter'], '', $service_displayname);
        }

        return (new ReportRow())
            ->setDimensions([$row->host_display_name, $service_displayname, $this->getServiceState($row->service_hard_state), date("d.m.Y H:i", $row->service_last_check)])
            ->setValues([$row->service_output . "\n" . $row->service_long_output]);

    }

    public function initConfigForm(Form $form)
    {
        parent::initConfigForm($form);
    }


    protected function fetchChecks($config, $filter = null)
    {
        $sla = $this
            ->getBackend()
            ->select()
            ->from('servicestatus', ['host_display_name', 'service_display_name', 'service_hard_state', 'service_long_output', 'service_output', 'service_last_check'])
            ->order('host_display_name');

        $this->applyFilterAndRestrictions($config['filter'] ?: '*', $sla);

        /** @var \Zend_Db_Select $select */
        $select = $sla->getQuery()->getSelectQuery();

        $columns = $sla->getQuery()->getColumns();

        $select->columns($columns);

        return $this->getBackend()->getResource()->getDbAdapter()->query($select);
    }


    public function getData(Timerange $timerange, array $config = null)
    {
        return $this->fetchReportData($config);
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
