<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706153334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_ingredient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ingredient_id INT NOT NULL, quantity NUMERIC(5, 5) DEFAULT NULL, unite_mesure VARCHAR(255) DEFAULT NULL, date_expiration DATETIME DEFAULT NULL, ajoute_a DATETIME DEFAULT NULL, INDEX IDX_CCC8BE9CA76ED395 (user_id), INDEX IDX_CCC8BE9C933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient ADD CONSTRAINT FK_CCC8BE9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient ADD CONSTRAINT FK_CCC8BE9C933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient DROP FOREIGN KEY FK_CCC8BE9CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient DROP FOREIGN KEY FK_CCC8BE9C933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_ingredient
        SQL);
    }
}
