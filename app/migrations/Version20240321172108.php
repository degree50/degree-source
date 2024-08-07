<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240321172108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase_team ADD is_test TINYINT(1) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        // set is_test for all existing teams that are considered test runs
        // (e.g. creator of the ExercisePhaseTeam is the same as creator of the Exercise)
        $this->connection->executeStatement('
            UPDATE exercise_phase_team
                JOIN user ON exercise_phase_team.creator_id = user.id
                JOIN exercise_phase ON exercise_phase_team.exercise_phase_id = exercise_phase.id
                JOIN exercise ON exercise_phase.belongs_to_exercise_id = exercise.id
            SET is_test = true
            WHERE is_test = false
                AND (
                    exercise.creator_id = exercise_phase_team.creator_id
                    OR user.roles LIKE "%ROLE_ADMIN%"
                    OR user.roles LIKE "%ROLE_DOZENT%"
                );
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase_team DROP is_test');
    }
}
