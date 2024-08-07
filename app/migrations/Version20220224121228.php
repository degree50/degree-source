<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220224121228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_code ADD exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE video_code ADD CONSTRAINT FK_70826D449F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('CREATE INDEX IDX_70826D449F65B160 ON video_code (exercise_phase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_code DROP FOREIGN KEY FK_70826D449F65B160');
        $this->addSql('DROP INDEX IDX_70826D449F65B160 ON video_code');
        $this->addSql('ALTER TABLE video_code DROP exercise_phase_id');
    }
}
