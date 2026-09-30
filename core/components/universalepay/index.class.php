<?php
require_once dirname(__FILE__) . '/bootstrap.php';

class IndexManagerController extends \MODX\Revolution\modExtraManagerController
{
    public static function getDefaultController()
    {
        return 'home';
    }

    public function initialize()
    {
        parent::initialize();
        $this->config = [
            'corePath' => dirname(__FILE__) . '/',
            'assetsUrl' => $this->modx->getOption('assets_url') . 'components/universalepay/',
            'connectorUrl' => $this->modx->getOption('assets_url') . 'components/universalepay/connector.php',
        ];
    }

    public function getLanguageTopics()
    {
        return [];
    }
}
