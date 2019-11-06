<?php

namespace Drupal\webpower\Controller;

use Drupal\Core\Controller\ControllerBase;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
#use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Drupal\webpower\Plugin\WebpowerAPI\WebpowerAPIClient;

/**
 * Defines WebpowerController class.
 */
class WebpowerController extends ControllerBase
{

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
    public function content()
    {
        /** webpower.settings are the API credentials for webpower */
        $config = \Drupal::config('webpower.settings');
        $clientId = $config->get('webpower_client_id');
        $clientSecret = $config->get('webpower_client_secret');
        $wpApi = new WebpowerAPIClient($clientId, $clientSecret);
        $wpApi->prepareAccessToken();
        #$campaings = $wpApi->getCampaigns();
        $custom = new \stdClass();
        $custom->field = "name";
        $custom->value = "test4";
        $data = [
          "email" => "me2@test.com",
          "lang" => "es",
           $custom
        ];
        $data2 = [
          "name" => "Newsletter test3",
          "is_test" => false,
          "is_active" => true,
          //"remarks" => "Newsletter test"
        ];
        #$contact = $wpApi->putContact(4, 5, $data);
        /* Si ya existe no duplica ni crea */
        #$contact = $wpApi->postContact(4, $data);
        #$contact = $wpApi->getContact(4, 5);
        #$contact = $wpApi->getContactIdByEmail(2, 'me@chiyana.com');
        #$groupId = $wpApi->getGroupIdByName(4, 'Newsletter Test'); //Dados de alta
        #$group = $wpApi->postGroup(4, $data2);
        #echo "<pre>";
        #var_dump(json_decode($contact));
        #echo "</pre>";
        #$group = json_decode($group);
        return [
        '#type' => 'markup',
        '#markup' => ['webpower'],
      ];
    }

   public function post(Request $request) {

    // This condition checks the `Content-type` and makes sure to
    // decode JSON string from the request body into array.
    $data = ['nada'];
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    }

    #$response['data'] = 'Some test data to return';
    #$response['method'] = 'POST';

    $response = $this->createContact($data);

