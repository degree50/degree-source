<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314093757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B21097C3EF58B');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B21097C3EF58B FOREIGN KEY (depends_on_exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercise_phase DROP FOREIGN KEY FK_948B21097C3EF58B');
        $this->addSql('ALTER TABLE exercise_phase ADD CONSTRAINT FK_948B21097C3EF58B FOREIGN KEY (depends_on_exercise_phase_id) REFERENCES exercise_phase (id)');
    }
}
