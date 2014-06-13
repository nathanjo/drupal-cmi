<?php

/**
 * @file
 * Contains \Drupal\comment\Entity\CommentType.
 */

namespace Drupal\comment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\comment\CommentTypeInterface;

/**
 * Defines the comment type entity.
 *
 * @ConfigEntityType(
 *   id = "comment_type",
 *   label = @Translation("Comment type"),
 *   controllers = {
 *     "form" = {
 *       "default" = "Drupal\comment\CommentTypeForm",
 *       "add" = "Drupal\comment\CommentTypeForm",
 *       "edit" = "Drupal\comment\CommentTypeForm",
 *       "delete" = "Drupal\comment\Form\CommentTypeDeleteForm"
 *     },
 *     "list_builder" = "Drupal\comment\CommentTypeListBuilder"
 *   },
 *   admin_permission = "administer comment types",
 *   config_prefix = "type",
 *   bundle_of = "comment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "comment.type_delete",
 *     "edit-form" = "comment.type_edit",
 *     "add-form" = "comment.type_add"
 *   }
 * )
 */
class CommentType extends ConfigEntityBundleBase implements CommentTypeInterface {

  /**
   * The comment type ID.
   *
   * @var string
   */
  public $id;

  /**
   * The comment type label.
   *
   * @var string
   */
  public $label;

  /**
   * The description of the comment type.
   *
   * @var string
   */
  public $description;

  /**
   * The target entity type.
   *
   * @var string
   */
  public $target_entity_type_id;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntityTypeId() {
    return $this->target_entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    if (!$update && !$this->isSyncing()) {
      \Drupal::service('comment.manager')->addBodyField($this->id());
    }
  }

}
