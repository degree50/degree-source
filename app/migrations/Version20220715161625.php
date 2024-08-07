<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220715161625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD fachbereich_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9A1FC02CE FOREIGN KEY (fachbereich_id) REFERENCES fachbereich (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9A1FC02CE ON course (fachbereich_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9A1FC02CE');
        $this->addSql('DROP INDEX IDX_169E6FB9A1FC02CE ON course');
        $this->addSql('ALTER TABLE course DROP fachbereich_id');
    }
}
