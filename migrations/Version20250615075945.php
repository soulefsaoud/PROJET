<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615075945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE menu_recette (menu_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_C1607E2ECCD7E912 (menu_id), INDEX IDX_C1607E2E89312FE9 (recette_id), PRIMARY KEY(menu_id, recette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette ADD CONSTRAINT FK_C1607E2ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette ADD CONSTRAINT FK_C1607E2E89312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette DROP FOREIGN KEY FK_C1607E2ECCD7E912
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE menu_recette DROP FOREIGN KEY FK_C1607E2E89312FE9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE menu_recette
        SQL);
    }
}
