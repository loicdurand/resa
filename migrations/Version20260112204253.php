<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112204253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_suivi ADD reservation_id INT NOT NULL');
        $this->addSql('ALTER TABLE fiche_suivi ADD CONSTRAINT FK_543C20B0B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('CREATE INDEX IDX_543C20B0B83297E7 ON fiche_suivi (reservation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_suivi DROP FOREIGN KEY FK_543C20B0B83297E7');
        $this->addSql('DROP INDEX IDX_543C20B0B83297E7 ON fiche_suivi');
        $this->addSql('ALTER TABLE fiche_suivi DROP reservation_id');
    }
}
