<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604185459 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE video_cut_list (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', cut_list LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_subtitles (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', subtitles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', subtitles_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', creator_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', title LONGTEXT NOT NULL, description LONGTEXT NOT NULL, uploaded_video_file_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, encoded_video_directory_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_7CC7DA2CC6C446AA (subtitles_id), INDEX IDX_7CC7DA2C61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE material (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, uploadAt DATETIME NOT NULL, INDEX IDX_7CBE75959F65B160 (exercise_phase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE autosaved_solution (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', owner_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', team_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', solution LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', update_timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', INDEX IDX_C210BDD77E3C61F9 (owner_id), INDEX IDX_C210BDD7296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise_phase (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', belongs_to_excercise_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', is_group_phase VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, task LONGTEXT NOT NULL, definition LONGTEXT NOT NULL, sorting VARCHAR(255) NOT NULL, components LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', phaseType VARCHAR(255) NOT NULL, INDEX IDX_948B2109EE8C9F47 (belongs_to_excercise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_analysis_video (video_analysis_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', video_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_6DFC18195BC3B19E (video_analysis_id), INDEX IDX_6DFC181929C1004E (video_id), PRIMARY KEY(video_analysis_id, video_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise_phase_team (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', exercise_phase_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', solution_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', current_editor_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_7416C7519F65B160 (exercise_phase_id), UNIQUE INDEX UNIQ_7416C7511C0BE183 (solution_id), INDEX IDX_7416C751B2B31DE0 (current_editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise_phase_team_user (exercise_phase_team_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_6DA7F5A67B50751B (exercise_phase_team_id), INDEX IDX_6DA7F5A6A76ED395 (user_id), PRIMARY KEY(exercise_phase_team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exercise (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', course_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', creator_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_AEDAD51C591CC992 (course_id), INDEX IDX_AEDAD51C61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solution (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', solution LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_role (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', course_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, INDEX IDX_9FCB576CA76ED395 (user_id), INDEX IDX_9FCB576C591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2CC6C446AA FOREIGN KEY (subtitles_id) REFERENCES video_subtitles (id)');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75959F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('ALTER TABLE autosaved_solution ADD CONSTRAINT FK_C210BDD77E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE autosaved_solution ADD CONSTRAINT FK_C210BDD7296CD8AE FOREIGN KEY (team_id) REFERENCES exercise_phase_team (id)');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B2109EE8C9F47 FOREIGN KEY (belongs_to_excercise_id) REFERENCES exercise (id)');
        $this->addSql('ALTER TABLE video_analysis_video ADD CONSTRAINT FK_6DFC18195BC3B19E FOREIGN KEY (video_analysis_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_analysis_video ADD CONSTRAINT FK_6DFC181929C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7519F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7511C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id)');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C751B2B31DE0 FOREIGN KEY (current_editor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE exercise_phase_team_user ADD CONSTRAINT FK_6DA7F5A67B50751B FOREIGN KEY (exercise_phase_team_id) REFERENCES exercise_phase_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_team_user ADD CONSTRAINT FK_6DA7F5A6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51C61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course_role ADD CONSTRAINT FK_9FCB576C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2CC6C446AA');
        $this->addSql('ALTER TABLE video_analysis_video DROP FOREIGN KEY FK_6DFC181929C1004E');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE75959F65B160');
        $this->addSql('ALTER TABLE video_analysis_video DROP FOREIGN KEY FK_6DFC18195BC3B19E');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7519F65B160');
        $this->addSql('ALTER TABLE autosaved_solution DROP FOREIGN KEY FK_C210BDD7296CD8AE');
        $this->addSql('ALTER TABLE exercise_phase_team_user DROP FOREIGN KEY FK_6DA7F5A67B50751B');
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B2109EE8C9F47');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7511C0BE183');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C61220EA6');
        $this->addSql('ALTER TABLE autosaved_solution DROP FOREIGN KEY FK_C210BDD77E3C61F9');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C751B2B31DE0');
        $this->addSql('ALTER TABLE exercise_phase_team_user DROP FOREIGN KEY FK_6DA7F5A6A76ED395');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C61220EA6');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576CA76ED395');
        $this->addSql('ALTER TABLE exercise DROP FOREIGN KEY FK_AEDAD51C591CC992');
        $this->addSql('ALTER TABLE course_role DROP FOREIGN KEY FK_9FCB576C591CC992');
        $this->addSql('DROP TABLE video_cut_list');
        $this->addSql('DROP TABLE video_subtitles');
        $this->addSql('DROP TABLE video');
        $this->addSql('DROP TABLE material');
        $this->addSql('DROP TABLE autosaved_solution');
        $this->addSql('DROP TABLE exercise_phase');
        $this->addSql('DROP TABLE video_analysis_video');
        $this->addSql('DROP TABLE exercise_phase_team');
        $this->addSql('DROP TABLE exercise_phase_team_user');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE solution');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE course_role');
    }
}
