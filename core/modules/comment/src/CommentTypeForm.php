<?php

/**
 * @file
 * Contains \Drupal\comment\CommentTypeForm.
 */

namespace Drupal\comment;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form controller for category edit forms.
 */
class CommentTypeForm extends EntityForm {

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Logger Channel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('logger.factory')->get('comment')
    );
  }

  /**
   * Constructs a CommentTypeFormController
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface
   *   The logger channel.
   */
  function __construct(EntityManagerInterface $entity_manager, LoggerChannelInterface $logger) {
    $this->entityManager = $entity_manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $comment_type = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#maxlength' => 255,
      '#default_value' => $comment_type->label(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $comment_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\comment\Entity\CommentType::load',
      ),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$comment_type->isNew(),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#default_value' => $comment_type->description,
      '#description' => t('Describe this comment type. The text will be displayed on the <em>Comment types</em> administration overview page'),
      '#title' => t('Description'),
    );

    $options = array();
    foreach ($this->entityManager->getDefinitions() as $entity_type) {
      if ($entity_type->isFieldable()) {
        $options[$entity_type->id()] = $entity_type->getLabel();
      }
    }
    $form['target_entity_type_id'] = array(
      '#type' => 'select',
      '#default_value' => $comment_type->getTargetEntityTypeId(),
      '#title' => t('Target entity type'),
      '#options' => $options,
    );

    if ($this->moduleHandler->moduleExists('content_translation')) {
      $form['language'] = array(
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings',
      );

      $language_configuration = language_get_default_configuration('comment', $comment_type->id());
      $form['language']['language_configuration'] = array(
        '#type' => 'language_configuration',
        '#entity_information' => array(
          'entity_type' => 'comment',
          'bundle' => $comment_type->id(),
        ),
        '#default_value' => $language_configuration,
      );

      $form['#submit'][] = 'language_configuration_element_submit';
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $comment_type = $this->entity;
    $status = $comment_type->save();

    $edit_link = \Drupal::linkGenerator()->generateFromUrl($this->t('Edit'), $this->entity->urlInfo());
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('Comment type %label has been updated.', array('%label' => $comment_type->label())));
      $this->logger->notice('Comment type %label has been updated.', array('%label' => $comment_type->label(), 'link' => $edit_link));
    }
    else {
      drupal_set_message(t('Comment type %label has been added.', array('%label' => $comment_type->label())));
      $this->logger->notice('Comment type %label has been added.', array('%label' => $comment_type->label(), 'link' =>  $edit_link));
    }

    $form_state['redirect_route']['route_name'] = 'comment.type_list';
  }

}
