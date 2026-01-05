<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105183351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE restriction (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(50) NOT NULL, description VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD mail VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD restriction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DE6160631 FOREIGN KEY (restriction_id) REFERENCES restriction (id)');
        $this->addSql('CREATE INDEX IDX_292FFF1DE6160631 ON vehicule (restriction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DE6160631');
        $this->addSql('DROP TABLE restriction');
        $this->addSql('ALTER TABLE user DROP mail');
        $this->addSql('DROP INDEX IDX_292FFF1DE6160631 ON vehicule');
        $this->addSql('ALTER TABLE vehicule DROP restriction_id');
    }
}
