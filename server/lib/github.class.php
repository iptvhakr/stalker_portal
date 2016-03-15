<?php

use Stalker\Lib\Core\Mysql;

class GitHub
{
    private $repository;
    private $owner;
    private $branch = 'master';

    /**
     * @param string $repository_url
     * @throws GitHubException
     */
    public function __construct($repository_url){

        preg_match('/\/\/github.com\/([^\/]+)\/([^\/]+)/', $repository_url, $match);

        if (count($match) != 3){
            throw new GitHubException("Wrong repository url");
        }

        $this->owner      = $match[1];
        $this->repository = $match[2];
    }

    public function getOwner(){
        return $this->owner;
    }

    public function getRepository(){
        return $this->repository;
    }

    /**
     * @param $filename string
     * @return string
     * @throws GitHubConnectionFailure
     * @throws GitHubConnectionTimeout
     * @throws GitHubError
     */
    public function getFileContent($filename){
        return $this->execute('https://raw.githubusercontent.com/'.$this->owner.'/'.$this->repository.'/'.$this->branch.'/'.$filename);
    }

    /**
     * @param int $limit
     * @return array
     * @throws GitHubUnknownFormat
     * @throws GitHubConnectionFailure
     * @throws GitHubConnectionTimeout
     * @throws GitHubError
     */
    public function getReleases($limit = 10){
        return $this->apiCall('https://api.github.com/repos/'.$this->owner.'/'.$this->repository.'/releases?per_page='.$limit);
    }

    /**
     * @return array
     * @throws GitHubUnknownFormat
     * @throws GitHubConnectionFailure
     * @throws GitHubConnectionTimeout
     * @throws GitHubError
     */
    public function getOwnerRepositories(){
        return $this->apiCall('https://api.github.com/users/'.$this->owner.'/repos');
    }

    /**
     * @param string $branch
     */
    public function setBranch($branch){
        $this->branch = $branch;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag){
        $this->setBranch($tag);
    }

    /**
     * @param string $version
     */
    public function setRelease($version){
        $this->setBranch($version);
    }

    /**
     * @param string $url
     * @return mixed
     * @throws GitHubConnectionFailure
     * @throws GitHubConnectionTimeout
     * @throws GitHubError
     * @throws GitHubUnknownFormat
     */
    private function apiCall($url){

        $json_result = $this->execute($url, true);

        if (is_string($json_result)) {

            $result = json_decode($json_result, true);

            if ($result === null) {
                throw new GitHubUnknownFormat("Result cannot be decoded. Result: " . $json_result);
            }
        }else{
            $result = $json_result;
        }

        return $result;
    }

    /**
     * @param string $url
     * @param bool $api_call
     * @return string
     * @throws GitHubConnectionFailure
     * @throws GitHubConnectionTimeout
     * @throws GitHubError
     */
    private function execute($url, $api_call = false){

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'GET');
        curl_setopt($ch, CURLOPT_USERAGENT, 'stalker_portal');
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($api_call){

            $cache = Mysql::getInstance()->from('github_api_cache')->where(array('url' => $url))->get()->first();

            if (!empty($cache) && $cache['etag']){
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('If-None-Match: '.$cache['etag']));
            }
        }

        $response = curl_exec($ch);

        list($headers, $response) = explode("\r\n\r\n", $response, 2);

        $headers = $this->headersAsArray($headers);

        if ($response === false){
            if (curl_errno($ch) == 28){
                throw new GitHubConnectionTimeout('Connection timeout. url: '.$url.'; Error: '.curl_error($ch));
            }else{
                throw new GitHubConnectionFailure('Error get contents from url: '.$url.'; Error: '.curl_error($ch));
            }
        }

        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 304 && !empty($cache)){
            $result = json_decode($cache['data'], true);
        }else{
            $result = json_decode($response, true);
        }


        if ($result !== null){
            $message = !empty($result['message']) ? $result['message'] : $response;
            $response = $result;

            if ($api_call && !empty($headers['etag'])){

                if (!isset($cache)){
                    $cache = Mysql::getInstance()->from('github_api_cache')->where(array('url' => $url))->get()->first();
                }

                $data = array(
                    'url'  => $url,
                    'etag' => $headers['etag'],
                    'data' => json_encode($result),
                    'updated' => 'NOW()'
                );

                if (empty($cache)){
                    Mysql::getInstance()->insert('github_api_cache', $data);
                } elseif ($cache['etag'] != $headers['etag']){
                    Mysql::getInstance()->update('github_api_cache', $data, array('id' => $cache['id']));
                }
            }

        }else{
            $message = $response;
        }

        if ($http_code != 200 && $http_code > 400){
            throw new GitHubError($message, $http_code);
        }

        return $response;
    }

    private function headersAsArray($header_text){

        $headers = array();

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0){
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(':', $line);

                $headers[strtolower($key)] = trim($value);
            }
        }

        return $headers;

    }
}

class GitHubException extends Exception{}
class GitHubConnectionTimeout extends GitHubException{}
class GitHubConnectionFailure extends GitHubException{}
class GitHubUnknownFormat extends GitHubException{}
class GitHubError extends GitHubException{}
