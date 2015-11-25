<?php

namespace OpenEd;


/**
 * Class ApiClient
 * @package OpenEd
 */
class ApiClient
{
    private $client_id;
    private $client_secret;
    private $username;
    private $password;
    private $curl;
    private $access_token;
    private $trigger_errors = true;
    private $verbose = true;

    const BASE_URL = 'https://api.opened.io';
    const PARTNER_BASE_URL = 'https://partner.opened.com';
    const TOKEN_PATH = '/oauth/token';

    public function __construct($client_id, $client_secret, $username = null, $password = null, $access_token = null)
    {
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
        $this->username = $username;
        $this->password = $password;
        $this->access_token = $access_token;

        $this->curl = curl_init();

        curl_setopt_array($this->curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        ]);
    }

    private function generateVerboseError($method, $url, $headers, $fields, $response, $response_code)
    {
        $curl_args = [];

        if ($method !== 'GET') {
            $curl_args[] = '-X ' . $method;
        }

        foreach ($headers as $header) {
            $curl_args[] = '-H "' . $header . '"';
        }

        if (count($fields) > 0) {
            $curl_args[] = "-d '" . json_encode($fields, JSON_UNESCAPED_SLASHES) . "'";
        }

        $curl = 'curl ' . implode(' ', $curl_args) . ' ' . $url;

        $error = "[HTTP $response_code] $method $url\n"
            . "HEADERS:\n" . json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
            . "BODY\n" . json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
            . "RESPONSE:\n" . $response . "\n"
            . "CURL:\n$curl\n";

        return $error;
    }

    public function getToken($username = null, $password = null, $use_token = true)
    {
        $username = $username ?: $this->username;
        $password = $password ?: $this->password;

        if (!($username && $password)) {
            throw new \InvalidArgumentException('username and password are required parameters if they were not set in ApiClient');
        }

        list($error, $response, $response_code) = $this->request('POST', self::TOKEN_PATH, [
            'username' => $username,
            'password' => $password,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'password'
        ]);

        if ($error || $response_code != 200) {
            throw new \ErrorException("[OpenEd] Unable to get access token: $error");
        }

        if ($use_token) {
            $this->access_token = $response['access_token'];
        }

        return $response;
    }

    public function getAccessToken($username = null, $password = null, $use_token = true)
    {

        return $this->getToken($username, $password, $use_token)['access_token'];
    }

    public function useAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    public function request($method, $path = '/', $params = [], $headers = [], $fields = [], $returnError = false)
    {
        $error = null;

        $url = ((strpos($path, '/teachers') === 0) ? self::PARTNER_BASE_URL : self::BASE_URL) . $path;

        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $url_params);

        // Parameters passed as $params are merged with parameters in the URL; $params takes precedence
        if (count($url_params) > 0) {
            $params = array_merge($url_params, $params);
        }

        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        $implicit_headers = [];

        if ($this->access_token) {
            $implicit_headers[] = 'Authorization: Bearer ' . $this->access_token;
        }

        $has_body = count($fields) > 0;

        if ($has_body) {
            $implicit_headers[] = 'Content-Type: application/json';
            print json_encode($fields);
        }

        $headers = array_merge($headers, $implicit_headers);

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => $method !== 'GET',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $has_body ? json_encode($fields) : null
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($response_code >= 400) {
            $error = 'HTTP $response_code';
            if ($this->trigger_errors) {
                if ($this->verbose) {
                    trigger_error($this->generateVerboseError($method, $url, $headers, $fields, $response, $response_code), $returnError ? E_USER_NOTICE : E_USER_ERROR);
                } else {
                    trigger_error("[OpenEd] $method $url returned $error", $returnError ? E_USER_NOTICE : E_USER_ERROR);
                }
            }
        }

        if ($curl_error = curl_errno($this->curl)) {
            $error = new \ErrorException(curl_error($this->curl));
        }

        $response_is_json = strpos(curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE), 'json') !== false;

        // returns error, response, response_code
        return [
            $error,
            $response_is_json ? json_decode($response, true) : $response,
            $response_code
        ];
    }


    public function get($path = '/', $params = [], $headers = [], $fields = [], $success_code = 200)
    {
        list($error, $response, $response_code) = $this->request('GET', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] GET $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] GET $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function put($path = '/', $params = [], $headers = [], $fields = [], $success_code = 201)
    {
        list($error, $response, $response_code) = $this->request('PUT', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] PUT $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] PUT $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function post($path = '/', $params = [], $headers = [], $fields = [], $success_code = 200, $return_error = false)
    {
        list($error, $response, $response_code) = $this->request('POST', $path, $params, $headers, $fields, $return_error);

        if ($error) {
            trigger_error("[OpenEd] POST $path: $error", $return_error ? E_USER_NOTICE : E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] POST $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function delete($path = '/', $params = [], $headers = [], $fields = [], $success_code = 204, $return_error = false)
    {
        list($error, $response, $response_code) = $this->request('DELETE', $path, $params, $headers, $fields, $return_error);

        if ($error) {
            trigger_error("[OpenEd] DELETE $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] DELETE $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function postRaw($body = '')
    {
        $url = self::BASE_URL . '/oauth/silent_login';

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: text/plain', 'Content-Length: ' . strlen($body)]
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);


        print "BODY:\n\n\n$body\n\n\n";

        if ($response_code >= 400) {
            throw new \ErrorException("POST $url failed with HTTP $response_code: $response");
        }

        return json_decode($response, true);
    }


    /**
     * @return mixed
     */
    public function getStandardsGroups()
    {
        return $this->get('/standard_groups.json')['standard_groups'];
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function getResources($params = [])
    {
        $valid_parameters = [
            'descriptive', 'limit', 'offset', 'standard_group', 'category', 'standard', 'area', 'subject', 'grade',
            'grade_group', 'contribution_name', 'resource_types'
        ];

        $invalid_parameters = array_diff(array_keys($params), $valid_parameters);

        if (count($invalid_parameters) > 0) {
            throw new \InvalidArgumentException('Invalid parameter(s) passed: ' . implode(', ', $invalid_parameters) .
                '; valid parameters are: ' . implode(', ', $valid_parameters));
        }

        return $this->get('/resources.json', $params)['resources'];
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function getCategories($params = [])
    {
        $valid_parameters = [ 'standard_group', 'grade_group' ];

        $invalid_parameters = array_diff(array_keys($params), $valid_parameters);

        if (count($invalid_parameters) > 0) {
            throw new \InvalidArgumentException('Invalid parameter(s) passed: ' . implode(', ', $invalid_parameters) .
                '; valid parameters are: ' . implode(', ', $valid_parameters));
        }

        return $this->get('/categories.json', $params)['categories'];
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function getAreas($params = [])
    {
        $valid_parameters = [ 'standard_group', 'grade_group' ];

        $invalid_parameters = array_diff(array_keys($params), $valid_parameters);

        if (count($invalid_parameters) > 0) {
            throw new \InvalidArgumentException('Invalid parameter(s) passed: ' . implode(', ', $invalid_parameters) .
                '; valid parameters are: ' . implode(', ', $valid_parameters));
        }

        return $this->get('/areas.json', $params)['areas'];
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function getSubjects($params = [])
    {
        $valid_parameters = [ 'area' ];

        $invalid_parameters = array_diff(array_keys($params), $valid_parameters);

        if (count($invalid_parameters) > 0) {
            throw new \InvalidArgumentException('Invalid parameter(s) passed: ' . implode(', ', $invalid_parameters) .
                '; valid parameters are: ' . implode(', ', $valid_parameters));
        }

        return $this->get('/subjects.json', $params)['subjects'];
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getResource($id)
    {
        return $this->get("/resources/$id.json")['resource'];
    }


    /**
     * @param null $standards_group
     * @return mixed
     */
    public function getGradeGroups($standards_group = null)
    {
        return $this->get('/grade_groups.json', $standards_group ? ['standards_group' => $standards_group] : [])['grade_groups'];
    }


    /**
     * @param array $ids
     * @return mixed
     */
    public function getClasses($ids = [])
    {
        $params = [];

        if (count($ids)) {
            $params['ids'] = $ids;
        }

        return $this->get('/teachers/classes', $params);
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getClass($id)
    {
        return $this->get('/teachers/classes/' . $id);
    }


    private static function validateGradeRange($str)
    {
        return preg_match("/^\\d\\-?\\d?$/um", $str);
    }


    /**
     * @param $title
     * @param null $grades_range
     * @return mixed
     * @throws \ErrorException
     */
    public function createClass($title, $grades_range = null)
    {
        $fields = [];

        if (is_string($title)) {
            $fields['title'] = $title;
        } else {
            throw new \ErrorException('Title is required.');
        }

        if ($grades_range) {
            if (static::validateGradeRange($grades_range)) {
                $fields['grades_range'] = (string)$grades_range;
            } else {
                throw new \InvalidArgumentException('Invalid grade range, valid formats are: 5, 5-6');
            }
        }

        return $this->post('/teachers/classes', [], [], ['class' => $fields], '201');
    }


    /**
     * @param $class_id
     * @param $title
     * @param null $grades_range
     * @throws \ErrorException
     */
    public function updateClass($class_id, $title, $grades_range = null)
    {
        $fields = [];

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        if ($grades_range) {
            if (static::validateGradeRange($grades_range)) {
                $params['grades_range'] = $grades_range;
            } else {
                throw new \InvalidArgumentException('Invalid grade range, valid formats are: 5, 5-6');
            }
        }

        if ($title) {
            $fields['title'] = $title;
        }

        // Do not issue a no-op to the server
        if (count($fields) === 0) {
            return;
        }

        return $this->put('/teachers/classes/' . $class_id, [], [], ['class' => $fields]);
    }


    /**
     * @param $class_id
     * @return mixed
     * @throws \ErrorException
     */
    public function deleteClass($class_id)
    {
        if (!$class_id) {
            throw new \ErrorException('Class id is required.');
        }

        return $this->delete('/teachers/classes/' . $class_id);
    }


    /**
     * @param $student
     * @return mixed
     */
    public function createStudent($student)
    {
        $required_fields = ['first_name', 'last_name', 'username', 'password'];
        $optional_fields = ['class_ids'];
        $student_keys = array_keys($student);
        $missing_fields = array_diff($required_fields, $student_keys);
        $invalid_fields = array_diff($student_keys, $required_fields, $optional_fields);

        if (count($invalid_fields) > 0) {
            throw new \InvalidArgumentException('Invalid fields(s) passed for student: ' . implode(', ', $invalid_fields) .
                '; valid fields are: ' . implode(', ', $required_fields) . implode(', ', $optional_fields));
        }

        if (count($missing_fields) > 0) {
            throw new \InvalidArgumentException('Missing required fields(s) for student: ' . implode(', ', $missing_fields) .
                '; required fields: ' . implode(', ', $required_fields) . ' optional fields: ' . implode(', ', $optional_fields));
        }

        return $this->post('/teachers/students', [], [], ['student' => $student], '201', true);
    }


    /**
     * @param $student_id
     * @param $student
     * @return mixed
     * @throws \ErrorException
     */
    public function updateStudent($student_id, $student)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        $required_fields = ['first_name', 'last_name', 'username', 'password'];
        $optional_fields = ['class_ids'];
        $student_keys = array_keys($student);
        $invalid_fields = array_diff($student_keys, $required_fields, $optional_fields);

        if (count($invalid_fields) > 0) {
            throw new \InvalidArgumentException('Invalid fields(s) passed for student: ' . implode(', ', $invalid_fields) .
                '; valid fields are: ' . implode(', ', $required_fields) . implode(', ', $optional_fields));
        }

        return $this->put('/teachers/students/' . $student_id, [], [], ['student' => $student]);
    }


    /**
     * @param $student_id
     * @return mixed
     * @throws \ErrorException
     */
    public function getStudent($student_id)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        return $this->get('/teachers/students/' . $student_id);
    }


    /**
     * @return mixed
     */
    public function getStudents()
    {
        return $this->get('/teachers/students')['students'];
    }


    /**
     * @param $student_id
     * @return mixed
     * @throws \ErrorException
     */
    public function deleteStudent($student_id)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        return $this->delete('/teachers/students/' . $student_id, [], [], [], '204', true);
    }


    /**
     * @param $student_ids
     * @param $class_id
     * @return mixed
     * @throws \ErrorException
     */
    public function addStudentsToClass($student_ids, $class_id)
    {
        $fields = [];

        if (is_array($student_ids)) {
            $fields['student_ids'] = $student_ids;
        } else if (is_numeric($student_ids)) {
            $fields['student_ids'] = [$student_ids];
        } else {
            throw new \InvalidArgumentException('student_ids should be an array of numbers or a number');
        }

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        return $this->post("/teachers/classes/$class_id/add_students", [], [], $fields);
    }


    /**
     * @param $student_id
     * @param $class_id
     * @return mixed
     * @throws \ErrorException
     */
    public function addStudentToClass($student_id, $class_id)
    {
        return $this->addStudentsToClass([$student_id], $class_id);
    }


    /**
     * @param $student_ids
     * @param $class_id
     * @return mixed
     * @throws \ErrorException
     */
    public function removeStudentsFromClass($student_ids, $class_id)
    {
        $fields = [];

        if (is_array($student_ids)) {
            $fields['student_ids'] = $student_ids;
        } else if (is_numeric($student_ids)) {
            $fields['student_ids'] = [$student_ids];
        } else {
            throw new \InvalidArgumentException('student_ids should be an array of numbers or a number');
        }

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        return $this->post("/teachers/classes/$class_id/remove_students", [], [], $fields);
    }

    /**
     * @param $student_id
     * @param $class_id
     * @return mixed
     * @throws \ErrorException
     */
    public function removeStudentFromClass($student_id, $class_id)
    {
        return $this->removeStudentsFromClass([$student_id], $class_id);
    }
}