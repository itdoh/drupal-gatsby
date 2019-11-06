<?php
namespace Drupal\webpower\Plugin\WebpowerAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class WebpowerAPIClient
{
    private $client = null;
    const API_URL = "https://forcadell.webpower.eu/admin/api/index.php/rest";
    const BASE_URL = "https://forcadell.webpower.eu/admin";
    public $clientId;
    public $clientSecret;
    public $accessToken;
    public $scope;
    public $campaignId;
    public $campaignIdSandbox;
    public function __construct($clientId = 'feaaeef1728917dbad1c', $clientSecret = '02a72cd0b8070ffe51897cd17e54ba647c9af4dc', $scope='rest')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scope = $scope;
        $this->client = new Client();
    }

    public function prepareAccessToken()
    {
        $post_params = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::BASE_URL . "/oauth2/token.php");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $post_params .= "client_id=".$this->clientId."&";
            $post_params .= "client_secret=".$this->clientSecret."&";
            $post_params .= "grant_type=client_credentials&";

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
            $response = curl_exec($ch);
            #$response = $this->client->post($url, $data);
            $result = json_decode($response);
            $this->accessToken = $result->access_token;

            curl_close($ch);
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    public function StatusCodeHandling($e)
    {
        if ($e->getResponse()->getStatusCode() == 400) {
            $this->prepareAccessToken();
        } elseif ($e->getResponse()->getStatusCode() == 422) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } elseif ($e->getResponse()->getStatusCode() == 500) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } elseif ($e->getResponse()->getStatusCode() == 401) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } elseif ($e->getResponse()->getStatusCode() == 403) {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        } else {
            $response = json_decode($e->getResponse()->getBody(true)->getContents());
            return $response;
        }
    }

    public function getCampaigns()
    {
        try {
            $url = self::API_URL . "/campaign";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    public function getCampaign($id)
    {
        try {
            $url = self::API_URL . "/campaign/{$id}";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }
    /**
    * @TODO: Saber cual es el id de la campaña
    */
    public function getContacts($campaignId, $page = 1, $length = 100)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/contact";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
        } catch (RequestException $e) {
            $response = $this->StatusCodeHandling($e);
            return $response;
        }
    }

    public function getContact($campaignId, $id)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/contact/{$id}";
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    /**
    * Retrieves a group
    * $campaignId The campaignId
    * $email The email of the contact
    */
    public function getContactIdByEmail($campaignId, $email)
    {
      try {
          $jsonString = 'match={"email": "'.$email.'"}';
          $url = self::API_URL . "/{$campaignId}/contact";
          $header = array("Authorization"=> "Bearer " . $this->accessToken);
          $response = $this->client->get($url, array("headers" => $header, "query" => $jsonString));
          $result = $response->getBody()->getContents();
          return $this->getContactId($result, $email);
        } catch (\Exception $e) {
          return $e->getMessage();
        }
    }
    /**
    * $campaignId integer (path)  The campaignId
    * $id integer (path)	 The id of the contact
    * $since string($date-time) (query)
    *
    */
    public function getContactGroups($campaignId, $id, $since=null)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/contact/{$id}/group";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    /**
    * {
    *    "email": "user@example.com",
    *    "mobile_nr": "string",
    *    "lang": "string",
    *    "custom": [
    *      {
    *        "field": "string",
    *        "value": "string"
    *      }
    *    ]
    *  }
    *
    */

    public function postContact($campaignId, $data)
    {


        try {
            $url = self::API_URL . "/{$campaignId}/contact";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            #$response = $this->client->get($url, array("headers" => $header));
            $response = $this->client->post($url, ["headers" => $header, "json" => $data]);
	    #return ['respuesta'];
	    $result = $response->getBody()->getContents();
            return $result;
        } catch (RequestException $e) {
            $response = $e->getMessage(); #this->StatusCodeHandling($e);
            return $response;
        }
    }

    /**
    * Add contact to groups
    * $campaignId The campaignId
    * $id integer (path)	The id of the contact
    * $groups (body) The groups to add the contact to
    */

    public function postContactGroups($campaignId, $id, $groups)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/contact/{$id}/group";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            #$response = $this->client->get($url, array("headers" => $header));
            $response = $this->client->post($url, ["headers" => $header, "json" => $groups]);
            $result = $response->getBody()->getContents();
            return $result;
        } catch (RequestException $e) {
            $response = $this->StatusCodeHandling($e);
            return $response;
        }
    }

    /**
    * Move contacts from group to group
    * $campaignId The campaignId
    * $id integer (path)	The id of the contact
    * $destId The id of the group
    */

    public function moveContactGroups($campaignId, $id, $destId)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/contacts/group/{$id}/to/{$destId}";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            #$response = $this->client->get($url, array("headers" => $header));
            $response = $this->client->put($url, ["headers" => $header]);
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }



    }

    /**
    * {
    *    "email": "user@example.com",
    *    "mobile_nr": "string",
    *    "lang": "string",
    *    "custom": [
    *      {
    *        "field": "string",
    *        "value": "string"
    *      }
    *    ]
    *  }
    *
    */

    public function putContact($campaignId, $id, $data)
    {


        try {
            $url = self::API_URL . "/{$campaignId}/contact/{$id}";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            #$response = $this->client->get($url, array("headers" => $header));
            $response = $this->client->put($url, ["headers" => $header, "json" => $data]);
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    /**
    * Retrieves a list of groups
    * @var $campaignId The campaignId
    * @var $page Which page to retrieve
    * @var $pagelength The amount of records per page
    *
    */
    public function getGroups($campaignId, $page = 1, $length = 100)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/group";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    /**
    * Retrieves a group
    * $campaignId The campaignId
    * $id The id of the group
    */
    public function getGroup($campaignId, $id)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/group/{$id}";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            $response = $this->client->get($url, array("headers" => $header));
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }

    /**
    * Retrieves a group
    * $campaignId The campaignId
    * $id The id of the group
    */
    public function getGroupIdByName($campaignId, $name)
    {
      try {
          $url = self::API_URL . "/{$campaignId}/group";
          $option = array("exceptions" => false);
          $header = array("Authorization"=> "Bearer " . $this->accessToken);
          $response = $this->client->get($url, array("headers" => $header));
          $result = $response->getBody()->getContents();
      //    print_r($result);
          $groupId = $this->getGroupId($result, $name);
          return $groupId;
        } catch (\Exception $e) {
          return $e->getMessage();
        }
    }



    /**
    * Retrieves a group
    * $campaignId The campaignId
    * $group The group to create
    * {
    *  "name": "string",
    *  "is_test": true,
    *  "is_active": true,
    *  "remarks": "string"
    * }
    */
    public function postGroup($campaignId, $group)
    {

        try {
          $url = self::API_URL . "/{$campaignId}/group";
          $option = array("exceptions" => false);
          $header = array("Authorization"=> "Bearer " . $this->accessToken);
          #$response = $this->client->get($url, array("headers" => $header));
          $response = $this->client->post($url, ["headers" => $header, "json" => $group]);
          $result = $response->getBody()->getContents();
          return $result;
        } catch (\Exception $e) {
          return $e->getMessage();
        }

    }

    /**
    * Update an existing group
    * $campaignId The campaignId
    * $id The id of the group
    * $group The group to update
    *  {
    *    "name": "string",
    *    "is_test": true,
    *    "is_active": true,
    *    "remarks": "string"
    *  }
    */
    public function putGroup($campaignId, $id, $group)
    {
        try {
            $url = self::API_URL . "/{$campaignId}/group/{$id}";
            $option = array("exceptions" => false);
            $header = array("Authorization"=> "Bearer " . $this->accessToken);
            #$response = $this->client->get($url, array("headers" => $header));
            $response = $this->client->put($url, ["headers" => $header, "json" => $group]);
            $result = $response->getBody()->getContents();
            return $result;
          } catch (\Exception $e) {
            return $e->getMessage();
          }
    }



        public function getFields($campaignId, $page = 1, $length = 100)
        {
            try {
                $url = self::API_URL . "/{$campaignId}/field";
                $option = array("exceptions" => false);
                $header = array("Authorization"=> "Bearer " . $this->accessToken);
                $response = $this->client->get($url, array("headers" => $header));
                $result = $response->getBody()->getContents();
                return $result;
              } catch (\Exception $e) {
                return $e->getMessage();
              }
        }

        /*
        {
          "name": "string",
          "description": [
            {
              "lang": "string",
              "description": "string"
            }
          ],
          "type": "varchar",
          "length": 0,
          "default": "string",
          "required": true
        }
        */

        public function postField($campaignId, $data)
        {


            try {
                $url = self::API_URL . "/{$campaignId}/field";
                $option = array("exceptions" => false);
                $header = array("Authorization"=> "Bearer " . $this->accessToken);
                #$response = $this->client->get($url, array("headers" => $header));
                $response = $this->client->post($url, ["headers" => $header, "json" => $data]);
                $result = $response->getBody()->getContents();
                return $result;
              } catch (\Exception $e) {
                return $e->getMessage();
              }
        }


        private function getGroupId($result, $name){
          if(!$result){
            return;
          }
          $result = json_decode($result);
          if(isset($result->result)){
            $array = $result->result;
            foreach($array as $id => $item){
              if($item->name == $name){
                return $item->id;
              }
            }
          }
        }

        private function getContactId($result, $email){
          if(!$result){
            return;
          }
          $result = json_decode($result);
          if(isset($result->result)){
            $array = $result->result;
            foreach($array as $id => $item){
              if($item->email == $email){
                return $item->id;
              }
            }
          }
        }


}
