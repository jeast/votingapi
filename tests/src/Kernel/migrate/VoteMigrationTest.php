<?php

namespace Drupal\Tests\votingapi\Kernel\migrate;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;

/**
 * Tests D7 rate source plugin.
 *
 * @group votingapi
 */
class VoteMigrationTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'votingapi',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getFixtureFilePath() {
    return implode(DIRECTORY_SEPARATOR, [
      drupal_get_path('module', 'votingapi'),
      'tests',
      'fixtures',
      'drupal7.php',
    ]);
  }

  /**
   * Tests migration.
   */
  public function testVoteMigration() {
    $this->installEntitySchema('vote');
    // This demonstrates that only comments belonging to articles are migrated.
    $this->executeMigrations(['d7_vote:comment:article']);
    $storage = \Drupal::entityTypeManager()->getStorage('vote');
    assert($storage instanceof EntityStorageInterface);
    $votes = $storage->loadMultiple();
    $this->assertCount(3, $votes);
    $array_1 = $votes[1]->toArray();
    $test_1 = [
      'id' => [['value' => '1']],
      'type' => [['target_id' => 'vote']],
      'entity_type' => [['value' => 'comment']],
      'entity_id' => [['target_id' => '6']],
      'value' => [['value' => '77']],
      'value_type' => [['value' => 'percent']],
      'user_id' => [['target_id' => '1']],
      'timestamp' => [['value' => '1635760436']],
      'vote_source' => [['value' => '127.0.0.1']],
    ];
    $this->assertEquals(
      array_diff_key(
        $array_1,
        ['uuid' => 'uuid']
      ),
      $test_1
    );
    // Migrates all votes present.
    $this->executeMigrations(['d7_vote']);
    $storage = \Drupal::entityTypeManager()->getStorage('vote');
    assert($storage instanceof EntityStorageInterface);
    $votes = $storage->loadMultiple();
    $this->assertCount(10, $votes);
  }

}
