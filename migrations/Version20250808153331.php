<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808153331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient_recette (recette_id INT NOT NULL, ingredient_id INT NOT NULL, quantite VARCHAR(255) NOT NULL, unite_mesure VARCHAR(255) NOT NULL, INDEX IDX_488C685689312FE9 (recette_id), INDEX IDX_488C6856933FE08C (ingredient_id), PRIMARY KEY(recette_id, ingredient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_ingredient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ingredient_id INT NOT NULL, quantity NUMERIC(5, 5) DEFAULT NULL, unite_mesure VARCHAR(255) DEFAULT NULL, date_expiration DATETIME DEFAULT NULL, added_at DATETIME DEFAULT NULL, ajoute_a DATETIME DEFAULT NULL, INDEX IDX_CCC8BE9CA76ED395 (user_id), INDEX IDX_CCC8BE9C933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_recette (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, recette_id INT NOT NULL, favoritis TINYINT(1) DEFAULT NULL, notation INT DEFAULT NULL, cuisine_a DATETIME DEFAULT NULL, nombre_cuisiné INT DEFAULT NULL, INDEX IDX_11B67D9AA76ED395 (user_id), INDEX IDX_11B67D9A89312FE9 (recette_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C685689312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C6856933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient ADD CONSTRAINT FK_CCC8BE9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient ADD CONSTRAINT FK_CCC8BE9C933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette ADD CONSTRAINT FK_11B67D9AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette ADD CONSTRAINT FK_11B67D9A89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A989312FE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient DROP FOREIGN KEY FK_17C041A9933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recette_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient ADD quantite INT DEFAULT NULL, CHANGE unite_mesure unite_mesure VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette ADD descriptions VARCHAR(255) DEFAULT NULL, ADD temps_cuisson INT DEFAULT NULL, ADD source_url VARCHAR(500) NOT NULL, ADD image_url VARCHAR(500) DEFAULT NULL, ADD image_path VARCHAR(255) DEFAULT NULL, ADD image_alt VARCHAR(100) DEFAULT NULL, CHANGE temps_preparation temps_preparation INT DEFAULT NULL, CHANGE difficulte difficulte VARCHAR(80) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE nombre_de_portions nombre_de_portions INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD first_name VARCHAR(100) NOT NULL, ADD last_name VARCHAR(100) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE recette_ingredient (recette_id INT NOT NULL, ingredient_id INT NOT NULL, INDEX IDX_17C041A9933FE08C (ingredient_id), INDEX IDX_17C041A989312FE9 (recette_id), PRIMARY KEY(recette_id, ingredient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A989312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette_ingredient ADD CONSTRAINT FK_17C041A9933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C685689312FE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C6856933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient DROP FOREIGN KEY FK_CCC8BE9CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_ingredient DROP FOREIGN KEY FK_CCC8BE9C933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette DROP FOREIGN KEY FK_11B67D9AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_recette DROP FOREIGN KEY FK_11B67D9A89312FE9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredient_recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_recette
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient DROP quantite, CHANGE unite_mesure unite_mesure VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recette DROP descriptions, DROP temps_cuisson, DROP source_url, DROP image_url, DROP image_path, DROP image_alt, CHANGE temps_preparation temps_preparation INT NOT NULL, CHANGE difficulte difficulte VARCHAR(80) NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE nombre_de_portions nombre_de_portions INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP first_name, DROP last_name
        SQL);
    }
}
