<?php

/* originally from Icinga DB Web | (c) 2022 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Checkreports\ProvidedHook\Reporting\Common;

use ipl\Web\Widget\EmptyState;
use Icinga\Module\Reporting\Hook\ReportHook;
use Icinga\Module\Reporting\ReportData;
use Icinga\Module\Reporting\Timerange;
use ipl\Html\Form;
use ipl\Html\Html;

use function ipl\I18n\t;

/**
 * Base class for host and service SLA reports
 */
abstract class CheckReportHook extends ReportHook
{

    abstract protected function fetchChecks($config, $filter = null);


    protected function createReportData()
    {
        return (new ReportData())
            ->setDimensions([t('Hostname'), t('Service Name'), t('State'), t('Last Execution')]);
    }


    public function initConfigForm(Form $form)
    {
        $form->addElement('text', 'filter', [
            'label' => t('Filter')
        ]);
        $form->addElement('text', 'regexfilter', [
            'label' => 'Regex beautifier',
            'value' => "",
            'description' => "This Regex will be evaluated on the display name for example \"/(TEST/\" will remove TEST from the text."
        ]);

    }


    public function getHtml(Timerange $timerange, array $config = null)
    {
        $data = $this->getData($timerange, $config);

        if (!count($data)) {
            return new EmptyState(t('No data found.'));
        }

        $tableHeaderCells = [];
        $columsCount = 0;
        foreach ($data->getDimensions() as $dimension) {
            $tableHeaderCells[] = Html::tag('th', null, $dimension);
            $columsCount++;
        }
        if ($data->getValues() != null) {
            foreach ($data->getValues() as $value) {
                $tableHeaderCells[] = Html::tag('th', null, $value);
                $columsCount++;
            }

        }

        $tableRows = [];


        foreach ($data->getRows() as $row) {
            $cells = [];

            foreach ($row->getDimensions() as $dimension) {
                $cells[] = Html::tag('td', null, $dimension);
            }

            $tableRows[] = Html::tag('tr', null, $cells);
            $cells = [];
            foreach ($row->getValues() as $value) {
                $cells[] = Html::tag('td', ['class' => "preformatted", 'colspan' => $columsCount], PluginOutput::create($value));
            }
            $tableRows[] = Html::tag('tr', $cells);

        }


        $table = Html::tag(
            'table',
            ['class' => 'service-check-table common-table'],
            [
                Html::tag(
                    'thead',
                    null,
                    Html::tag(
                        'tr',
                        null,
                        $tableHeaderCells
                    )
                ),
                Html::tag('tbody', null, $tableRows)
            ]
        );

        $divWrapper = Html::tag(
            'div',
            ['class' => 'icinga-module module-checkreports'],
            $table
        );


        return $divWrapper;

    }

}
