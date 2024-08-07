<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220301140214 extends AbstractMigration
{
    private array $phaseIdDependsOnIdMap = [];

    public function getDescription(): string
    {
        return '';
    }

    public function preUp(Schema $schema): void
    {
        //If memory becomes an issue, write to a file
        // Get all phaseIds and the phaseId they depend on as key value pair ([id => dependsOnId])
        $this->phaseIdDependsOnIdMap = $this->connection->executeQuery('
            SELECT t1.id, t2.id
            FROM exercise_phase t1
            JOIN exercise_phase t2 ON t1.sorting = t2.sorting + 1
            WHERE t1.depends_on_previous_phase = 1
                AND t1.belongs_to_exercise_id = t2.belongs_to_exercise_id
            ;
        ')->fetchAllKeyValue();
    }

    public function postUp(Schema $schema) : void
    {
        // after table migration we want to restore data in new format.
        // update each row that has depends on another phase accordingly
        foreach ($this->phaseIdDependsOnIdMap as $id => $dependsOnId) {
            $this->connection->update(
                'exercise_phase', // tableName
                ['depends_on_exercise_phase_id' => $dependsOnId], // SET
                ['id' => $id] // WHERE
            );
        }
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase ADD depends_on_exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP depends_on_previous_phase');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B21097C3EF58B FOREIGN KEY (depends_on_exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('CREATE INDEX IDX_948B21097C3EF58B ON exercise_phase (depends_on_exercise_phase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B21097C3EF58B');
        $this->addSql('DROP INDEX IDX_948B21097C3EF58B ON exercise_phase');
        $this->addSql('ALTER TABLE exercise_phase ADD depends_on_previous_phase TINYINT(1) NOT NULL, DROP depends_on_exercise_phase_id');
    }
}
