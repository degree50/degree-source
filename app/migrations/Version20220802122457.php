<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\User\Model\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220802122457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function postUp(Schema $schema): void
    {
        // WHY:
        // Set the creation_date and the expiration_date for existing users
        $defaultCreationDate = new \DateTimeImmutable('2022-01-01');
        $this->connection->executeStatement(
            "UPDATE user SET created_at = :created_at WHERE created_at = '0000-00-00 00:00:00'",
            ['created_at' => $defaultCreationDate->format(User::DB_DATE_FORMAT)]
        );

        $this->connection->executeStatement(
            "UPDATE user SET expiration_date = :expiration_date WHERE expiration_date = '0000-00-00 00:00:00'",
            ['expiration_date' => $defaultCreationDate->add(\DateInterval::createFromDateString(User::EXPIRATION_DURATION_STRING))->format(User::DB_DATE_FORMAT)]
        );
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', ADD expiration_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP created_at, DROP expiration_date');
    }
}
