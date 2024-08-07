<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523121929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB61220EA6');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB9F65B160');
        $this->addSql('ALTER TABLE attachment CHANGE exercise_phase_id exercise_phase_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576C591CC992');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576CA76ED395');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576C591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C591CC992');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C61220EA6');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B210969B1E40A');
        $this->addSql('ALTER TABLE exercise_phase CHANGE belongs_to_exercise_id belongs_to_exercise_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B210969B1E40A FOREIGN KEY (belongs_to_exercise_id) REFERENCES exercise (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B210969B1E40A');
        $this->addSql('ALTER TABLE exercise_phase CHANGE belongs_to_exercise_id belongs_to_exercise_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B210969B1E40A FOREIGN KEY (belongs_to_exercise_id) REFERENCES exercise (id)');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C591CC992');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C61220EA6');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576CA76ED395');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576C591CC992');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB61220EA6');
        $this->addSql('ALTER TABLE attachment DROP FOREIGN KEY FK_795FD9BB9F65B160');
        $this->addSql('ALTER TABLE attachment CHANGE exercise_phase_id exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
    }
}
