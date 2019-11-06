<?php
/**
 * @file
 * Contains \Drupal\webform_forcadell\Controller\WebformForcadellController.
 */
 
namespace Drupal\webform_forcadell\Controller;
 
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;


class WebformforcadellController extends ControllerBase {
	
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

    $this->crateContact('contact', $data);

    return new JsonResponse([
      'data' => $data,
      'method' => 'POST',
    ]);
  
  }


  /**
   * A helper function returning results.
   */
  public function getResults() {
    return [
      [
        "name" => "The best space",
        "city" => 'Vila Azul',
        "price" => 120,
      ],
    ];
  }
 
  public function crateContact($webform, $data){

    // Example IDs
    #$webform_id = 'my_webform';

    // Create webform submission.
    #return ['not saving...'];	  
    $values = [
      'uid' => 57,	
      'webform_id' => $webform,
      'data' => $this->getFields($data),
    ];

    /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
    $webform_submission = WebformSubmission::create($values);
    $webform_submission->save();
    return $webform_submission;
  }

  private function getFields($data){
    $output = [];
    foreach($data as $key => $value){
    	$output[$key] = $value;
    }

    return $output;
  }

}
