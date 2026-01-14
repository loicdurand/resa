<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112200831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_fiche_suivi (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiche_suivi ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE fiche_suivi ADD CONSTRAINT FK_543C20B0C54C8C93 FOREIGN KEY (type_id) REFERENCES type_fiche_suivi (id)');
        $this->addSql('CREATE INDEX IDX_543C20B0C54C8C93 ON fiche_suivi (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_suivi DROP FOREIGN KEY FK_543C20B0C54C8C93');
        $this->addSql('DROP TABLE type_fiche_suivi');
        $this->addSql('DROP INDEX IDX_543C20B0C54C8C93 ON fiche_suivi');
        $this->addSql('ALTER TABLE fiche_suivi DROP type_id');
    }
}
