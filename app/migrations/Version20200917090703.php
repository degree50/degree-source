<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200917090703 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solution ADD cut_video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE solution ADD CONSTRAINT FK_9F3329DBB5C6F244 FOREIGN KEY (cut_video_id) REFERENCES video (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F3329DBB5C6F244 ON solution (cut_video_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE solution DROP FOREIGN KEY FK_9F3329DBB5C6F244');
        $this->addSql('DROP INDEX UNIQ_9F3329DBB5C6F244 ON solution');
        $this->addSql('ALTER TABLE solution DROP cut_video_id');
    }
}
