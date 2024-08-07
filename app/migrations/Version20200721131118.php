<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200721131118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE exercise_phase_video_code (exercise_phase_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', video_code_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_87D1EFCE9F65B160 (exercise_phase_id), INDEX IDX_87D1EFCEC23717BA (video_code_id), PRIMARY KEY(exercise_phase_id, video_code_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exercise_phase_video_code ADD CONSTRAINT FK_87D1EFCE9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_video_code ADD CONSTRAINT FK_87D1EFCEC23717BA FOREIGN KEY (video_code_id) REFERENCES video_code (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE video_code_exercise_phase');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE video_code_exercise_phase (video_code_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', exercise_phase_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', INDEX IDX_D72E8C42C23717BA (video_code_id), INDEX IDX_D72E8C429F65B160 (exercise_phase_id), PRIMARY KEY(video_code_id, exercise_phase_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE video_code_exercise_phase ADD CONSTRAINT FK_D72E8C429F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_code_exercise_phase ADD CONSTRAINT FK_D72E8C42C23717BA FOREIGN KEY (video_code_id) REFERENCES video_code (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE exercise_phase_video_code');
    }
}
