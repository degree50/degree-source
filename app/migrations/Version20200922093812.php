<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200922093812 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7511C0BE183');
        $this->addSql('DROP INDEX UNIQ_7416C7511C0BE183 ON exercise_phase_team');
        $this->addSql('ALTER TABLE exercise_phase_team DROP solution_id');
        $this->addSql('ALTER TABLE solution ADD team_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE solution ADD CONSTRAINT FK_9F3329DB296CD8AE FOREIGN KEY (team_id) REFERENCES exercise_phase_team (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F3329DB296CD8AE ON solution (team_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exercise_phase_team ADD solution_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7511C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7416C7511C0BE183 ON exercise_phase_team (solution_id)');
        $this->addSql('ALTER TABLE solution DROP FOREIGN KEY FK_9F3329DB296CD8AE');
        $this->addSql('DROP INDEX UNIQ_9F3329DB296CD8AE ON solution');
        $this->addSql('ALTER TABLE solution DROP team_id');
    }
}
