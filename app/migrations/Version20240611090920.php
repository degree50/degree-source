<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611090920 extends AbstractMigration
{
    /**
     * shape:
     *  [
     *     'solution_id' => '...',
     *     'cut_video_id' => '...',
     *     'original_video_id' => '...',
     *     ...
     *  ]
     */
    private array $cutVideos = [];

    /**
     * shape:
     * [
     *    [
     *      'solution_id' => '...',
     *      'exercise_phase_team_id' => '...',
     *    ]
     * ]
     */
    private array $solutionExercisePhaseTeam = [];

    public function getDescription(): string
    {
        return 'Introduce CutVideo entity and cascading foreign key constraints on db level';
    }

    public function preUp(Schema $schema): void
    {
        $cutVideoSql = <<<SQL
            SELECT
                solution.id as solution_id,
                solution.cut_video_id as cut_video_id,
                exercise_phase_video.video_id as original_video_id,
                video.encoded_video_directory_virtual_path_and_filename as encoded_dir,
                video.uploaded_subtitle_file_virtual_path_and_filename as subtitle_file,
                video.video_duration as video_duration,
                video.encoding_status as encoding_status,
                video.encoding_started as encoding_started,
                video.encoding_finished as encoding_finished,
                video.created_at as created_at
            FROM solution
            JOIN exercise_phase_team ON solution.id = exercise_phase_team.solution_id
            JOIN exercise_phase ON exercise_phase_team.exercise_phase_id = exercise_phase.id
            JOIN exercise_phase_video ON exercise_phase.id = exercise_phase_video.exercise_phase_id
            JOIN video ON solution.cut_video_id = video.id
            WHERE solution.cut_video_id IS NOT NULL
            ;
        SQL;

        $this->cutVideos = $this->connection->fetchAllAssociative($cutVideoSql);

        $solutionExercisePhaseTeamSql = <<<SQL
            SELECT
                solution.id as solution_id,
                exercise_phase_team.id as exercise_phase_team_id
            FROM solution
            JOIN exercise_phase_team ON solution.id = exercise_phase_team.solution_id
            ;
        SQL;

        $this->solutionExercisePhaseTeam = $this->connection->fetchAllAssociative($solutionExercisePhaseTeamSql);
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cut_video (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', original_video_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', solution_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', encoding_status INT NOT NULL, video_duration DOUBLE PRECISION DEFAULT NULL, encoding_started DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', encoding_finished DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', subtitle_file_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, encoded_video_directory_virtual_path_and_filename VARCHAR(255) DEFAULT NULL, INDEX IDX_384D5510A9EE75F (original_video_id), UNIQUE INDEX UNIQ_384D55101C0BE183 (solution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cut_video ADD CONSTRAINT FK_384D5510A9EE75F FOREIGN KEY (original_video_id) REFERENCES video (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cut_video ADD CONSTRAINT FK_384D55101C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7511C0BE183');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7519F65B160');
        $this->addSql('DROP INDEX UNIQ_7416C7511C0BE183 ON exercise_phase_team');
        $this->addSql('ALTER TABLE exercise_phase_team DROP solution_id');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7519F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solution DROP FOREIGN KEY FK_9F3329DBB5C6F244');
        $this->addSql('DROP INDEX UNIQ_9F3329DBB5C6F244 ON solution');
        $this->addSql('ALTER TABLE solution ADD exercise_phase_team_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', DROP cut_video_id');

        // Foreign key constraint for solution.exercise_phase_team_id is added in postUp
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->cutVideos as $cutVideo) {
            $this->connection->executeStatement(
                'INSERT INTO cut_video
                (
                    id,
                    original_video_id,
                    solution_id,
                    created_at,
                    encoding_status,
                    video_duration,
                    encoding_started,
                    encoding_finished,
                    subtitle_file_virtual_path_and_filename,
                    encoded_video_directory_virtual_path_and_filename
                ) VALUES
                (
                    :cut_video_id,
                    :original_video_id,
                    :solution_id,
                    :created_at,
                    :encoding_status,
                    :video_duration,
                    :encoding_started,
                    :encoding_finished,
                    :subtitle_file,
                    :encoded_dir
                )',
                $cutVideo
            );

            // delete VideoFavorites with this CutVideo
            $this->connection->executeStatement(
                'DELETE FROM video_favorite where video_id = :cut_video_id',
                $cutVideo
            );

            // delete CutVideo from video table
            $this->connection->executeStatement(
                'DELETE FROM video where id = :cut_video_id',
                $cutVideo
            );
        }

        // clean up video_favorite table
        $this->connection->executeStatement('
            DELETE FROM video_favorite
            WHERE video_id IN (
                SELECT video.id as id
                FROM video
                JOIN video_favorite ON video.id = video_favorite.video_id
                WHERE video.title LIKE "Cut_video, %" OR video.title LIKE "Video to be cut <%"
            )
        ');

        // clean up video table
        $this->connection->executeStatement(
            'DELETE FROM video WHERE title LIKE "Cut_video, %" OR title LIKE "Video to be cut <%";'
        );

        // set exercise_phase_team_id in solution table
        foreach ($this->solutionExercisePhaseTeam as $entry) {
            $this->connection->executeStatement(
                'UPDATE solution SET exercise_phase_team_id = :exercise_phase_team_id WHERE id = :solution_id',
                $entry
            );
        }

        // remove all orphaned solutions
        $this->connection->executeStatement('DELETE FROM solution WHERE exercise_phase_team_id = ""');

        // add foreign key constraint after migration of data
        $this->connection->executeStatement('ALTER TABLE solution ADD CONSTRAINT FK_9F3329DB7B50751B FOREIGN KEY (exercise_phase_team_id) REFERENCES exercise_phase_team (id) ON DELETE CASCADE');
        $this->connection->executeStatement('CREATE UNIQUE INDEX UNIQ_9F3329DB7B50751B ON solution (exercise_phase_team_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cut_video DROP FOREIGN KEY FK_384D5510A9EE75F');
        $this->addSql('ALTER TABLE cut_video DROP FOREIGN KEY FK_384D55101C0BE183');
        $this->addSql('DROP TABLE cut_video');
        $this->addSql('ALTER TABLE exercise_phase_team DROP FOREIGN KEY FK_7416C7519F65B160');
        $this->addSql('ALTER TABLE exercise_phase_team ADD solution_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7511C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id)');
        $this->addSql('ALTER TABLE exercise_phase_team ADD CONSTRAINT FK_7416C7519F65B160 FOREIGN KEY (exercise_phase_id) REFERENCES exercise_phase (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7416C7511C0BE183 ON exercise_phase_team (solution_id)');
        $this->addSql('ALTER TABLE solution DROP FOREIGN KEY FK_9F3329DB7B50751B');
        $this->addSql('DROP INDEX UNIQ_9F3329DB7B50751B ON solution');
        $this->addSql('ALTER TABLE solution ADD cut_video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP exercise_phase_team_id');
        $this->addSql('ALTER TABLE solution ADD CONSTRAINT FK_9F3329DBB5C6F244 FOREIGN KEY (cut_video_id) REFERENCES video (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F3329DBB5C6F244 ON solution (cut_video_id)');
    }
}
