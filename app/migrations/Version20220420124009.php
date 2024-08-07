<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220420124009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachment (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', creator_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', exercise_phase_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, upload_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', uploaded_file_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, INDEX IDX_795FD9BB61220EA6 (creator_id), INDEX IDX_795FD9BB9F65B160 (exercise_phase_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BB9F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('DROP TABLE material');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE material (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', exercise_phase_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', creator_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, uploaded_file_virtual_path_and_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, upload_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', mime_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_7CBE75959F65B160 (exercise_phase_id), INDEX IDX_7CBE759561220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE759561220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE75959F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('DROP TABLE attachment');
    }
}
