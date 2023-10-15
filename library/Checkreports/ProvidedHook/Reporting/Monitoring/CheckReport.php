<?php

// originally from Icinga IDO Reports | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Checkreports\ProvidedHook\Reporting\Monitoring;

use Icinga\Application\Icinga;
use Icinga\Data\Filter\Filter;
use Icinga\Data\Filterable;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\QueryException;
use Icinga\Module\Checkreports\ProvidedHook\Reporting\Common\CheckReportHook;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Reporting\ReportRow;


/**
 * @TODO(el): Respect restrictions from monitoring module
 */
abstract class CheckReport extends CheckReportHook
{


    protected function createReportRow($config, $row)
    {
        $service_displayname = $row->display_name;
        if ($config['regexfilter'] != "") {
            $service_displayname = preg_replace($config['regexfilter'], '', $service_displayname);
        }

        return (new ReportRow())
            ->setDimensions([$row->host->display_name, $service_displayname, $this->getServiceState($row->state->hard_state)])
            ->setValues([$row->state->output . "\n" . $row->state->long_output]);

    }

    abstract protected function fetchChecks($config, $filter = null);

    protected function fetchReportData(array $config = null)
    {
        $rd = $this->createReportData();
        $rows = [];

        $filter = trim((string)$config['filter']) ?: '';

        foreach ($this->fetchChecks($config, $filter) as $row) {
            $rows[] = $this->createReportRow($config, $row);
        }


        $rd->setRows($rows);

        return $rd;
    }

    protected function applyFilterAndRestrictions($filter, Filterable $filterable)
    {
        $filters = Filter::matchAll();
        $filters->setAllowedFilterColumns(array(
            'host_name',
            'hostgroup_name',
            'instance_name',
            'service_description',
            'servicegroup_name',
            function ($c) {
                return \preg_match('/^_(?:host|service)_/i', $c);
            }
        ));

        try {
            if ($filter !== '*') {
                $filters->addFilter(Filter::fromQueryString($filter));
            }

            foreach ($this->yieldMonitoringRestrictions() as $filter) {
                $filters->addFilter($filter);
            }
        } catch (QueryException $e) {
            throw new ConfigurationError(
                'Cannot apply filter. You can only use the following columns: %s',
                implode(', ', array(
                    'instance_name',
                    'host_name',
                    'hostgroup_name',
                    'service_description',
                    'servicegroup_name',
                    '_(host|service)_<customvar-name>'
                )),
                $e
            );
        }

        $filterable->applyFilter($filters);
    }

    protected function getBackend()
    {
        MonitoringBackend::clearInstances();

        return MonitoringBackend::instance();
    }

    protected function getRestrictions($name)
    {
        $app = Icinga::app();
        if (!$app->isCli()) {
            $result = $app->getRequest()->getUser()->getRestrictions($name);
        } else {
            $result = [];
        }

        return $result;
    }


    protected function yieldMonitoringRestrictions()
    {
        foreach ($this->getRestrictions('monitoring/filter/objects') as $restriction) {
            if ($restriction !== '*') {
                yield Filter::fromQueryString($restriction);
            }
        }
    }
}
