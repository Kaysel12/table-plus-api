<?php
// config/soap.php

return [
    'task_service_wsdl' => env('SOAP_TASK_SERVICE_WSDL', 'http://localhost:8080/soap/tasks?wsdl'),
    'timeout' => env('SOAP_TIMEOUT', 30),
    'cache_wsdl' => env('SOAP_CACHE_WSDL', WSDL_CACHE_NONE),
];