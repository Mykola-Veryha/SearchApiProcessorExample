<?php

namespace Drupal\MODULE_NAME\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Processor\ProcessorProperty;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add access status field.
 *
 * @SearchApiProcessor(
 *   id = "example_search_public_status",
 *   label = @Translation("Public Status"),
 *   description = @Translation("Nodes with a public status available to all users."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 * )
 */
class PublicStatus extends ProcessorPluginBase {

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL): array {
    $properties = [];

    if ($datasource !== NULL && $datasource->getPluginId() === 'entity:node') {
      $definition = [
        'label' => $this->t('Public Status'),
        'description' => $this->t('Nodes with a public status available to all users.'),
        'type' => 'integer',
        'processor_id' => $this->getPluginId(),
      ];
      $properties['public_status'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function addFieldValues(ItemInterface $item): void {
    /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $original_object */
    $original_object = $item->getOriginalObject();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $original_object->getEntity();
    $public_status = $node->isPublished();

    $fields = $this->getFieldsHelper()->filterForPropertyPath(
      $item->getFields(),
      $item->getDatasourceId(),
      'public_status'
    );
    foreach ($fields as $field) {
      $field->addValue($public_status);
    }
  }

}
