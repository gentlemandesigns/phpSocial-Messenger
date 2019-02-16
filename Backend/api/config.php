<?php

error_reporting(E_ALL);

$CONF['secret'] = 'gentlemandesigns';
$CONF['token_validity'] = 31; // in days
$CONF['token_issuer'] = 'GentlemanDesigns';
$CONF['token_audience'] = 'phpSocial Messenger';

$CONF['onesignal']['appid'] = 'ONESIGNAL_APIKEY';
$CONF['onesignal']['restkey'] = 'ONESIGNAL_REST_KEY';