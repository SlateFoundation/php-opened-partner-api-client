OpenEd\ApiClient
===============

Class ApiClient




* Class name: ApiClient
* Namespace: OpenEd



Constants
----------


### BASE_URL

    const BASE_URL = 'https://api.opened.io'





### PARTNER_BASE_URL

    const PARTNER_BASE_URL = 'https://partner.opened.com'





### TOKEN_PATH

    const TOKEN_PATH = '/oauth/token'





Properties
----------


### $client_id

    private mixed $client_id





* Visibility: **private**


### $client_secret

    private mixed $client_secret





* Visibility: **private**


### $username

    private mixed $username





* Visibility: **private**


### $password

    private mixed $password





* Visibility: **private**


### $curl

    private mixed $curl





* Visibility: **private**


### $access_token

    private mixed $access_token





* Visibility: **private**


### $trigger_errors

    private mixed $trigger_errors = true





* Visibility: **private**


### $verbose

    private mixed $verbose = true





* Visibility: **private**


Methods
-------


### __construct

    mixed OpenEd\ApiClient::__construct($client_id, $client_secret, $username, $password, $access_token)





* Visibility: **public**


#### Arguments
* $client_id **mixed**
* $client_secret **mixed**
* $username **mixed**
* $password **mixed**
* $access_token **mixed**



### generateVerboseError

    mixed OpenEd\ApiClient::generateVerboseError($method, $url, $headers, $fields, $response, $response_code)





* Visibility: **private**


#### Arguments
* $method **mixed**
* $url **mixed**
* $headers **mixed**
* $fields **mixed**
* $response **mixed**
* $response_code **mixed**



### getToken

    mixed OpenEd\ApiClient::getToken($username, $password, $use_token)





* Visibility: **public**


#### Arguments
* $username **mixed**
* $password **mixed**
* $use_token **mixed**



### getAccessToken

    mixed OpenEd\ApiClient::getAccessToken($username, $password, $use_token)





* Visibility: **public**


#### Arguments
* $username **mixed**
* $password **mixed**
* $use_token **mixed**



### useAccessToken

    mixed OpenEd\ApiClient::useAccessToken($access_token)





* Visibility: **public**


#### Arguments
* $access_token **mixed**



### request

    mixed OpenEd\ApiClient::request($method, $path, $params, $headers, $fields, $returnError)





* Visibility: **public**


#### Arguments
* $method **mixed**
* $path **mixed**
* $params **mixed**
* $headers **mixed**
* $fields **mixed**
* $returnError **mixed**



### get

    mixed OpenEd\ApiClient::get($path, $params, $headers, $fields, $success_code)





* Visibility: **public**


#### Arguments
* $path **mixed**
* $params **mixed**
* $headers **mixed**
* $fields **mixed**
* $success_code **mixed**



### put

    mixed OpenEd\ApiClient::put($path, $params, $headers, $fields, $success_code)





* Visibility: **public**


#### Arguments
* $path **mixed**
* $params **mixed**
* $headers **mixed**
* $fields **mixed**
* $success_code **mixed**



### post

    mixed OpenEd\ApiClient::post($path, $params, $headers, $fields, $success_code, $return_error)





* Visibility: **public**


#### Arguments
* $path **mixed**
* $params **mixed**
* $headers **mixed**
* $fields **mixed**
* $success_code **mixed**
* $return_error **mixed**



### delete

    mixed OpenEd\ApiClient::delete($path, $params, $headers, $fields, $success_code, $return_error)





* Visibility: **public**


#### Arguments
* $path **mixed**
* $params **mixed**
* $headers **mixed**
* $fields **mixed**
* $success_code **mixed**
* $return_error **mixed**



### postRaw

    mixed OpenEd\ApiClient::postRaw($body)





* Visibility: **public**


#### Arguments
* $body **mixed**



### getStandardsGroups

    mixed OpenEd\ApiClient::getStandardsGroups()





* Visibility: **public**




### getResources

    mixed OpenEd\ApiClient::getResources(array $params)





* Visibility: **public**


#### Arguments
* $params **array**



### getCategories

    mixed OpenEd\ApiClient::getCategories(array $params)





* Visibility: **public**


#### Arguments
* $params **array**



### getAreas

    mixed OpenEd\ApiClient::getAreas(array $params)





* Visibility: **public**


#### Arguments
* $params **array**



### getSubjects

    mixed OpenEd\ApiClient::getSubjects(array $params)





* Visibility: **public**


#### Arguments
* $params **array**



### getResource

    mixed OpenEd\ApiClient::getResource($id)





* Visibility: **public**


#### Arguments
* $id **mixed**



### getGradeGroups

    mixed OpenEd\ApiClient::getGradeGroups(null $standards_group)





* Visibility: **public**


#### Arguments
* $standards_group **null**



### getClasses

    mixed OpenEd\ApiClient::getClasses(array $ids)





* Visibility: **public**


#### Arguments
* $ids **array**



### getClass

    mixed OpenEd\ApiClient::getClass($id)





* Visibility: **public**


#### Arguments
* $id **mixed**



### validateGradeRange

    mixed OpenEd\ApiClient::validateGradeRange($str)





* Visibility: **private**
* This method is **static**.


#### Arguments
* $str **mixed**



### createClass

    mixed OpenEd\ApiClient::createClass($title, null $grades_range)





* Visibility: **public**


#### Arguments
* $title **mixed**
* $grades_range **null**



### updateClass

    mixed OpenEd\ApiClient::updateClass($class_id, $title, null $grades_range)





* Visibility: **public**


#### Arguments
* $class_id **mixed**
* $title **mixed**
* $grades_range **null**



### deleteClass

    mixed OpenEd\ApiClient::deleteClass($class_id)





* Visibility: **public**


#### Arguments
* $class_id **mixed**



### createStudent

    mixed OpenEd\ApiClient::createStudent($student)





* Visibility: **public**


#### Arguments
* $student **mixed**



### updateStudent

    mixed OpenEd\ApiClient::updateStudent($student_id, $student)





* Visibility: **public**


#### Arguments
* $student_id **mixed**
* $student **mixed**



### getStudent

    mixed OpenEd\ApiClient::getStudent($student_id)





* Visibility: **public**


#### Arguments
* $student_id **mixed**



### getStudents

    mixed OpenEd\ApiClient::getStudents()





* Visibility: **public**




### deleteStudent

    mixed OpenEd\ApiClient::deleteStudent($student_id)





* Visibility: **public**


#### Arguments
* $student_id **mixed**



### addStudentsToClass

    mixed OpenEd\ApiClient::addStudentsToClass($student_ids, $class_id)





* Visibility: **public**


#### Arguments
* $student_ids **mixed**
* $class_id **mixed**



### addStudentToClass

    mixed OpenEd\ApiClient::addStudentToClass($student_id, $class_id)





* Visibility: **public**


#### Arguments
* $student_id **mixed**
* $class_id **mixed**



### removeStudentsFromClass

    mixed OpenEd\ApiClient::removeStudentsFromClass($student_ids, $class_id)





* Visibility: **public**


#### Arguments
* $student_ids **mixed**
* $class_id **mixed**



### removeStudentFromClass

    mixed OpenEd\ApiClient::removeStudentFromClass($student_id, $class_id)





* Visibility: **public**


#### Arguments
* $student_id **mixed**
* $class_id **mixed**


