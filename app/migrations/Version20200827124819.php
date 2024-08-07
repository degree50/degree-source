<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200827124819 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B2109EE8C9F47');
        $this->addSql('DROP INDEX IDX_948B2109EE8C9F47 ON exercise_phase');
        $this->addSql('ALTER TABLE exercise_phase CHANGE belongs_to_excercise_id belongs_to_exercise_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B210969B1E40A FOREIGN KEY (belongs_to_exercise_id) REFERENCES exercise (id)');
        $this->addSql('CREATE INDEX IDX_948B210969B1E40A ON exercise_phase (belongs_to_exercise_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B210969B1E40A');
        $this->addSql('DROP INDEX IDX_948B210969B1E40A ON exercise_phase');
        $this->addSql('ALTER TABLE exercise_phase CHANGE belongs_to_exercise_id belongs_to_excercise_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B2109EE8C9F47 FOREIGN KEY (belongs_to_excercise_id) REFERENCES exercise (id)');
        $this->addSql('CREATE INDEX IDX_948B2109EE8C9F47 ON exercise_phase (belongs_to_excercise_id)');
    }
}
