<?php
namespace Drupal\webpower\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure webpower settings for this site.
 */
class WebpowerSettingsForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'webpower_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
      'webpower.settings',
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('webpower.settings');

        // Get the form values and raw input (unvalidated values).
        $values = $form_state->getValues();


        $campaings = get_webpower_fc_campaigns();
        #print_r($campaings);
        $groups = get_webpower_fc_groups($config->get('webpower_campaign_id'));


        // Define a wrapper id to populate new content into.
        $ajax_wrapper = 'my-ajax-wrapper';
        $ajax_wrapper_no = 'my-ajax-wrapper-no';
        $ajax_wrapper_yes = 'my-ajax-wrapper-yes';

        $form['webpower_client_id'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Webpower Client ID'),
          '#default_value' => $config->get('webpower_client_id'),
          '#description' => t('Enter here the API Client ID')
        );

        $form['webpower_client_secret'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Webpower Client Secret'),
          '#default_value' => $config->get('webpower_client_secret'),
          '#description' => t('Enter here the API Client Secret')
        );

        #$form['webpower_campaign_id'] = array(
        #  '#type' => 'textfield',
        #  '#title' => $this->t('Campaign ID'),
        #  '#default_value' => $config->get('webpower_campaign_id'),
        #  '#description' => t('Enter here Campaign ID. Ex: 4')
        #);
        $form['webpower_campaign_id'] = array(
          '#type' => 'select',
          '#options' => $campaings,
          '#title' => $this->t('Campaign ID'),
          '#default_value' => $config->get('webpower_campaign_id'),
          '#description' => t('Select the Campaign. Ex: Club Forcadell'),
          '#ajax' => [
            'callback' => [$this, 'mySelectChange'],
            'event' => 'change',
            'wrapper' => $ajax_wrapper,
          ],
        );

        #$form['webpower_campaign_group_yes'] = array(
        #'#type' => 'textfield',
        #'#title' => $this->t('Campaign Group Name'),
        #'#default_value' => $config->get('webpower_campaign_group_yes'),
        #'#description' => t('Enter here Campaign Group Name. Ex: DDBB Newsletter')
        #);


        #if (!empty($values) && !empty($values['webpower_campaign_id'])) {
        #$form['my_response'] = [
        #     '#markup' => 'The current select value is ' . $values['webpower_campaign_id'],
        #   ];

        $groups = isset($values['webpower_campaign_id']) ? get_webpower_fc_groups($values['webpower_campaign_id']) : get_webpower_fc_groups($config->get('webpower_campaign_id'));

        $form['group'] = array(
          '#type' => 'fieldset',
          '#prefix' => '<div id="'.$ajax_wrapper.'">',
          '#suffix' => '</div>',
          '#title' => $this
            ->t('Webpower Groups'),
        );

        $form['group']['webpower_campaign_group_yes'] = array(
             '#type' => 'select',
             '#options' => $groups,
             '#title' => $this->t('Campaign Group Name - Yes Newsletter'),
             '#default_value' => $config->get('webpower_campaign_group_yes'),
             '#prefix' => '<div id="'.$ajax_wrapper_yes.'">',
             '#suffix' => '</div>',
             '#attributes' => [
                'id' => $ajax_wrapper_yes,
              ]
        );

        $form['group']['webpower_campaign_group_no'] = array(
             '#type' => 'select',
             '#options' => $groups,
             '#title' => $this->t('Campaign Group Name - No Newsletter'),
             '#default_value' => $config->get('webpower_campaign_group_no'),
             '#prefix' => '<div id="'.$ajax_wrapper_no.'">',
             '#suffix' => '</div>',
             '#attributes' => [
                'id' => $ajax_wrapper_no,
              ]
        );
        #}

        $form['webpower_all_contacts'] = array(
          '#type' => 'hidden',
          '#title' => $this->t('All contacts?'),
          '#default_value' => $config->get('webpower_all_contacts'),
          '#description' => t('Do you want to sync all contacts or only users without account on the website.')
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Retrieve the configuration
        $this->configFactory->getEditable('webpower.settings')
      // Set the submitted configuration setting
      ->set('webpower_client_id', $form_state->getValue('webpower_client_id'))
      // You can set multiple configurations at once by making
      // multiple calls to set()
      ->set('webpower_client_secret', $form_state->getValue('webpower_client_secret'))
      ->set('webpower_campaign_id', $form_state->getValue('webpower_campaign_id'))
      ->set('webpower_campaign_group_yes', $form_state->getValue('webpower_campaign_group_yes'))
      ->set('webpower_campaign_group_no', $form_state->getValue('webpower_campaign_group_no'))
      ->set('webpower_all_contacts', $form_state->getValue('webpower_all_contacts'))
      ->save();

        parent::submitForm($form, $form_state);
    }

    /**
      * The callback function for when the `my_select` element is changed.
      *
      * What this returns will be replace the wrapper provided.
      */
    public function mySelectChange(array $form, FormStateInterface $form_state)
    {
        // Return the element that will replace the wrapper (we return itself).
        return $form['group'];
    }
}
