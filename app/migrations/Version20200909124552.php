<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200909124552 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE exercise_phase_video_code');
        $this->addSql('ALTER TABLE video_code ADD exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE video_code ADD CONSTRAINT FK_70826D449F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('CREATE INDEX IDX_70826D449F65B160 ON video_code (exercise_phase_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE exercise_phase_video_code (exercise_phase_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', video_code_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_87D1EFCE9F65B160 (exercise_phase_id), INDEX IDX_87D1EFCEC23717BA (video_code_id), PRIMARY KEY(exercise_phase_id, video_code_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE exercise_phase_video_code ADD CONSTRAINT FK_87D1EFCE9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_video_code ADD CONSTRAINT FK_87D1EFCEC23717BA FOREIGN KEY (video_code_id) REFERENCES video_code (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_code DROP FOREIGN KEY FK_70826D449F65B160');
        $this->addSql('DROP INDEX IDX_70826D449F65B160 ON video_code');
        $this->addSql('ALTER TABLE video_code DROP exercise_phase_id');
    }
}
