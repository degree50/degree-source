<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617080911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_anonymized TINYINT(1) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        // set initial value. Already anonymized users are identified by UUID as email
        $this->connection->executeStatement('UPDATE user SET is_anonymized = TRUE WHERE email REGEXP("^.{8}-.{4}-4.{3}-.{4}-.{12}$")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP is_anonymized');
    }
}
