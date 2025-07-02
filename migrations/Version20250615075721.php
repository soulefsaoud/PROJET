<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615075721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE regime_recette (regime_id INT NOT NULL, recette_id INT NOT NULL, INDEX IDX_D4095CF735E7D534 (regime_id), INDEX IDX_D4095CF789312FE9 (recette_id), PRIMARY KEY(regime_id, recette_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette ADD CONSTRAINT FK_D4095CF735E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette ADD CONSTRAINT FK_D4095CF789312FE9 FOREIGN KEY (recette_id) REFERENCES recette (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette DROP FOREIGN KEY FK_D4095CF735E7D534
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE regime_recette DROP FOREIGN KEY FK_D4095CF789312FE9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE regime_recette
        SQL);
    }
}
