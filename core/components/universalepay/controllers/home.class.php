<?php
class UniversalEpayHomeManagerController extends \MODX\Revolution\modExtraManagerController
{
    public function getPageTitle()
    {
        return 'UniversalEpay — Halyk ePay';
    }

    public function getTemplateFile()
    {
        return dirname(__DIR__) . '/elements/templates/home.tpl';
    }

    public function loadCustomCssJs()
    {
        $this->addLastJavascript(
            $this->modx->getOption('assets_url') . 'components/universalepay/js/mgr/home.js'
        );
        $this->addHtml('<script>window.UniversalEpayConfig=' . $this->modx->toJSON([
            'connectorUrl' => $this->modx->getOption('assets_url') . 'components/universalepay/connector.php',
        ]) . ';</script>');
    }

    public function checkPermissions()
    {
        return $this->modx->hasPermission('save_system_settings');
    }
}
