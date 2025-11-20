<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706154114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_recette (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, recette_id INT NOT NULL, favoritis TINYINT(1) DEFAULT NULL, notation INT DEFAULT NULL, cuisine_a DATETIME DEFAULT NULL, nombre_cuisiné INT DEFAULT NULL, INDEX IDX_11B67D9AA76ED395 (user_id), INDEX IDX_11B67D9A89312FE9 (recette_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette ADD CONSTRAINT FK_11B67D9AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette ADD CONSTRAINT FK_11B67D9A89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette DROP FOREIGN KEY FK_11B67D9AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette DROP FOREIGN KEY FK_11B67D9A89312FE9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_recette
        SQL);
    }
}
