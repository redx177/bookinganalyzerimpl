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
    'distanceFields' => ['DIWATER','DISKI','DICENTER','DISEA','DILAKE'],
    'idField' => 'id'
);