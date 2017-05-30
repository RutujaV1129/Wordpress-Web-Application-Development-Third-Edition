<?php


class WPWA_XMLRPC_Client {

    private $xml_rpc_url;
    private $username;
    private $password;

    public function __construct( $xml_rpc_url, $username, $password ) {
        $this->xml_rpc_url  = $xml_rpc_url;
        $this->username     = $username;
        $this->password     = $password;
    }

    public function api_request( $request_method, $params ) {

        $request = xmlrpc_encode_request($request_method, $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_URL, $this->xml_rpc_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $results = curl_exec($ch);

        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);


        if ($errorno != 0) {
            return array("error" => $error);
        }

        if ($response_code != 200) {
            return array("error" => "Request Failed : $results");
        }

        return xmlrpc_decode($results);
    }



    function getLatestTopics() {
        $params = array( 0, $this->username, $this->password, array( "post_type" => "wpwaf_topic" ) );
        return $this->api_request("wp.getPosts", $params);
    }

    function getLatestForums() {
        $params = array( 0, $this->username, $this->password, array( "post_type" => "wpwaf_forum" ) );
        return $this->api_request("wp.getPosts", $params);
    }

    function subscribeToTopics($topic_id, $api_token) {
        $params = array( "username" => $this->username, "password" => $this->password, "topic_id" => $topic_id, "token" => $api_token);

        return $this->api_request("wpwaf.subscribeToTopics", $params);
    }

    function getForumTopics($forum_id) {
        $params = array("forum_id" => $forum_id);
        return $this->api_request("wpwaf.getForumTopics", $params);
    }

    function apiDoc() {
        $params = array();
        return $this->api_request("wpwaf.apiDoc", $params);
    }

}

$wpwaf_api_client = new WPWA_XMLRPC_Client("http://localhost/wp-cookbook/xmlrpc.php", "premiummember", "premiummember");
$topics = $wpwaf_api_client->getLatestTopics();
$forums = $wpwaf_api_client->getLatestForums();


$wpwaf_api_client = new WPWA_XMLRPC_Client("http://localhost/wp-cookbook/xmlrpc.php", "premiummember", "premiummember");

$apiDoc = $wpwaf_api_client->apiDoc();
$forum_topics = $wpwaf_api_client->getForumTopics(231);



$subscribe_status = $wpwaf_api_client->subscribeToTopics(272, "afdb17a9a1eaf0f3ec6764d9d3e8a6a63");
echo "<pre>";print_r($subscribe_status);exit;



exit;
?>
