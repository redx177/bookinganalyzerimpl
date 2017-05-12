<?php

class SettingsController implements Controller
{
    private $pageSize;
    private $gamma;
    private $minSup;
    private $radius;
    private $minPoints;
    private $editableConfigFile;
    private $bookingsCountCap;
    private $fieldNames;
    private $ignoreFields;
    private $fieldNameMapping;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * ExploreController constructor.
     * @param Twig_Environment $twig Twig environment for loading templates.
     * @param ConfigProvider $config Configuration provider.
     */
    public function __construct(
        Twig_Environment $twig,
        ConfigProvider $config)
    {
        $this->twig = $twig;

        $this->pageSize = $config->get('pageSize');
        $this->gamma = $config->get('gamma');
        $this->bookingsCountCap = $config->get('bookingsCountCap');
        $this->fieldNames = [
            'booleanFields' => $config->get('booleanFields'),
            'integerFields' => $config->get('integerFields'),
            'floatFields' => $config->get('floatFields'),
            'stringFields' => $config->get('stringFields'),
            'priceFields' => $config->get('priceFields'),
            'distanceFields' => $config->get('distanceFields'),
        ];
        $this->ignoreFields = $config->get('ignoreFields');
        $this->fieldNameMapping = $config->get('fieldNameMapping');
        $rootDir = $config->get('rootDir');
        $this->editableConfigFile = $rootDir . '/' . $config->get('editableConfigFile');


        $apriori = $config->get('apriori');
        $this->minSup = $apriori['minSup'];

        $dbscan = $config->get('dbscan');
        $this->radius = $dbscan['radius'];
        $this->minPoints = $dbscan['minPoints'];
    }


    /**
     * Returns the code to render.
     * @return string Code to render.
     */
    public function render()
    {
        $saved = false;
        if (array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'save') {
            $data = $_REQUEST;
            unset($data['settings']);
            unset($data['action']);
            file_put_contents($this->editableConfigFile, json_encode($data));
        }

        $template = $this->twig->load('settings.twig');
        return $template->render([
            'view' => 'settings',
            'bookingsCountCap' => $this->bookingsCountCap,
            'pageSize' => $this->pageSize,
            'gamma' => $this->gamma,
            'minSup' => $this->minSup,
            'radius' => $this->radius,
            'minPoints' => $this->minPoints,
            'fieldNames' => $this->fieldNames,
            'ignoreFields' => $this->ignoreFields,
            'fieldNameMapping' => $this->fieldNameMapping,
            'saved' => $saved,
            'buttonConfig' => new ButtonConfig('Save', 'save'),
        ]);
    }
}