    return new JsonResponse([
      'data' => $response,
      'method' => 'POST',
    ]);

   }

   private function createContact($data){
    //WebPower Config
    $config = $this->getCredentials();
    $object = new \stdClass();
    $customString = '[
        {
          "field": "Nom",
          "value": "'.$data['name'].'"
	},
       {
          "field": "formulario",
          "value": "coworking"
        }
     ]'; 
   # return json_decode($customString);
   /*$array = [
      [
        'field' => 'Nom',
        'value' => $data['name']
      ],
      [
        'field' => 'formulario',
        'value' => 'coworking' 
      ],
    ];
    foreach ($array as $key => $value)
    {
        $object->{$key} = $value;
    }*/
 
    /**
     * $args values to save
     * @var array
     */
    $args = array(
      'email' => $data['email'],
      'custom' => json_decode($customString),
      'campaignGroup' => $config['campaignGroupYes'],
    );
    
    try {
        if (isset($data['email'])) {
          return  $this->saveContactAndGroup($args);
        }
    } catch (\Exception $e) {
        // Log the exception to watchdog.
        \Drupal::logger('type')->error($e->getMessage());
    }
  }

  /**
   * saveContactAndGroup saves the contact and the group
   * @param  [type] $args [description]
   * @return [type]       [description]
   */
  private function saveContactAndGroup($args)
  {
      //registramos el usario en webpower si no existe.
      $groupId = null;
      $contactId = $this->getWebpowerContactId($args['email']);
       //Si ya existe no volvemos a guardar
      if(null != $contactId){
        return $contactId;
      }
      $contactId = $this->saveWebPowerContact($args);
      if (null != $contactId) {
          $this->saveWebpowerGroup($args, $contactId);
      }
      return $contactId;
  }

  /**
   * getWebpowerContactId gets the contact ID
   * @param  [type] $email [description]
   * @return [type]        [description]
   */
  private function getWebpowerContactId($email)
  {
      try {
          $config = $this->getCredentials();
          $wpApi = new WebpowerAPIClient($config['clientId'], $config['clientSecret']);
          $wpApi->prepareAccessToken();
          /* If contact exists it does not create or update it */
          return $wpApi->getContactIdByEmail($config['campaignId'], $email);
      } catch (\Exception $e) {
          // Log the exception to watchdog.
          \Drupal::logger('type')->error($e->getMessage());
      }
  }

  /**
   * saveWebPowerContact saves the Contact
   * @param  [type] $args [description]
   * @return [type]       [description]
   */
  private function saveWebPowerContact($args)
  {
      try {
          $config = $this->getCredentials();
          $wpApi = new WebpowerAPIClient($config['clientId'], $config['clientSecret']);
          $wpApi->prepareAccessToken();
	  $data = [
	    "lang" => "es", //camp obligatori
            "email" => $args['email'],
            "custom" => $args['custom'],
          ];
          /* If contact exists it does not create or update it */
          $contact = $wpApi->postContact($config['campaignId'], $data);
	    \Drupal::logger('type')->info(t('Creating a new contact.... Webpower response: ') . json_encode([$contact, $config['campaignId'], $data]));
	  if (null != $contact) {
              if (is_string($contact)) {
                  $contact = json_decode($contact);
              }
              if (isset($contact->id)) {
                  \Drupal::logger('type')->info(t('New contact created on webpower. Webpower response: ') . json_encode($contact));
                  return $contact->id;
              }
          }
          if (strpos(json_encode($contact), 'exception') !== false) {
              \Drupal::logger('type')->error(json_encode($contact));
          }
      } catch (\Exception $e) {
          // Log the exception to watchdog.
          \Drupal::logger('type')->error($e->getMessage());
      }
  }

  /**
   * saveWebpowerGroup saves the group
   * @param  [type] $args      [description]
   * @param  [type] $contactId [description]
   * @return [type]            [description]
   */
  private function saveWebpowerGroup($args, $contactId){
    if (is_numeric($contactId)) {
        //verificamos si existe el grupo newsletter
        $groupId = $this->getContactGroupId($args['campaignGroup']);
        //Si no existe lo creamos
        if (!$groupId) {
            //$groupId = $this->saveCampaignGroup($args['campaignGroup']);
        }
    }
    //recogemos su id de una forma u otra
    if (null != $groupId) {
        //asignamos el contacto al grupo
        $this->saveContactGroup($contactId, $groupId);
    }
    return $contactId;
  }

  /**
   * getContactGroupId returns the contact group ID
   * @param  string $name [description]
   * @return [type]       [description]
   */
  private function getContactGroupId($name='Dados de alta')
  {
      try {
          $config = $this->getCredentials();
          $wpApi = new WebpowerAPIClient($config['clientId'], $config['clientSecret']);
          $wpApi->prepareAccessToken();
          /* If contact exists it does not create or update it */
          $groupId = $wpApi->getGroupIdByName($config['campaignId'], $name);

          if (strpos(json_encode($groupId), 'exception') !== false) {
              \Drupal::logger('type')->error(json_encode($groupId));
          } else {
              \Drupal::logger('type')->info(t('Creating a new contact. Webpower Group ID: ') . json_encode($groupId));
          }
          return $groupId;
      } catch (\Exception $e) {
          // Log the exception to watchdog.
          \Drupal::logger('type')->error($e->getMessage());
      }
  }

  /**
  * @returns The id of the added group
  */
  private function saveCampaignGroup($name='Dados de alta')
  {
      try {
          $config = getWebpowerCredentials();
          $wpApi = new WebpowerAPIClient($config['clientId'], $config['clientSecret']);
          $wpApi->prepareAccessToken();
          $data = [
            "name" => $name,
            "is_test" => false,
            "is_active" => true
          ];
          /* If contact exists it does not create or update it */
          $group = $wpApi->postGroup($config['campaignId'], $data);
          if (null != $group) {
              $group = json_decode($group);
              if (isset($group->id)) {
                  return $group->id;
              }
          }
          if (strpos(json_encode($group), 'exception') !== false) {
              \Drupal::logger('type')->error(json_encode($group));
          } else {
              \Drupal::logger('type')->info(t('Creating a new Campaign Group. Webpower response: ') . json_encode($group));
          }
      } catch (\Exception $e) {
          // Log the exception to watchdog.
          \Drupal::logger('type')->error($e->getMessage());
      }
  }

  /**
  * @returns The id of the added group
  */
  private function saveContactGroup($contactId, $groupId)
  {
      try {
          $config = $this->getCredentials();
          $wpApi = new WebpowerAPIClient($config['clientId'], $config['clientSecret']);
          $wpApi->prepareAccessToken();
          /* If contact exists it does not create or update it */
          $contactInGroup = $wpApi->postContactGroups($config['campaignId'], $contactId, [$groupId]);
          if (null != $contactInGroup) {
              $contactInGroup = json_decode($contactInGroup);
              if (isset($contactInGroup->id)) {
                  return $contactInGroup->id;
              }
          }
          if (strpos(json_encode($contactInGroup), 'exception') !== false) {
              \Drupal::logger('type')->error(json_encode($contactInGroup));
          } else {
              \Drupal::logger('type')->info(t('Adding a contact to a group. Webpower response: ') . json_encode($contactInGroup));
          }
      } catch (\Exception $e) {
          // Log the exception to watchdog.
          \Drupal::logger('type')->error($e->getMessage());
      }
  }
    
   private function getCredentials()
   {
    $config = \Drupal::config('webpower.settings');
    return [
    'clientId' => (null != $config->get('webpower_client_id') ? $config->get('webpower_client_id') : 'feaaeef1728917dbad1c'),
    'clientSecret' => (null != $config->get('webpower_client_secret') ? $config->get('webpower_client_secret')  : '02a72cd0b8070ffe51897cd17e54ba647c9af4dc'),
    'campaignId' => (null != $config->get('webpower_campaign_id') ?  $config->get('webpower_campaign_id')  :  1),
    'campaignGroupYes' => (null != $config->get('webpower_campaign_group_yes') ?  $config->get('webpower_campaign_group_yes')  :  'Dados de alta'),
    'campaignGroupNo' => (null != $config->get('webpower_campaign_group_no') ?  $config->get('webpower_campaign_group_no')  :  'Dados de baja'),
    'allContacts' => (null != $config->get('webpower_all_contacts') ? $config->get('webpower_all_contacts') : false)
  ];
   }

}
