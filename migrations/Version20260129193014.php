<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129193014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(25) NOT NULL, libelle VARCHAR(255) NOT NULL, template VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE atelier (id INT AUTO_INCREMENT NOT NULL, code_unite INT NOT NULL, nom_court VARCHAR(25) NOT NULL, nom_long VARCHAR(255) DEFAULT NULL, departement INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carburant_vehicule (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_vehicule (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, illustration VARCHAR(25) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fiche_suivi (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT NOT NULL, type_id INT NOT NULL, reservation_id INT NOT NULL, created_at DATETIME NOT NULL, path VARCHAR(50) NOT NULL, INDEX IDX_543C20B04A4A3511 (vehicule_id), INDEX IDX_543C20B0C54C8C93 (type_id), INDEX IDX_543C20B0B83297E7 (reservation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE genre_vehicule (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(4) NOT NULL, libelle VARCHAR(255) NOT NULL, ordre INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE horaire_ouverture (id INT AUTO_INCREMENT NOT NULL, code_unite_id INT NOT NULL, jour VARCHAR(2) NOT NULL, creneau VARCHAR(5) NOT NULL, debut VARCHAR(5) NOT NULL, fin VARCHAR(5) NOT NULL, INDEX IDX_D97D249515CB0C80 (code_unite_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, action_id INT NOT NULL, INDEX IDX_E04992AAD60322AC (role_id), INDEX IDX_E04992AA9D32F035 (action_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT NOT NULL, path VARCHAR(50) NOT NULL, position VARCHAR(25) DEFAULT NULL, principale INT DEFAULT NULL, INDEX IDX_14B784184A4A3511 (vehicule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, vehicule_id INT NOT NULL, statut_id INT NOT NULL, type_demande_id INT DEFAULT NULL, date_debut DATE NOT NULL, heure_debut VARCHAR(5) NOT NULL, date_fin DATE NOT NULL, heure_fin VARCHAR(5) NOT NULL, user VARCHAR(8) NOT NULL, created_at DATETIME NOT NULL, observation VARCHAR(255) DEFAULT NULL, demandeur VARCHAR(8) DEFAULT NULL, INDEX IDX_42C849554A4A3511 (vehicule_id), INDEX IDX_42C84955F6203804 (statut_id), INDEX IDX_42C849559DEA883D (type_demande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restriction (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(50) NOT NULL, description VARCHAR(1024) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(25) NOT NULL, ordre INT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE statut_reservation (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(25) NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5F37A13BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transmission_vehicule (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) NOT NULL, libelle VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_demande (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_fiche_suivi (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unite (id INT AUTO_INCREMENT NOT NULL, code_unite INT NOT NULL, nom_court VARCHAR(25) NOT NULL, nom_long VARCHAR(255) DEFAULT NULL, departement INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nigend VARCHAR(8) NOT NULL, unite VARCHAR(8) NOT NULL, profil VARCHAR(25) DEFAULT NULL, departement INT DEFAULT NULL, mail VARCHAR(255) DEFAULT NULL, banned TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicule (id INT AUTO_INCREMENT NOT NULL, genre_id INT NOT NULL, categorie_id INT DEFAULT NULL, carburant_id INT DEFAULT NULL, transmission_id INT DEFAULT NULL, unite_id INT DEFAULT NULL, restriction_id INT DEFAULT NULL, marque VARCHAR(255) NOT NULL, modele VARCHAR(255) NOT NULL, motorisation VARCHAR(25) DEFAULT NULL, finition VARCHAR(25) DEFAULT NULL, controle_technique DATE DEFAULT NULL, nb_places INT DEFAULT NULL, immatriculation VARCHAR(9) NOT NULL, serigraphie TINYINT(1) NOT NULL, couleur_vignette VARCHAR(8) NOT NULL, observation VARCHAR(1024) DEFAULT NULL, departement INT DEFAULT NULL, INDEX IDX_292FFF1D4296D31F (genre_id), INDEX IDX_292FFF1DBCF5E72D (categorie_id), INDEX IDX_292FFF1D32DAAD24 (carburant_id), INDEX IDX_292FFF1D78D28519 (transmission_id), INDEX IDX_292FFF1DEC4A74AB (unite_id), INDEX IDX_292FFF1DE6160631 (restriction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiche_suivi ADD CONSTRAINT FK_543C20B04A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE fiche_suivi ADD CONSTRAINT FK_543C20B0C54C8C93 FOREIGN KEY (type_id) REFERENCES type_fiche_suivi (id)');
        $this->addSql('ALTER TABLE fiche_suivi ADD CONSTRAINT FK_543C20B0B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
        $this->addSql('ALTER TABLE horaire_ouverture ADD CONSTRAINT FK_D97D249515CB0C80 FOREIGN KEY (code_unite_id) REFERENCES atelier (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AAD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AA9D32F035 FOREIGN KEY (action_id) REFERENCES action (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849554A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955F6203804 FOREIGN KEY (statut_id) REFERENCES statut_reservation (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559DEA883D FOREIGN KEY (type_demande_id) REFERENCES type_demande (id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D4296D31F FOREIGN KEY (genre_id) REFERENCES genre_vehicule (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_vehicule (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D32DAAD24 FOREIGN KEY (carburant_id) REFERENCES carburant_vehicule (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1D78D28519 FOREIGN KEY (transmission_id) REFERENCES transmission_vehicule (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DEC4A74AB FOREIGN KEY (unite_id) REFERENCES unite (id)');
        $this->addSql('ALTER TABLE vehicule ADD CONSTRAINT FK_292FFF1DE6160631 FOREIGN KEY (restriction_id) REFERENCES restriction (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_suivi DROP FOREIGN KEY FK_543C20B04A4A3511');
        $this->addSql('ALTER TABLE fiche_suivi DROP FOREIGN KEY FK_543C20B0C54C8C93');
        $this->addSql('ALTER TABLE fiche_suivi DROP FOREIGN KEY FK_543C20B0B83297E7');
        $this->addSql('ALTER TABLE horaire_ouverture DROP FOREIGN KEY FK_D97D249515CB0C80');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AAD60322AC');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AA9D32F035');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784184A4A3511');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849554A4A3511');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955F6203804');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559DEA883D');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D4296D31F');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DBCF5E72D');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D32DAAD24');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1D78D28519');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DEC4A74AB');
        $this->addSql('ALTER TABLE vehicule DROP FOREIGN KEY FK_292FFF1DE6160631');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE atelier');
        $this->addSql('DROP TABLE carburant_vehicule');
        $this->addSql('DROP TABLE categorie_vehicule');
        $this->addSql('DROP TABLE fiche_suivi');
        $this->addSql('DROP TABLE genre_vehicule');
        $this->addSql('DROP TABLE horaire_ouverture');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE restriction');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE statut_reservation');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE transmission_vehicule');
        $this->addSql('DROP TABLE type_demande');
        $this->addSql('DROP TABLE type_fiche_suivi');
        $this->addSql('DROP TABLE unite');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicule');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
