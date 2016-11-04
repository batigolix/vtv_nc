<?php

namespace Drupal\vtv_tools\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Contact' block.
 *
 * @Block(
 *  id = "contact",
 *  admin_label = @Translation("VTV tools: Contact"),
 * )
 */
class Contact extends BlockBase {

  protected $default_markup = 'Theater Frans Boermans<br>
Wilhelminastraat 1, 5941 GJ Velden - Telefoon : 077 472 2313<br>
Bankrekening : NL71RABO 017.38.92.132 - Kvk venlo : 12052238<br>
E-mail : volkstheater.venlo@gmail.com - <a href="/contact">Contact</a>';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'label' => t("Contact"),
      'contact_markup_string' => $this->default_markup,
      'cache' => array(
        'max_age' => 3600,
        'contexts' => array(
          'cache_context.user.roles',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['contact_markup_string_text'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Contact markup'),
      '#description' => $this->t('This text will appear in the example block.'),
      '#default_value' => $this->configuration['contact_markup_string'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['contact_markup_string']
      = $form_state->getValue('contact_markup_string_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => $this->configuration['contact_markup_string'],
    );
  }
}
