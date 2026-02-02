<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202195959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation CHANGE demandeur demandeur VARCHAR(8) DEFAULT NULL, CHANGE message_valideur message_valideur VARCHAR(255) DEFAULT NULL, CHANGE observation_valideur observation_valideur VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation CHANGE demandeur demandeur VARCHAR(8) DEFAULT NULL COMMENT \'Nigend du personnel qui effectue la réservation (différent du champs "user" qui en est le bénéficiaire)\', CHANGE message_valideur message_valideur VARCHAR(255) DEFAULT NULL COMMENT \'Message du valideur à la personne qui a fait la réservation (pour lui dire pourquoi il modifie / supprime la demande)\', CHANGE observation_valideur observation_valideur TEXT DEFAULT NULL COMMENT \'Remplace le champs "Observation" dans le suivi pour les valideurs\'');
    }
}
