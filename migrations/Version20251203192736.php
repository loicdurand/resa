<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203192736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicule_restriction DROP FOREIGN KEY FK_3CAC3A2D4A4A3511');
        $this->addSql('ALTER TABLE vehicule_restriction DROP FOREIGN KEY FK_3CAC3A2DE6160631');
        $this->addSql('DROP TABLE vehicule_restriction');
        $this->addSql('ALTER TABLE vehicule ADD restriction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DE6160631 FOREIGN KEY (restriction_id) REFERENCES restriction (id)');
        $this->addSql('CREATE INDEX IDX_292FFF1DE6160631 ON vehicule (restriction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vehicule_restriction (vehicule_id INT NOT NULL, restriction_id INT NOT NULL, INDEX IDX_3CAC3A2D4A4A3511 (vehicule_id), INDEX IDX_3CAC3A2DE6160631 (restriction_id), PRIMARY KEY(vehicule_id, restriction_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE vehicule_restriction ADD CONSTRAINT FK_3CAC3A2D4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicule_restriction ADD CONSTRAINT FK_3CAC3A2DE6160631 FOREIGN KEY (restriction_id) REFERENCES restriction (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DE6160631');
        $this->addSql('DROP INDEX IDX_292FFF1DE6160631 ON vehicule');
        $this->addSql('ALTER TABLE vehicule DROP restriction_id');
    }
}
