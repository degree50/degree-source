<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605082735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix foreign key cascades that the orm somehow does not catch';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solution DROP FOREIGN KEY FK_9F3329DBB5C6F244');
        $this->addSql('ALTER TABLE solution ADD CONSTRAINT FK_9F3329DBB5C6F244 FOREIGN KEY (cut_video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7511C0BE183');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7511C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
