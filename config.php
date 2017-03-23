<?php
$configContent = array(
    'dataSource' => './rapidminerdata.csv',
    'pageSize' => 10,
    'paginationWindow' => 3,
    'booleanFields' => ['CPOOL','CBBQ','CSAUNA','CJACUZZI','CWASHMACHI','CPARKING','CDISHWASHE','CFIREPLACE','SCTV','SCBALCONY','SCBALCONY','SCNOSMOKE','INTERNET'],
    'integerFields' => ['QUAL','ROOMS','BEDROOMS'],
    'floatFields' => ['longitude', 'latitude'],
    'stringFields' => ['NREF'],
    'priceFields' => ['PRICES'],
    'distanceFields' => ['DIWATER','DISKI','DICENTER','DISEA','DILAKE', 'DIPUBT'],
    'idField' => 'id',
    'fieldNameMapping' => [
        'ID' => 'Id',
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
        'CPOOL' => 'Pool',
        'CBBQ' => 'BBQ',
        'CSAUNA' => 'Sauna',
        'CJACUZZI' => 'Jacuzzi',
        'CWASHMACHI' => 'Washmachine',
        'CPARKING' => 'Parking',
        'CDISHWASHE' => 'Dishwasher',
        'CFIREPLACE' => 'Fireplace',
        'SCTV' => 'TV',
        'SCBALCONY' => 'Balcony',
        'SCNOSMOKE' => 'Non Smoking',
        'INTERNET' => 'Internet',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
    ]
);