<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240508114944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add name to material and automatically set it to "course - exercise - phase" if we can find the original phase team.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE autosaved_solution CHANGE solution solution JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE7595F669AC9D');
        $this->addSql('DROP INDEX IDX_7CBE7595F669AC9D ON material');
        $this->addSql('ALTER TABLE material ADD name VARCHAR(255) NOT NULL, CHANGE original_phase_team_id original_phase_team CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE759559D06CA5 FOREIGN KEY (original_phase_team) REFERENCES exercise_phase_team (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7CBE759559D06CA5 ON material (original_phase_team)');
        $this->addSql('ALTER TABLE solution CHANGE solution solution JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function postUp(Schema $schema): void
    {
        // this does the same as {Material->generateNameFromExercisePhaseTeam()}
        $this->connection->executeStatement('
            UPDATE material
                JOIN exercise_phase_team ON material.original_phase_team = exercise_phase_team.id
                JOIN exercise_phase ON exercise_phase_team.exercise_phase_id = exercise_phase.id
                JOIN exercise ON exercise_phase.belongs_to_exercise_id = exercise.id
                JOIN course ON exercise.course_id = course.id
            SET material.name = CONCAT(course.name, " - ", exercise.name, " - ", exercise_phase.name)
            WHERE material.name = ""
                AND material.original_phase_team IS NOT NULL;
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solution CHANGE solution solution JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE759559D06CA5');
        $this->addSql('DROP INDEX IDX_7CBE759559D06CA5 ON material');
        $this->addSql('ALTER TABLE material DROP name, CHANGE original_phase_team original_phase_team_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE7595F669AC9D FOREIGN KEY (original_phase_team_id) REFERENCES exercise_phase_team (id)');
        $this->addSql('CREATE INDEX IDX_7CBE7595F669AC9D ON material (original_phase_team_id)');
        $this->addSql('ALTER TABLE autosaved_solution CHANGE solution solution JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
