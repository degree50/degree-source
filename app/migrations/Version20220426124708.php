<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\ExercisePhase\Model\ExercisePhaseStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220426124708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function postUp(Schema $schema): void
    {
        // WHY:
        // The exercisePhaseStatus is mandatory.
        // Already existing teams should either have it set to "BEENDET" if they already have a solution
        // or to "IN_BEARBEITUNG" if they do not have a solution.
        $this->connection->executeStatement(
            "UPDATE exercise_phase_team SET status = :defaultStatus WHERE status = '' AND solution_id IS NOT null",
            ['defaultStatus' => ExercisePhaseStatus::BEENDET->value]
        );

        $this->connection->executeStatement(
            "UPDATE exercise_phase_team SET status = :defaultStatus WHERE status = '' AND solution_id IS null",
            ['defaultStatus' => ExercisePhaseStatus::IN_BEARBEITUNG->value]
        );
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase_team ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase_team DROP status');
    }
}
