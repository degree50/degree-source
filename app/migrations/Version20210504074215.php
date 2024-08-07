<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210504074215 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2CC6C446AA');
        $this->addSql('DROP INDEX UNIQ_7CC7DA2CC6C446AA ON video');
        $this->addSql('ALTER TABLE video ADD uploaded_subtitle_file_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, DROP subtitles_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video ADD subtitles_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', DROP uploaded_subtitle_file_virtual_path_and_filename');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2CC6C446AA FOREIGN KEY (subtitles_id) REFERENCES video_subtitles (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7CC7DA2CC6C446AA ON video (subtitles_id)');
    }
}
