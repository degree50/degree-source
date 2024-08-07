<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200928125301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE exercise_phase_video (exercise_phase_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', video_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_ED1340DA9F65B160 (exercise_phase_id), INDEX IDX_ED1340DA29C1004E (video_id), PRIMARY KEY(exercise_phase_id, video_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exercise_phase_video ADD CONSTRAINT FK_ED1340DA9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_video ADD CONSTRAINT FK_ED1340DA29C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE video_analysis_video');
        $this->addSql('ALTER TABLE exercise_phase DROP video_cutting_active');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE video_analysis_video (video_analysis_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', video_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_6DFC18195BC3B19E (video_analysis_id), INDEX IDX_6DFC181929C1004E (video_id), PRIMARY KEY(video_analysis_id, video_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE video_analysis_video ADD CONSTRAINT FK_6DFC181929C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_analysis_video ADD CONSTRAINT FK_6DFC18195BC3B19E FOREIGN KEY (video_analysis_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE exercise_phase_video');
        $this->addSql('ALTER TABLE exercise_phase ADD video_cutting_active TINYINT(1) DEFAULT NULL');
    }
}
