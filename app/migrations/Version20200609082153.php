<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create event store table
 */
final class Version20200609082153 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `eventstore_events` (
          `sequencenumber` int(11) NOT NULL AUTO_INCREMENT,
          `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `payload` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json)\',
          `metadata` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json)\',
          `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `recordedat` datetime NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          PRIMARY KEY (`sequencenumber`),
          UNIQUE KEY `id_uniq` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
    ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE eventstore_events');
    }
}
