<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250808212737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, quantite INT DEFAULT NULL, unite_mesure VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ingredient_recette (id INT AUTO_INCREMENT NOT NULL, recette_id INT NOT NULL, ingredient_id INT NOT NULL, quantite VARCHAR(255) NOT NULL, unite_mesure VARCHAR(255) NOT NULL, INDEX IDX_488C685689312FE9 (recette_id), INDEX IDX_488C6856933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_7D053A93A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE menu_recette (menu_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_C1607E2ECCD7E912 (menu_id), INDEX IDX_C1607E2E89312FE9 (recette_id), PRIMARY KEY(menu_id, recette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE recette (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, descriptions VARCHAR(255) DEFAULT NULL, instructions LONGTEXT NOT NULL, temps_preparation INT DEFAULT NULL, temps_cuisson INT DEFAULT NULL, difficulte VARCHAR(80) DEFAULT NULL, date_creation DATETIME DEFAULT NULL, nombre_de_portions INT DEFAULT NULL, source_url VARCHAR(500) NOT NULL, image_url VARCHAR(500) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE regime (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, couleur VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE regime_recette (regime_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_D4095CF735E7D534 (regime_id), INDEX IDX_D4095CF789312FE9 (recette_id), PRIMARY KEY(regime_id, recette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_ingredient (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ingredient_id INT NOT NULL, quantity NUMERIC(5, 5) DEFAULT NULL, unite_mesure VARCHAR(255) DEFAULT NULL, date_expiration DATETIME DEFAULT NULL, added_at DATETIME DEFAULT NULL, ajoute_a DATETIME DEFAULT NULL, INDEX IDX_CCC8BE9CA76ED395 (user_id), INDEX IDX_CCC8BE9C933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_recette (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, recette_id INT NOT NULL, favoritis TINYINT(1) DEFAULT NULL, notation INT DEFAULT NULL, cuisine_a DATETIME DEFAULT NULL, nombre_cuisiné INT DEFAULT NULL, INDEX IDX_11B67D9AA76ED395 (user_id), INDEX IDX_11B67D9A89312FE9 (recette_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C685689312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette ADD CONSTRAINT FK_488C6856933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu ADD CONSTRAINT FK_7D053A93A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette ADD CONSTRAINT FK_C1607E2ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette ADD CONSTRAINT FK_C1607E2E89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette ADD CONSTRAINT FK_D4095CF735E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette ADD CONSTRAINT FK_D4095CF789312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C685689312FE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ingredient_recette DROP FOREIGN KEY FK_488C6856933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette DROP FOREIGN KEY FK_C1607E2ECCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette DROP FOREIGN KEY FK_C1607E2E89312FE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette DROP FOREIGN KEY FK_D4095CF735E7D534
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette DROP FOREIGN KEY FK_D4095CF789312FE9
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
            DROP TABLE ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ingredient_recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE menu
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE menu_recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE regime
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE regime_recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_ingredient
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_recette
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
