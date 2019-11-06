<?php

namespace Drupal\webpower\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Webpower block with which you can save a contact on webpower.
 *
 * @Block(
 *   id = "webpower_block",
 *   admin_label = @Translation("Webpower block"),
 * )
 */
class WebpowerBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
    public function build()
    {
        // Return the form @ Form/WebpowerBlockForm.php.
        return \Drupal::formBuilder()->getForm('Drupal\webpower\Form\WebpowerBlockForm');
    }

    /**
    * {@inheritdoc}
    */
    protected function blockAccess(AccountInterface $account)
    {
        return AccessResult::allowedIfHasPermission($account, 'create content');
    }

    /**
     * {@inheritdoc}
    */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state)
    {
        $this->setConfigurationValue('webpower_block_settings', $form_state->getValue('webpower_block_settings'));
    }
}
