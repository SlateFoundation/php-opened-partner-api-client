OpenEd\SignedServerRequest
===============






* Class name: SignedServerRequest
* Namespace: OpenEd





Properties
----------


### $client_id

    private mixed $client_id





* Visibility: **private**


### $client_secret

    private mixed $client_secret





* Visibility: **private**


### $nces_id

    private mixed $nces_id





* Visibility: **private**


Methods
-------


### __construct

    mixed OpenEd\SignedServerRequest::__construct($client_id, $client_secret, $nces_id)





* Visibility: **public**


#### Arguments
* $client_id **mixed**
* $client_secret **mixed**
* $nces_id **mixed**



### base64UrlEncode

    mixed OpenEd\SignedServerRequest::base64UrlEncode($input)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $input **mixed**



### generateToken

    mixed OpenEd\SignedServerRequest::generateToken($username)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $username **mixed**



### generateSignedRequest

    mixed OpenEd\SignedServerRequest::generateSignedRequest($username, $params, $token)





* Visibility: **public**


#### Arguments
* $username **mixed**
* $params **mixed**
* $token **mixed**



### setNcesId

    mixed OpenEd\SignedServerRequest::setNcesId($nces_id)





* Visibility: **public**


#### Arguments
* $nces_id **mixed**



### getNcesId

    mixed OpenEd\SignedServerRequest::getNcesId()





* Visibility: **public**



