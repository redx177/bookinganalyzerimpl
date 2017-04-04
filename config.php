<?php
$configContent = array(
    'dataSource' => './rapidminerdata.csv',
    'pageSize' => 10,
    'paginationWindow' => 3,
    'booleanFields' => ['CAIRCOND','CPOOL','CBBQ','CSAUNA','CJACUZZI','CWASHMACHI','CPARKING','CDISHWASHE','CFIREPLACE','SCTV','SCBALCONY','PETS','SCNOSMOKE','INTERNET'],
    'integerFields' => ['QUAL','ROOMS','BEDROOMS'],
    'floatFields' => ['longitude', 'latitude'],
    'stringFields' => ['NREF'],
    'priceFields' => ['PRICES'],
    'distanceFields' => ['DIWATER','DISKI','DICENTER','DISEA','DILAKE', 'DIPUBT'],
    'idField' => 'id',
    'atLeastFilterFields' => ['ROOMS','BEDROOMS'],
    'fieldNameMapping' => [
        'ID' => 'Id',
        'NREF' => 'NREF',
        'PRICES' => 'Price',
        'QUAL' => 'Stars',
        'ROOMS' => 'Rooms',
        'BEDROOMS' => 'Bedrooms',
        'DIWATER' => 'Close to water',
        'DISKI' => 'Close to ski',
        'DICENTER' => 'Close to center',
        'DISEA' => 'Close to sea',
        'DILAKE' => 'Close to lake',
        'DIPUBT' => 'Close to public transport',
        'CAIRCOND' => 'Aircondition',
        'CPOOL' => 'Pool',
        'CBBQ' => 'BBQ',
        'CSAUNA' => 'Sauna',
        'CJACUZZI' => 'Jacuzzi',
        'CWASHMACHI' => 'Washmachine',
        'CPARKING' => 'Parking',
        'CDISHWASHE' => 'Dishwasher',
        'CFIREPLACE' => 'Fireplace',
        'SCTV' => 'TV',
        'PETS' => 'Pets',
        'SCBALCONY' => 'Balcony',
        'SCNOSMOKE' => 'Non Smoking',
        'INTERNET' => 'Internet',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
    ],
    'filterButtonTitle' => 'Apply Fiters',
    'runButtonTitle' => 'Run analysis',
    'aprioriMinSup' => 2000,
    'bookingsCountCap' => 40000, // Debugging only
    'ignoreFields' => ['SCTV'],
    'aprioriServiceOutput' => '/Services/Apriori/status.json',
    'aprioriServicePidFile' => 'pid.txt',
    'aprioriOutputInterval' => 10, // Seconds
);